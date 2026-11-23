// Proteccion de rutas por permiso y administracion de accesos a puntos de venta.

// ─────────────────────────────────────────────────────────
// PROTECCIÓN DE RUTAS
// El permiso de cada módulo se declara en data-permiso, sobre
// el propio botón del ribbon.
// ─────────────────────────────────────────────────────────
function botonDeModulo(key) {
    return document.querySelector('.bc-tab-btn[data-target="modulo-' + key + '"]');
}

function moduloPermitido(key) {
    const boton = botonDeModulo(key);
    const permiso = boton ? boton.dataset.permiso : null;
    if (!permiso) return true;
    return PatternCareAuth.tienePermiso(permiso);
}

function primerModuloPermitido() {
    const boton = Array.from(document.querySelectorAll('.bc-tab-btn[data-target]'))
        .find(b => !b.dataset.permiso || PatternCareAuth.tienePermiso(b.dataset.permiso));
    return boton ? boton.dataset.target.replace('modulo-', '') : null;
}

function aplicarProteccionDeRutas() {
    document.querySelectorAll('.bc-tab-btn[data-target]').forEach(boton => {
        const permiso = boton.dataset.permiso;
        const permitido = !permiso || PatternCareAuth.tienePermiso(permiso);
        boton.classList.toggle('hidden', !permitido);
    });

    if (!PatternCareAuth.autenticado()) return;

    const seccionVisible = document.querySelector('.bc-seccion:not(.hidden)');
    const claveActual = seccionVisible ? seccionVisible.id.replace('modulo-', '') : null;

    if (claveActual && moduloPermitido(claveActual)) return;

    const destino = primerModuloPermitido();
    if (destino) {
        cambiarModulo(destino);
    } else {
        mostrarAvisoAcceso('Tu rol no tiene ningún módulo habilitado. Contacta a la Encargada General.', 'error');
    }
}

// ─────────────────────────────────────────────────────────
// SUCURSAL ACTIVA ACOTADA A LA ASIGNACIÓN
// ─────────────────────────────────────────────────────────
function actualizarEtiquetasSucursal(sucursalId) {
    const txt = document.getElementById('txt-sucursal-actual');
    const lblVista = document.getElementById('lbl-vista-activa');
    const nombre = sucursalId === null ? 'Todas las Sucursales' : obtenerNombreSucursal(sucursalId);

    if (txt) txt.innerText = nombre;
    if (lblVista) lblVista.innerText = sucursalId === null ? 'Consolidada' : nombre;
}

function ajustarSucursalActivaSegunSesion() {
    const permitidas = PatternCareAuth.sucursalesPermitidas();
    if (permitidas === null || permitidas.length === 0) return;

    const actual = bcState.sucursalActivaId;
    if (actual !== null && permitidas.includes(parseInt(actual, 10))) return;

    const preferida = PatternCareAuth.usuario ? PatternCareAuth.usuario.sucursal_default_id : null;
    const destino = (preferida !== null && permitidas.includes(parseInt(preferida, 10)))
        ? parseInt(preferida, 10)
        : permitidas[0];

    bcState.sucursalActivaId = destino;
    actualizarEtiquetasSucursal(destino);
    renderizarDropdownSucursales();
}

/** Deja constancia en el servidor de cuál es la sucursal activa */
async function sincronizarSucursalConServidor(sucursalId) {
    if (PatternCareAuth.modo !== 'servidor') return;
    try {
        await fetchConSesion('backend/auth_sesion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'seleccionar_sucursal', sucursal_id: sucursalId })
        });
    } catch (err) {
        console.error(err);
    }
}

// La sesión manda: cada vez que cambia se recalculan rutas y sucursal
document.addEventListener('patterncare:sesion', () => {
    ajustarSucursalActivaSegunSesion();
    aplicarProteccionDeRutas();
});

// ─────────────────────────────────────────────────────────
// MÓDULO DE USUARIOS Y ACCESOS
// ─────────────────────────────────────────────────────────
const accesosState = {
    usuarios: [],
    sucursales: [],
    editando: null,     // usuario cuya asignación se está editando
    seleccion: {}       // { sucursalId: { puede_vender, puede_ajustar_inventario } }
};

async function cargarAccesos() {
    const cuerpo = document.getElementById('tabla-accesos-body');
    if (!cuerpo) return;

    if (PatternCareAuth.modo !== 'servidor') {
        cuerpo.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-gray-500">La administración de accesos requiere el backend activo (MySQL).</td></tr>';
        return;
    }

    cuerpo.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-gray-500">Cargando usuarios...</td></tr>';

    try {
        const [resUsuarios, resCatalogos] = await Promise.all([
            fetchConSesion('backend/gestionar_usuarios.php?action=listar'),
            fetchConSesion('backend/gestionar_usuarios.php?action=catalogos')
        ]);

        const jsonUsuarios  = await resUsuarios.json();
        const jsonCatalogos = await resCatalogos.json();

        if (!jsonUsuarios.success) {
            cuerpo.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-rose-600">${jsonUsuarios.message}</td></tr>`;
            return;
        }

        accesosState.usuarios   = jsonUsuarios.data || [];
        accesosState.sucursales = (jsonCatalogos.data && jsonCatalogos.data.sucursales) || [];

        renderizarTablaAccesos();
        cargarBitacoraAccesos();
    } catch (err) {
        cuerpo.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-rose-600">No fue posible consultar los accesos.</td></tr>';
    }
}

function etiquetasDeSucursales(usuario) {
    if (usuario.alcance_global) {
        return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-bc-blue border border-blue-200">Todas las sucursales</span>';
    }
    if (!usuario.sucursales || usuario.sucursales.length === 0) {
        return '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">Sin asignación</span>';
    }
    return usuario.sucursales.map(s => {
        const marca = s.puede_ajustar_inventario
            ? '<i class="fa-solid fa-pen-to-square text-[9px] ml-1 text-emerald-600" title="Puede ajustar existencias"></i>'
            : '';
        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-50 text-teal-700 border border-teal-200 mr-1 mb-1">${s.nombre}${marca}</span>`;
    }).join('');
}

function renderizarTablaAccesos() {
    const cuerpo = document.getElementById('tabla-accesos-body');
    if (!cuerpo) return;

    if (accesosState.usuarios.length === 0) {
        cuerpo.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-gray-500">Sin usuarios registrados.</td></tr>';
        return;
    }

    const puedeEditar = PatternCareAuth.tienePermiso('usuarios.gestionar');

    cuerpo.innerHTML = accesosState.usuarios.map(u => {
        const activo = u.estado === 'Activo';
        const badgeEstado = activo
            ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Activo</span>'
            : '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 border border-gray-300">Inactivo</span>';

        const acciones = puedeEditar
            ? `
                <button onclick="abrirModalAsignacion(${u.id})" class="px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 text-bc-blue border border-blue-200 font-semibold transition" title="Asignar puntos de venta">
                    <i class="fa-solid fa-store text-[11px]"></i>
                </button>
                <button onclick="alternarEstadoUsuario(${u.id}, '${activo ? 'Inactivo' : 'Activo'}')" class="px-2 py-1 rounded bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-300 font-semibold transition" title="${activo ? 'Desactivar cuenta' : 'Activar cuenta'}">
                    <i class="fa-solid ${activo ? 'fa-user-slash' : 'fa-user-check'} text-[11px]"></i>
                </button>
              `
            : '<span class="text-[10px] text-gray-400">Solo lectura</span>';

        return `
            <tr class="hover:bg-gray-50">
                <td class="p-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-bc-blue text-white flex items-center justify-center font-bold text-[10px]">${(u.nombre_completo || '').substring(0, 2).toUpperCase()}</div>
                        <div>
                            <div class="font-semibold text-gray-900">${u.nombre_completo}</div>
                            <div class="text-[10px] text-gray-500 font-mono">@${u.usuario}</div>
                        </div>
                    </div>
                </td>
                <td class="p-3 text-gray-700">${u.rol_nombre}</td>
                <td class="p-3">${etiquetasDeSucursales(u)}</td>
                <td class="p-3 text-center">${badgeEstado}</td>
                <td class="p-3 text-gray-500">${u.ultimo_acceso ? u.ultimo_acceso.substring(0, 16) : 'Nunca'}</td>
                <td class="p-3 text-center whitespace-nowrap">${acciones}</td>
            </tr>
        `;
    }).join('');
}

async function cargarBitacoraAccesos() {
    const cuerpo = document.getElementById('tabla-bitacora-body');
    if (!cuerpo) return;

    try {
        const res = await fetchConSesion('backend/gestionar_usuarios.php?action=bitacora&limite=25');
        const json = await res.json();
        const filas = (json.success && json.data) ? json.data : [];

        if (filas.length === 0) {
            cuerpo.innerHTML = '<tr><td class="p-6 text-center text-gray-500">Sin movimientos registrados.</td></tr>';
            return;
        }

        const iconos = {
            login_ok:        'fa-right-to-bracket text-emerald-600',
            login_fallido:   'fa-triangle-exclamation text-amber-600',
            logout:          'fa-right-from-bracket text-gray-500',
            acceso_denegado: 'fa-ban text-rose-600'
        };

        cuerpo.innerHTML = filas.map(f => `
            <tr class="hover:bg-gray-50">
                <td class="p-2.5 w-8 text-center"><i class="fa-solid ${iconos[f.evento] || 'fa-circle-info text-gray-400'} text-xs"></i></td>
                <td class="p-2.5 font-mono text-gray-700">${f.usuario || 'anónimo'}</td>
                <td class="p-2.5 text-gray-600">${f.evento.replace('_', ' ')}${f.permiso ? ' · ' + f.permiso : ''}</td>
                <td class="p-2.5 text-gray-500">${f.recurso || ''}</td>
                <td class="p-2.5 text-gray-500 text-right">${f.fecha ? f.fecha.substring(0, 16) : ''}</td>
            </tr>
        `).join('');
    } catch (err) {
        cuerpo.innerHTML = '<tr><td class="p-6 text-center text-gray-500">Bitácora no disponible.</td></tr>';
    }
}

// ─────────────────────────────────────────────────────────
// ASIGNACIÓN DE PUNTOS DE VENTA
// ─────────────────────────────────────────────────────────
function abrirModalAsignacion(usuarioId) {
    const usuario = accesosState.usuarios.find(u => u.id === usuarioId);
    if (!usuario) return;

    accesosState.editando = usuario;
    accesosState.seleccion = {};
    (usuario.sucursales || []).forEach(s => {
        accesosState.seleccion[s.sucursal_id] = {
            puede_vender: !!s.puede_vender,
            puede_ajustar_inventario: !!s.puede_ajustar_inventario
        };
    });

    const subtitulo = document.getElementById('asignacion-subtitulo');
    if (subtitulo) {
        subtitulo.innerText = usuario.alcance_global
            ? `${usuario.nombre_completo} · su rol ya tiene alcance global sobre todas las sucursales`
            : `${usuario.nombre_completo} · ${usuario.rol_nombre}`;
    }

    renderizarListaAsignacion();
    document.getElementById('modal-asignacion').classList.remove('hidden');
}

function cerrarModalAsignacion() {
    document.getElementById('modal-asignacion').classList.add('hidden');
    accesosState.editando = null;
}

function renderizarListaAsignacion() {
    const lista = document.getElementById('lista-asignacion-sucursales');
    if (!lista) return;

    lista.innerHTML = accesosState.sucursales.map(s => {
        const id = parseInt(s.id, 10);
        const sel = accesosState.seleccion[id];
        const activa = !!sel;

        return `
            <div class="rounded-lg border ${activa ? 'border-bc-blue bg-blue-50/40' : 'border-gray-200'} px-3 py-2.5 transition">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" ${activa ? 'checked' : ''} onchange="alternarSucursalAsignacion(${id}, this.checked)" class="rounded border-gray-300 text-bc-blue focus:ring-bc-blue">
                    <span class="font-semibold text-gray-800">${s.nombre}</span>
                    <span class="text-[10px] text-gray-500">${s.tipo}</span>
                </label>
                <div class="flex items-center gap-4 mt-2 ml-6 ${activa ? '' : 'opacity-40 pointer-events-none'}">
                    <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-gray-600">
                        <input type="checkbox" ${activa && sel.puede_vender ? 'checked' : ''} onchange="marcarPermisoAsignacion(${id}, 'puede_vender', this.checked)" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                        <span>Puede vender</span>
                    </label>
                    <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-gray-600">
                        <input type="checkbox" ${activa && sel.puede_ajustar_inventario ? 'checked' : ''} onchange="marcarPermisoAsignacion(${id}, 'puede_ajustar_inventario', this.checked)" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Puede ajustar existencias</span>
                    </label>
                </div>
            </div>
        `;
    }).join('');
}

function alternarSucursalAsignacion(sucursalId, activa) {
    if (activa) {
        accesosState.seleccion[sucursalId] = { puede_vender: true, puede_ajustar_inventario: false };
    } else {
        delete accesosState.seleccion[sucursalId];
    }
    renderizarListaAsignacion();
}

function marcarPermisoAsignacion(sucursalId, campo, valor) {
    if (!accesosState.seleccion[sucursalId]) return;
    accesosState.seleccion[sucursalId][campo] = valor;
}

async function guardarAsignacionSucursales() {
    const usuario = accesosState.editando;
    if (!usuario) return;

    const boton = document.getElementById('btn-guardar-asignacion');
    if (boton) boton.disabled = true;

    const sucursales = Object.keys(accesosState.seleccion).map(id => ({
        sucursal_id: parseInt(id, 10),
        puede_vender: accesosState.seleccion[id].puede_vender ? 1 : 0,
        puede_ajustar_inventario: accesosState.seleccion[id].puede_ajustar_inventario ? 1 : 0
    }));

    try {
        const res = await fetchConSesion('backend/gestionar_usuarios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'asignar_sucursales', id: usuario.id, sucursales: sucursales })
        });
        const json = await res.json();

        if (json.success) {
            mostrarAvisoAcceso(json.message, 'ok');
            cerrarModalAsignacion();
            cargarAccesos();
        } else {
            mostrarAvisoAcceso(json.message, 'error');
        }
    } catch (err) {
        mostrarAvisoAcceso('No fue posible guardar la asignación.', 'error');
    }

    if (boton) boton.disabled = false;
}

async function alternarEstadoUsuario(usuarioId, estado) {
    try {
        const res = await fetchConSesion('backend/gestionar_usuarios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'cambiar_estado', id: usuarioId, estado: estado })
        });
        const json = await res.json();

        if (json.success) {
            mostrarAvisoAcceso(json.message, 'ok');
            cargarAccesos();
        } else {
            mostrarAvisoAcceso(json.message, 'error');
        }
    } catch (err) {
        mostrarAvisoAcceso('No fue posible cambiar el estado de la cuenta.', 'error');
    }
}
