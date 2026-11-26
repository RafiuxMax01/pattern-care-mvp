// Contexto de sesion: PatternCareAuth, permisos, sucursales y modo standalone.

// Sesión de demostración para el modo standalone (index.html sin servidor)
const SESION_STANDALONE = {
    id: 0,
    usuario: 'demo',
    nombre_completo: 'Dueña / Encargada General',
    correo: null,
    iniciales: 'EG',
    estado: 'Activo',
    rol_clave: 'ENCARGADA_GENERAL',
    rol_nombre: 'Dueña / Encargada General',
    alcance_global: true,
    sucursal_default_id: null,
    permisos: ['*'],
    sucursales: [],
    modo_standalone: true
};

const authState = {
    sesion: null,
    modo: 'detectando',   // 'servidor' | 'standalone'
    cargando: true
};

const PatternCareAuth = {
    get usuario() {
        return authState.sesion;
    },
    get modo() {
        return authState.modo;
    },
    autenticado() {
        return authState.sesion !== null;
    },
    esGlobal() {
        return !!(authState.sesion && authState.sesion.alcance_global);
    },

    /** ¿El rol en sesión tiene este permiso? (ej. 'inventario.ajustar') */
    tienePermiso(clave) {
        if (!authState.sesion) return false;
        const permisos = authState.sesion.permisos || [];
        return permisos.includes('*') || permisos.includes(clave);
    },

    /** Ids de sucursales asignadas; null = sin restricción (alcance global) */
    sucursalesPermitidas() {
        if (!authState.sesion || authState.sesion.alcance_global) return null;
        return (authState.sesion.sucursales || []).map(s => parseInt(s.sucursal_id, 10));
    },

    /** ¿Puede operar en esta sucursal? accion: 'ver' | 'vender' | 'ajustar' */
    puedeEnSucursal(sucursalId, accion = 'ver') {
        if (!authState.sesion) return false;
        if (authState.sesion.alcance_global) return true;

        const fila = (authState.sesion.sucursales || [])
            .find(s => parseInt(s.sucursal_id, 10) === parseInt(sucursalId, 10));
        if (!fila) return false;
        if (accion === 'vender') return !!fila.puede_vender;
        if (accion === 'ajustar') return !!fila.puede_ajustar_inventario;
        return true;
    },

    /**
     * Lee la sesión del servidor al arrancar la aplicación.
     *
     * La sesión de demostración (todos los permisos, datos de ejemplo) solo se
     * usa cuando no hay PHP detrás: index.html abierto con doble clic o servido
     * por un servidor estático, que devuelve el .php sin ejecutar. Si PHP
     * responde con un error (por ejemplo, la base caída), no se entra en modo
     * demostración: se vuelve a login.html explicando el motivo.
     */
    async inicializar() {
        authState.cargando = true;

        if (window.location.protocol === 'file:') {
            return this._entrarEnModoStandalone();
        }

        let res;
        try {
            res = await fetch('backend/auth_sesion.php?action=sesion', {
                credentials: 'same-origin',
                cache: 'no-store'
            });
        } catch (err) {
            // El servidor que sirvió la página ya no contesta
            authState.cargando = false;
            irAPantallaAcceso('sin-servidor');
            return null;
        }

        const cuerpo = await res.text().catch(() => '');
        let json = null;
        try {
            json = JSON.parse(cuerpo);
        } catch (err) {
            json = null;
        }

        if (json === null || typeof json !== 'object') {
            // No hay PHP: el servidor estático entrega el .php sin ejecutar, o
            // el backend no existe. Cualquier otra respuesta sin JSON (como la
            // página de un error fatal de PHP) es un fallo y no abre la demo.
            const sinPhp = res.status === 404 || (res.ok && cuerpo.trimStart().startsWith('<?php'));
            if (sinPhp) {
                return this._entrarEnModoStandalone();
            }
            authState.cargando = false;
            irAPantallaAcceso('sin-servidor');
            return null;
        }

        authState.cargando = false;

        if (!res.ok || !json.success) {
            irAPantallaAcceso('sin-servidor');
            return null;
        }

        authState.modo = 'servidor';
        authState.sesion = json.data || null;

        if (!authState.sesion) {
            irAPantallaAcceso();
            return null;
        }
        notificarCambioDeSesion();
        return authState.sesion;
    },

    _entrarEnModoStandalone() {
        authState.modo = 'standalone';
        authState.sesion = SESION_STANDALONE;
        authState.cargando = false;
        notificarCambioDeSesion();
        return authState.sesion;
    },

    /** Cierra la sesión y devuelve al usuario a la pantalla de acceso */
    async cerrarSesion() {
        if (authState.modo === 'servidor') {
            try {
                await fetch('backend/auth_sesion.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });
            } catch (err) {
                console.error(err);
            }
        }
        authState.sesion = null;
        irAPantallaAcceso();
    }
};

// ─────────────────────────────────────────────────────────
// PANTALLA DE ACCESO
// El inicio de sesión vive en login.html, que llama a
// backend/auth_sesion.php. Esta página solo redirige allí.
// ─────────────────────────────────────────────────────────
let _redirigiendoAlAcceso = false;

/** motivo: 'expirada' para que login.html explique por qué volvió a pedir acceso */
function irAPantallaAcceso(motivo) {
    if (_redirigiendoAlAcceso) return;
    _redirigiendoAlAcceso = true;
    window.location.replace('login.html' + (motivo ? '?motivo=' + encodeURIComponent(motivo) : ''));
}

// ─────────────────────────────────────────────────────────
// IDENTIDAD EN LA BARRA SUPERIOR
// ─────────────────────────────────────────────────────────
function renderizarIdentidadUsuario() {
    const sesion  = authState.sesion;
    const avatar  = document.getElementById('avatar-usuario');
    const nombre  = document.getElementById('nombre-usuario');
    const rol     = document.getElementById('rol-usuario');
    const salir   = document.getElementById('btn-cerrar-sesion');

    if (avatar) avatar.innerText = sesion ? (sesion.iniciales || 'PC') : '··';
    if (nombre) nombre.innerText = sesion ? sesion.nombre_completo : 'Sin sesión';

    if (rol) {
        if (!sesion) {
            rol.innerText = 'Identifícate para continuar';
        } else if (sesion.alcance_global) {
            rol.innerText = `${sesion.rol_nombre} · Todas las sucursales`;
        } else {
            const total = (sesion.sucursales || []).length;
            rol.innerText = `${sesion.rol_nombre} · ${total} punto(s) de venta`;
        }
    }

    if (salir) {
        salir.classList.toggle('hidden', !sesion || authState.modo !== 'servidor');
    }
}

/** Avisa al resto de la aplicación de que la sesión cambió */
function notificarCambioDeSesion() {
    renderizarIdentidadUsuario();
    document.dispatchEvent(new CustomEvent('patterncare:sesion', { detail: authState.sesion }));
}

// ─────────────────────────────────────────────────────────
// AVISOS DE ACCESO
// ─────────────────────────────────────────────────────────
let _avisoAccesoTimeout = null;

function mostrarAvisoAcceso(mensaje, tipo = 'error') {
    const caja = document.getElementById('aviso-acceso');
    if (!caja) return;

    const estilos = {
        error: 'bg-rose-50 border-rose-300 text-rose-700',
        info:  'bg-blue-50 border-blue-300 text-bc-blue',
        ok:    'bg-emerald-50 border-emerald-300 text-emerald-700'
    };
    const iconos = { error: 'fa-shield-halved', info: 'fa-circle-info', ok: 'fa-circle-check' };

    caja.className = 'fixed top-4 right-4 z-[110] max-w-sm rounded-lg px-4 py-3 text-xs shadow-lg border no-print ' + (estilos[tipo] || estilos.error);
    caja.innerHTML = `
        <div class="flex items-start gap-2">
            <i class="fa-solid ${iconos[tipo] || iconos.error} mt-0.5"></i>
            <span>${mensaje}</span>
        </div>
    `;

    clearTimeout(_avisoAccesoTimeout);
    _avisoAccesoTimeout = setTimeout(() => caja.classList.add('hidden'), 6000);
}

// ─────────────────────────────────────────────────────────
// PETICIONES CON SESIÓN
// Envuelve a fetch() para enviar la cookie de sesión y traducir
// las respuestas 401 / 403 / 503 del middleware a la interfaz.
// ─────────────────────────────────────────────────────────
async function fetchConSesion(url, opciones = {}) {
    const res = await fetch(url, Object.assign({ credentials: 'same-origin' }, opciones));

    if (res.status === 401) {
        authState.sesion = null;
        irAPantallaAcceso('expirada');
    } else if (res.status === 403 || res.status === 503) {
        let mensaje = res.status === 403
            ? 'No tienes permiso para esa operación.'
            : 'La base de datos no está disponible. Intenta de nuevo en unos minutos.';
        try {
            const cuerpo = await res.clone().json();
            if (cuerpo && cuerpo.message) mensaje = cuerpo.message;
        } catch (err) {
            // Respuesta sin cuerpo JSON: se conserva el mensaje genérico
        }
        mostrarAvisoAcceso(mensaje, 'error');
    }

    return res;
}
