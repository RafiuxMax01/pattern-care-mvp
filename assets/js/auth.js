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

    /** Lee la sesión del servidor al arrancar la aplicación */
    async inicializar() {
        authState.cargando = true;
        try {
            const res = await fetch('backend/auth_sesion.php?action=sesion', {
                credentials: 'same-origin',
                cache: 'no-store'
            });
            if (!res.ok) throw new Error('Respuesta no válida del servidor de sesión');

            const json = await res.json();
            authState.modo = 'servidor';
            authState.sesion = json.data || null;
        } catch (err) {
            // index.html abierto con doble clic: no hay backend que consultar
            authState.modo = 'standalone';
            authState.sesion = SESION_STANDALONE;
        }
        authState.cargando = false;

        if (authState.sesion) {
            ocultarPantallaAcceso();
        } else {
            mostrarPantallaAcceso();
        }
        notificarCambioDeSesion();
        return authState.sesion;
    },

    /** Inicia sesión con usuario y contraseña */
    async iniciarSesion(usuario, password) {
        const res = await fetch('backend/auth_sesion.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', usuario: usuario, password: password })
        });
        const json = await res.json();

        if (!json.success) {
            return { ok: false, mensaje: json.message || 'No fue posible iniciar sesión.' };
        }

        authState.modo = 'servidor';
        authState.sesion = json.data;
        ocultarPantallaAcceso();
        notificarCambioDeSesion();
        return { ok: true, mensaje: json.message };
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
        mostrarPantallaAcceso();
        notificarCambioDeSesion();
    }
};

// ─────────────────────────────────────────────────────────
// PANTALLA DE ACCESO
// ─────────────────────────────────────────────────────────
function mostrarPantallaAcceso() {
    const caja = document.getElementById('pantalla-acceso');
    if (!caja) return;
    caja.classList.remove('hidden');
    caja.classList.add('flex');

    const usuario = document.getElementById('acceso-usuario');
    if (usuario) setTimeout(() => usuario.focus(), 60);
}

function ocultarPantallaAcceso() {
    const caja = document.getElementById('pantalla-acceso');
    if (!caja) return;
    caja.classList.add('hidden');
    caja.classList.remove('flex');

    const error = document.getElementById('acceso-error');
    if (error) error.classList.add('hidden');
    const password = document.getElementById('acceso-password');
    if (password) password.value = '';
}

async function enviarFormularioAcceso(evento) {
    evento.preventDefault();

    const usuario  = document.getElementById('acceso-usuario').value.trim();
    const password = document.getElementById('acceso-password').value;
    const error    = document.getElementById('acceso-error');
    const boton    = document.getElementById('btn-acceso');
    const texto    = document.getElementById('btn-acceso-texto');

    error.classList.add('hidden');
    boton.disabled = true;
    texto.innerText = 'Verificando...';

    try {
        const resultado = await PatternCareAuth.iniciarSesion(usuario, password);
        if (!resultado.ok) {
            error.innerText = resultado.mensaje;
            error.classList.remove('hidden');
        } else {
            mostrarAvisoAcceso(resultado.mensaje, 'ok');
            sincronizarDatos();
        }
    } catch (err) {
        error.innerText = 'No fue posible contactar al servidor de sesión.';
        error.classList.remove('hidden');
    }

    boton.disabled = false;
    texto.innerText = 'Entrar';
    return false;
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
// las respuestas 401 / 403 del middleware a la interfaz.
// ─────────────────────────────────────────────────────────
async function fetchConSesion(url, opciones = {}) {
    const res = await fetch(url, Object.assign({ credentials: 'same-origin' }, opciones));

    if (res.status === 401) {
        authState.sesion = null;
        mostrarPantallaAcceso();
        notificarCambioDeSesion();
        mostrarAvisoAcceso('Tu sesión terminó. Vuelve a identificarte.', 'info');
    } else if (res.status === 403) {
        let mensaje = 'No tienes permiso para esa operación.';
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
