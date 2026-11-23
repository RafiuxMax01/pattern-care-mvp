// Motor principal: bcState, inventario, transferencias, produccion, mermas y rendimiento.

        let bcState = {
            modoBackend: 'detectando',
            sucursalActivaId: null,
            sucursales: [
                { id: 1, codigo: 'MAT-01', nombre: 'Matriz & Planta de Confección', tipo: 'Planta / Taller Central', encargado: 'Dueña / Encargada General', telefono: '55-1234-5678', direccion: 'Parque Industrial Textil, Nave 4' },
                { id: 2, codigo: 'SUC-HOSP', nombre: 'Sucursal Hospital General', tipo: 'Punto de Venta / Showroom', encargado: 'Área Administrativa', telefono: '55-8765-4321', direccion: 'Av. Médicos 405, Frente a Hospital Regional' },
                { id: 3, codigo: 'SUC-NORTE', nombre: 'Sucursal Zona Norte (Clínica Palmas)', tipo: 'Punto de Venta / Showroom', encargado: 'Corte y Calidad [2]', telefono: '55-9988-7766', direccion: 'Plaza Médica Las Palmas, Local 12' },
                { id: 4, codigo: 'ALM-DIG', nombre: 'Almacén Digital & Envíos WhatsApp', tipo: 'Almacén Digital', encargado: 'Área Administrativa', telefono: '55-4433-2211', direccion: 'Bodega Central de Logística y Paquetería' }
            ],
            productos: [],
            inventarioDetalle: [],
            transferencias: [],
            ordenes: [],
            mermas: [],
            rendimientos: [],
            kpis: {},
            alertasCriticas: [],
            skuSeleccionadoId: null,
            chartInstance: null
        };

        const FASES_PRODUCCION = ['Diseño', 'Abastecimiento', 'Impresión', 'Corte', 'Confección', 'Empaque', 'Distribución'];

        const DATOS_SEMILLA_BC = {
            productos: [
                { id: 1, sku: 'GQ-AZC-U', nombre: 'Gorro Quirúrgico Clásico Antifluido', categoria: 'Gorros Quirúrgicos', color: 'Azul Cielo', talla: 'Unitalla', coleccion: 'Clásica Hospitalaria', stock_minimo_general: 6, precio_unitario: 120.00, notas: 'Alta rotación hospitalaria' },
                { id: 2, sku: 'GQ-EST-OD', nombre: 'Gorro Quirúrgico Estampado Odonto', categoria: 'Gorros Quirúrgicos', color: 'Multicolor', talla: 'Unitalla', coleccion: 'Odontología Smile', stock_minimo_general: 6, precio_unitario: 150.00, notas: 'Estampado en taller externo' },
                { id: 3, sku: 'GQ-LIL-U', nombre: 'Gorro Quirúrgico Quirófano Pastel', categoria: 'Gorros Quirúrgicos', color: 'Lila', talla: 'Unitalla', coleccion: 'Colección Pastel', stock_minimo_general: 6, precio_unitario: 130.00, notas: 'Venta activa WhatsApp' },
                { id: 4, sku: 'CH-BLA-M', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Blanco Óptico', talla: 'M', coleccion: 'Línea Premier', stock_minimo_general: 6, precio_unitario: 680.00, notas: 'Desabasto de cierres y tela' },
                { id: 5, sku: 'CH-AZM-L', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Azul Marino', talla: 'L', coleccion: 'Línea Premier', stock_minimo_general: 6, precio_unitario: 680.00, notas: 'Prenda premier' },
                { id: 6, sku: 'CH-VER-S', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Verde Quirófano', talla: 'S', coleccion: 'Línea Premier', stock_minimo_general: 6, precio_unitario: 680.00, notas: 'Bajo inventario' },
                { id: 7, sku: 'CP-NEG-U', nombre: 'Cubrepolvo Impermeable con Ajuste', categoria: 'Cubrepolvos', color: 'Negro Oxford', talla: 'Unitalla', coleccion: 'Protección Total', stock_minimo_general: 6, precio_unitario: 320.00, notas: 'Riesgo inminente de agotamiento' },
                { id: 8, sku: 'CP-AZM-U', nombre: 'Cubrepolvo Quirúrgico Repelente', categoria: 'Cubrepolvos', color: 'Azul Marino', talla: 'Unitalla', coleccion: 'Protección Total', stock_minimo_general: 6, precio_unitario: 320.00, notas: 'Demanda constante' }
            ],
            inventarioDetalle: [
                { producto_id: 1, sucursal_id: 1, stock_actual: 14, stock_minimo: 6 },
                { producto_id: 1, sucursal_id: 2, stock_actual: 2, stock_minimo: 6 },
                { producto_id: 1, sucursal_id: 3, stock_actual: 5, stock_minimo: 6 },
                { producto_id: 1, sucursal_id: 4, stock_actual: 8, stock_minimo: 6 },

                { producto_id: 2, sucursal_id: 1, stock_actual: 20, stock_minimo: 6 },
                { producto_id: 2, sucursal_id: 2, stock_actual: 8, stock_minimo: 6 },
                { producto_id: 2, sucursal_id: 3, stock_actual: 7, stock_minimo: 6 },
                { producto_id: 2, sucursal_id: 4, stock_actual: 12, stock_minimo: 6 },

                { producto_id: 3, sucursal_id: 1, stock_actual: 12, stock_minimo: 6 },
                { producto_id: 3, sucursal_id: 2, stock_actual: 4, stock_minimo: 6 },
                { producto_id: 3, sucursal_id: 3, stock_actual: 6, stock_minimo: 6 },
                { producto_id: 3, sucursal_id: 4, stock_actual: 15, stock_minimo: 6 },

                { producto_id: 4, sucursal_id: 1, stock_actual: 5, stock_minimo: 6 },
                { producto_id: 4, sucursal_id: 2, stock_actual: 2, stock_minimo: 6 },
                { producto_id: 4, sucursal_id: 3, stock_actual: 1, stock_minimo: 6 },
                { producto_id: 4, sucursal_id: 4, stock_actual: 3, stock_minimo: 6 },

                { producto_id: 5, sucursal_id: 1, stock_actual: 15, stock_minimo: 6 },
                { producto_id: 5, sucursal_id: 2, stock_actual: 7, stock_minimo: 6 },
                { producto_id: 5, sucursal_id: 3, stock_actual: 6, stock_minimo: 6 },
                { producto_id: 5, sucursal_id: 4, stock_actual: 9, stock_minimo: 6 },

                { producto_id: 6, sucursal_id: 1, stock_actual: 8, stock_minimo: 6 },
                { producto_id: 6, sucursal_id: 2, stock_actual: 3, stock_minimo: 6 },
                { producto_id: 6, sucursal_id: 3, stock_actual: 4, stock_minimo: 6 },
                { producto_id: 6, sucursal_id: 4, stock_actual: 5, stock_minimo: 6 },

                { producto_id: 7, sucursal_id: 1, stock_actual: 6, stock_minimo: 6 },
                { producto_id: 7, sucursal_id: 2, stock_actual: 1, stock_minimo: 6 },
                { producto_id: 7, sucursal_id: 3, stock_actual: 2, stock_minimo: 6 },
                { producto_id: 7, sucursal_id: 4, stock_actual: 3, stock_minimo: 6 },

                { producto_id: 8, sucursal_id: 1, stock_actual: 18, stock_minimo: 6 },
                { producto_id: 8, sucursal_id: 2, stock_actual: 8, stock_minimo: 6 },
                { producto_id: 8, sucursal_id: 3, stock_actual: 7, stock_minimo: 6 },
                { producto_id: 8, sucursal_id: 4, stock_actual: 10, stock_minimo: 6 }
            ],
            transferencias: [
                { id: 1, codigo_transferencia: 'TRF-2026-001', sucursal_origen_id: 1, sucursal_destino_id: 2, sucursal_origen: 'Matriz & Planta', sucursal_destino: 'Sucursal Hospital', producto_id: 1, sku: 'GQ-AZC-U', producto_nombre: 'Gorro Quirúrgico Clásico', cantidad: 10, estado: 'Recibido', responsable: 'Área Administrativa', notas: 'Reabastecimiento urgente de showroom', fecha_transferencia: '2026-10-12 09:30' },
                { id: 2, codigo_transferencia: 'TRF-2026-002', sucursal_origen_id: 1, sucursal_destino_id: 4, sucursal_origen: 'Matriz & Planta', sucursal_destino: 'Almacén Digital', producto_id: 2, sku: 'GQ-EST-OD', producto_nombre: 'Gorro Quirúrgico Odonto', cantidad: 15, estado: 'Recibido', responsable: 'Dueña / Encargada General', notas: 'Pedidos por catálogo WhatsApp', fecha_transferencia: '2026-10-12 11:15' },
                { id: 3, codigo_transferencia: 'TRF-2026-003', sucursal_origen_id: 1, sucursal_destino_id: 3, sucursal_origen: 'Matriz & Planta', sucursal_destino: 'Sucursal Zona Norte', producto_id: 5, sku: 'CH-AZM-L', producto_nombre: 'Chamarra Médica Premier', cantidad: 6, estado: 'En Tránsito', responsable: 'Corte y Calidad [2]', notas: 'Traspaso de Chamarras L', fecha_transferencia: '2026-10-13 08:20' }
            ],
            ordenes: [
                { id: 1, codigo_orden: 'ORD-2026-001', producto: 'Gorro Quirúrgico Odonto Smile', lote: 'LOT-GQ-01', cantidad: 120, fase_actual: 'Impresión', estado: 'Detenido / Alerta', responsable: 'Área Administrativa', notas: 'Retraso de 48 horas en taller de estampado externo.' },
                { id: 2, codigo_orden: 'ORD-2026-002', producto: 'Chamarra Médica Premier Azul Marino', lote: 'LOT-CH-04', cantidad: 50, fase_actual: 'Confección', estado: 'En Proceso', responsable: 'Confección [2]', notas: 'Ensamble de cremalleras en Planta.' },
                { id: 3, codigo_orden: 'ORD-2026-003', producto: 'Cubrepolvo Impermeable Negro', lote: 'LOT-CP-02', cantidad: 80, fase_actual: 'Corte', estado: 'En Proceso', responsable: 'Corte y Calidad [2]', notas: 'Trazado con tela CRO.' },
                { id: 4, codigo_orden: 'ORD-2026-004', producto: 'Gorro Quirúrgico Clásico Azul Cielo', lote: 'LOT-GQ-03', cantidad: 150, fase_actual: 'Empaque', estado: 'En Proceso', responsable: 'Corte y Calidad [2]', notas: 'Empaque final para distribución.' }
            ],
            mermas: [
                { id: 1, fecha: '2026-10-08', descripcion_error: 'Tono fuera de escala por falta de muestra autorizada en taller externo', tipo: 'Merma', area: 'Impresión externa', material_desperdiciado: '18 gorros cortados', costo_impacto: 810.00, lote_afectado: 'LOT-GQ-01', registrado_por: 'Corte y Calidad [2]' },
                { id: 2, fecha: '2026-10-10', descripcion_error: 'Descuadre en costura de cierre por tensión overlock', tipo: 'Retrabajo', area: 'Confección', material_desperdiciado: 'Tiempo de descosido + 3 cierres', costo_impacto: 240.00, lote_afectado: 'LOT-CH-04', registrado_por: 'Dueña / Encargada General' },
                { id: 3, fecha: '2026-10-11', descripcion_error: 'Error en etiquetado de tallas en empaque', tipo: 'Retrabajo', area: 'Empaque', material_desperdiciado: '40 bolsas reabiertas', costo_impacto: 120.00, lote_afectado: 'LOT-GQ-03', registrado_por: 'Área Administrativa' },
                { id: 4, fecha: '2026-10-12', descripcion_error: 'Melladura en tela al cortar sin fijación de plantilla', tipo: 'Merma', area: 'Corte', material_desperdiciado: '2.5 metros tela CRO', costo_impacto: 450.00, lote_afectado: 'LOT-CP-02', registrado_por: 'Corte y Calidad [2]' }
            ],
            rendimientos: [
                { id: 1, fecha: '2026-10-12 10:30', tipo_tela: 'SRA', metros: 20, piezas_estimadas: 120, sobrante_cm: 0, lote_referencia: 'LOT-GQ-01', color: 'Multicolor', coleccion: 'Odonto Smile' },
                { id: 2, fecha: '2026-10-12 11:45', tipo_tela: 'CRO', metros: 20, piezas_estimadas: 80, sobrante_cm: 1000, lote_referencia: 'LOT-CP-02', color: 'Negro', coleccion: 'Protección Total' }
            ]
        };

        document.addEventListener('DOMContentLoaded', async () => {
            // El contexto de autenticación decide qué se puede pedir y mostrar
            await PatternCareAuth.inicializar();

            renderizarDropdownSucursales();
            cargarSucursalesAdmin();
            verDetalleFase(1);
            calcularRendimientoEnVivo();

            if (PatternCareAuth.autenticado()) {
                sincronizarDatos();
            }
        });

        async function sincronizarDatos() {
            const syncIcon = document.getElementById('sync-icon');
            if (syncIcon) syncIcon.classList.add('fa-spin');
            const badgeDiv = document.getElementById('bc-db-badge');

            const urlParams = bcState.sucursalActivaId ? `?sucursal_id=${bcState.sucursalActivaId}` : '';

            try {
                const res = await fetchConSesion(`backend/obtener_datos.php${urlParams}`, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (res.ok) {
                    const json = await res.json();
                    if (json.success && json.data) {
                        bcState.modoBackend = 'mysql';
                        bcState.sucursales = json.data.sucursales || bcState.sucursales;
                        bcState.productos = json.data.skus_planos || [];
                        bcState.inventarioDetalle = json.data.inventario_detalle || [];
                        bcState.transferencias = json.data.transferencias || [];
                        bcState.ordenes = json.data.ordenes || [];
                        bcState.mermas = json.data.mermas || [];
                        bcState.rendimientos = json.data.rendimientos || [];
                        bcState.kpis = json.data.kpis || {};
                        bcState.alertasCriticas = json.data.alertas_criticas || [];

                        badgeDiv.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-300';
                        badgeDiv.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400"></span><span class="hidden sm:inline">MySQL Conectado (IIS/PDO)</span>';

                        renderizarTodo();
                        if (syncIcon) syncIcon.classList.remove('fa-spin');
                        return;
                    }
                }
                throw new Error('Servidor offline');
            } catch (err) {
                bcState.modoBackend = 'local_storage';
                cargarDesdeLocalStorage();

                badgeDiv.className = 'flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-300 cursor-pointer';
                badgeDiv.title = 'Haz clic para inicializar MySQL cuando el servicio esté activo';
                badgeDiv.onclick = instalarBaseDatos;
                badgeDiv.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-400"></span><span class="hidden sm:inline">Modo Standalone / Local</span>';

                renderizarTodo();
            }

            if (syncIcon) syncIcon.classList.remove('fa-spin');
        }

        function cargarDesdeLocalStorage() {
            const raw = localStorage.getItem('pattern_care_bc_data');
            if (raw) {
                try {
                    const parsed = JSON.parse(raw);
                    bcState.sucursales = parsed.sucursales || DATOS_SEMILLA_BC.sucursales || bcState.sucursales;
                    bcState.productos = parsed.productos || DATOS_SEMILLA_BC.productos;
                    bcState.inventarioDetalle = parsed.inventarioDetalle || DATOS_SEMILLA_BC.inventarioDetalle;
                    bcState.transferencias = parsed.transferencias || DATOS_SEMILLA_BC.transferencias;
                    bcState.ordenes = parsed.ordenes || DATOS_SEMILLA_BC.ordenes;
                    bcState.mermas = parsed.mermas || DATOS_SEMILLA_BC.mermas;
                    bcState.rendimientos = parsed.rendimientos || DATOS_SEMILLA_BC.rendimientos;
                } catch(e) {
                    asignarSemilla();
                }
            } else {
                asignarSemilla();
                guardarEnLocalStorage();
            }

            procesarDatosLocales();
        }

        function asignarSemilla() {
            bcState.productos = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.productos));
            bcState.inventarioDetalle = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.inventarioDetalle));
            bcState.transferencias = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.transferencias));
            bcState.ordenes = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.ordenes));
            bcState.mermas = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.mermas));
            bcState.rendimientos = JSON.parse(JSON.stringify(DATOS_SEMILLA_BC.rendimientos));
        }

        function guardarEnLocalStorage() {
            localStorage.setItem('pattern_care_bc_data', JSON.stringify({
                sucursales: bcState.sucursales,
                productos: bcState.productos,
                inventarioDetalle: bcState.inventarioDetalle,
                transferencias: bcState.transferencias,
                ordenes: bcState.ordenes,
                mermas: bcState.mermas,
                rendimientos: bcState.rendimientos
            }));
            procesarDatosLocales();
        }

        function procesarDatosLocales() {
            const mapa = {};
            bcState.inventarioDetalle.forEach(inv => {
                if (!mapa[inv.producto_id]) {
                    mapa[inv.producto_id] = { total: 0, sucursales: {} };
                }
                mapa[inv.producto_id].total += parseInt(inv.stock_actual);
                mapa[inv.producto_id].sucursales[inv.sucursal_id] = {
                    sucursal_id: inv.sucursal_id,
                    stock_actual: parseInt(inv.stock_actual),
                    stock_minimo: parseInt(inv.stock_minimo || 6)
                };
            });

            let skusCriticos = 0;
            let stockTotalGlobal = 0;

            bcState.productos.forEach(p => {
                const dist = mapa[p.id] || { total: 0, sucursales: {} };
                p.stock_total = dist.total;
                p.desglose_sucursales = dist.sucursales;

                if (bcState.sucursalActivaId !== null) {
                    const sucData = dist.sucursales[bcState.sucursalActivaId] || { stock_actual: 0, stock_minimo: 6 };
                    p.stock_actual = sucData.stock_actual;
                    p.stock_minimo = sucData.stock_minimo;
                    p.es_critico = p.stock_actual < p.stock_minimo;
                } else {
                    p.stock_actual = dist.total;
                    p.stock_minimo = parseInt(p.stock_minimo_general || 6);
                    p.es_critico = dist.total < parseInt(p.stock_minimo_general || 6);
                }

                if (p.es_critico) skusCriticos++;
                stockTotalGlobal += p.stock_actual;
            });

            let costoMermas = 0;
            bcState.mermas.forEach(m => costoMermas += parseFloat(m.costo_impacto || 0));

            const trfTransito = bcState.transferencias.filter(t => t.estado === 'En Tránsito').length;

            bcState.kpis = {
                sucursales_conteo: bcState.sucursales.length,
                personal_total: 6,
                productos_activos: bcState.productos.length,
                stock_total_piezas: stockTotalGlobal,
                skus_bajo_minimo: skusCriticos,
                transferencias_transito: trfTransito,
                total_transferencias: bcState.transferencias.length,
                ordenes_activas: bcState.ordenes.filter(o => o.estado === 'En Proceso').length,
                costo_total_mermas: costoMermas.toFixed(2)
            };

            bcState.alertasCriticas = [
                { area: 'Logística Externa', titulo: 'Cuello de Botella: Taller de Impresión/Sublimado', descripcion: 'Dependencia de terceros sin SLA para gorros estampados. Retrasos de 48-72h impactan las entregas hospitalarias.', severidad: 'alta' },
                { area: 'Abastecimiento Multi-Sucursal', titulo: 'Riesgo de Desabasto en Sucursal Hospital General', descripcion: 'Stock de Gorros Azules (2 pzas) y Chamarras (2 pzas) en punto de venta crítico por debajo de la norma de 6 piezas.', severidad: 'critica' },
                { area: 'Infraestructura Tecnológica', titulo: 'Ausencia de Respaldos en la Nube', descripcion: 'Vulnerabilidad ante pérdida de datos en equipos locales sin respaldo automatizado centralizado.', severidad: 'critica' },
                { area: 'Control de Calidad', titulo: 'Merma Oculta en Confección y Empaque', descripcion: 'Pérdidas acumuladas por desajustes mecánicos y retrazado de tela CRO no costeadas formalmente.', severidad: 'media' }
            ];
        }


const API_SUCURSALES_ADMIN = 'backend/gestionar_sucursales.php';

function escaparTextoSucursalAdmin(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, caracter => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    })[caracter]);
}

function mostrarMensajeSucursalAdmin(mensaje, error = false) {
    const elemento = document.getElementById('sucursales-mensaje');
    if (!elemento) return;

    elemento.textContent = mensaje;
    elemento.style.color = error ? '#dc2626' : '#16a34a';
}

async function peticionSucursalAdmin(url, opciones = {}) {
    const respuesta = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            ...(opciones.body ? { 'Content-Type': 'application/json' } : {})
        },
        ...opciones
    });

    let resultado;

    try {
        resultado = await respuesta.json();
    } catch {
        throw new Error('El servidor no devolvió una respuesta JSON válida.');
    }

    if (!respuesta.ok || resultado.success === false) {
        throw new Error(
            resultado.message ||
            resultado.mensaje ||
            `Error HTTP ${respuesta.status}`
        );
    }

    return resultado;
}

async function cargarSucursalesAdmin() {
    const cuerpo = document.getElementById('tabla-sucursales-admin');
    if (!cuerpo) return;

    cuerpo.innerHTML = '<tr><td colspan="5">Cargando sucursales...</td></tr>';

    try {
        const resultado = await peticionSucursalAdmin(
            `${API_SUCURSALES_ADMIN}?accion=listar`
        );

        const sucursales = resultado.data?.sucursales ?? [];

        if (!sucursales.length) {
            cuerpo.innerHTML =
                '<tr><td colspan="5">No hay sucursales registradas.</td></tr>';
            return;
        }

        cuerpo.innerHTML = sucursales.map(sucursal => {
            const id = Number(sucursal.id);
            const estado = String(sucursal.estado ?? 'Activo');
            const siguienteEstado = estado === 'Activo' ? 'Inactivo' : 'Activo';
            const textoAccion = siguienteEstado === 'Inactivo'
                ? 'Desactivar'
                : 'Reactivar';

            return `
                <tr>
                    <td>
                        <strong>${escaparTextoSucursalAdmin(sucursal.codigo)}</strong><br>
                        ${escaparTextoSucursalAdmin(sucursal.nombre)}
                    </td>
                    <td>${escaparTextoSucursalAdmin(sucursal.tipo)}</td>
                    <td>
                        ${escaparTextoSucursalAdmin(sucursal.encargado)}<br>
                        ${escaparTextoSucursalAdmin(sucursal.telefono || 'Sin teléfono')}
                    </td>
                    <td>${escaparTextoSucursalAdmin(estado)}</td>
                    <td>
                        <button type="button"
                            onclick="editarSucursalAdmin(${id})">Editar</button>
                        <button type="button"
                            onclick="cambiarEstadoSucursalAdmin(${id}, '${siguienteEstado}')">
                            ${textoAccion}
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

    } catch (error) {
        cuerpo.innerHTML =
            '<tr><td colspan="5">No se pudieron cargar las sucursales.</td></tr>';
        mostrarMensajeSucursalAdmin(error.message, true);
    }
}

function nuevaSucursal() {
    const formulario = document.getElementById('form-sucursal-admin');
    if (!formulario) return;

    formulario.reset();
    document.getElementById('admin-sucursal-id').value = '';

    mostrarMensajeSucursalAdmin('Formulario listo para registrar una sucursal.');
    document.getElementById('admin-sucursal-codigo')?.focus();
}

function cancelarEdicionSucursal() {
    const formulario = document.getElementById('form-sucursal-admin');
    if (!formulario) return;

    formulario.reset();
    document.getElementById('admin-sucursal-id').value = '';
    mostrarMensajeSucursalAdmin('Edición cancelada.');
}

async function editarSucursalAdmin(id) {
    try {
        const resultado = await peticionSucursalAdmin(
            `${API_SUCURSALES_ADMIN}?accion=obtener&id=${encodeURIComponent(id)}`
        );

        const sucursal = resultado.data;

        if (!sucursal || typeof sucursal !== 'object') {
            throw new Error('No se encontraron los datos de la sucursal.');
        }

        document.getElementById('admin-sucursal-id').value = sucursal.id ?? '';
        document.getElementById('admin-sucursal-codigo').value = sucursal.codigo ?? '';
        document.getElementById('admin-sucursal-nombre').value = sucursal.nombre ?? '';
        document.getElementById('admin-sucursal-tipo').value = sucursal.tipo ?? '';
        document.getElementById('admin-sucursal-encargado').value = sucursal.encargado ?? '';
        document.getElementById('admin-sucursal-telefono').value = sucursal.telefono ?? '';
        document.getElementById('admin-sucursal-direccion').value = sucursal.direccion ?? '';

        mostrarMensajeSucursalAdmin(
            `Editando la sucursal ${sucursal.codigo ?? ''}.`
        );

        document.getElementById('admin-sucursal-codigo')?.focus();

    } catch (error) {
        mostrarMensajeSucursalAdmin(error.message, true);
    }
}

async function guardarSucursalAdmin(event) {
    event.preventDefault();

    const id = document.getElementById('admin-sucursal-id').value;

    const sucursal = {
        codigo: document.getElementById('admin-sucursal-codigo').value.trim(),
        nombre: document.getElementById('admin-sucursal-nombre').value.trim(),
        tipo: document.getElementById('admin-sucursal-tipo').value,
        encargado: document.getElementById('admin-sucursal-encargado').value.trim(),
        telefono: document.getElementById('admin-sucursal-telefono').value.trim(),
        direccion: document.getElementById('admin-sucursal-direccion').value.trim()
    };

    if (id) {
        sucursal.id = Number(id);
    }

    try {
        const resultado = await peticionSucursalAdmin(API_SUCURSALES_ADMIN, {
            method: id ? 'PUT' : 'POST',
            body: JSON.stringify(sucursal)
        });

        mostrarMensajeSucursalAdmin(
            resultado.message || (id
                ? 'Sucursal actualizada correctamente.'
                : 'Sucursal registrada correctamente.')
        );

        document.getElementById('form-sucursal-admin').reset();
        document.getElementById('admin-sucursal-id').value = '';

        await cargarSucursalesAdmin();

        if (typeof sincronizarDatos === 'function') {
            await sincronizarDatos();
        }

    } catch (error) {
        mostrarMensajeSucursalAdmin(error.message, true);
    }
}

async function cambiarEstadoSucursalAdmin(id, estado) {
    const accion = estado === 'Inactivo' ? 'desactivar' : 'reactivar';

    if (!confirm(`¿Seguro que deseas ${accion} esta sucursal?`)) {
        return;
    }

    try {
        const resultado = await peticionSucursalAdmin(API_SUCURSALES_ADMIN, {
            method: 'POST',
            body: JSON.stringify({
                accion: 'cambiar_estado',
                id: Number(id),
                estado
            })
        });

        mostrarMensajeSucursalAdmin(
            resultado.message || `Sucursal ${accion === 'desactivar' ? 'desactivada' : 'reactivada'} correctamente.`
        );

        await cargarSucursalesAdmin();

        if (typeof sincronizarDatos === 'function') {
            await sincronizarDatos();
        }

    } catch (error) {
        mostrarMensajeSucursalAdmin(error.message, true);
    }
}


        function renderizarTodo() {
            renderizarHeadline();
            renderizarCues();
            renderizarGraficaInventario();
            renderizarAlertasRoleCenter();
            renderizarTablaInventario();
            renderizarTablaTransferencias();
            renderizarGridOrdenes();
            renderizarTablaMermas();
            renderizarTablaRendimientos();
            renderizarTarjetasSucursales();
            actualizarSelectoresTransferencia();

            if (bcState.skuSeleccionadoId) {
                mostrarFactBoxProducto(bcState.skuSeleccionadoId);
            } else if (bcState.productos.length > 0) {
                mostrarFactBoxProducto(bcState.productos[0].id);
            }
        }

        function renderizarHeadline() {
            const h = document.getElementById('headline-texto');
            if (!h) return;
            const criticos = bcState.kpis.skus_bajo_minimo || 0;
            const trf = bcState.kpis.transferencias_transito || 0;
            const sucTxt = bcState.sucursalActivaId 
                ? `en ${obtenerNombreSucursal(bcState.sucursalActivaId)}` 
                : 'en la red de 4 sucursales';

            h.innerText = `Buenos días, Encargada General. Hay ${criticos} productos con stock crítico (< 6 pzas) ${sucTxt} y ${trf} traspasos en tránsito.`;
        }

        function renderizarCues() {
            const kpis = bcState.kpis;
            document.getElementById('cue-skus-criticos').innerText = kpis.skus_bajo_minimo || 0;
            document.getElementById('cue-transferencias').innerText = kpis.total_transferencias || 0;
            document.getElementById('cue-ordenes').innerText = kpis.ordenes_activas || 0;
            document.getElementById('cue-mermas-costo').innerText = `$${Number(kpis.costo_total_mermas || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 })}`;
            document.getElementById('cue-mermas-conteo').innerText = `${bcState.mermas.length} registros en bitácora`;

            const notif = document.getElementById('badge-notif-count');
            if (notif) notif.innerText = kpis.skus_bajo_minimo || 0;

            const lblContexto = document.getElementById('lbl-cues-contexto');
            if (lblContexto) {
                lblContexto.innerText = `Filtro: ${bcState.sucursalActivaId ? obtenerNombreSucursal(bcState.sucursalActivaId) : 'Todas las Sucursales (Consolidado)'}`;
            }
        }

        function renderizarAlertasRoleCenter() {
            const cont = document.getElementById('rolecenter-alertas-lista');
            if (!cont) return;

            let html = '';
            (bcState.alertasCriticas || []).slice(0, 3).forEach(a => {
                const colorBorder = a.severidad === 'critica' ? 'border-rose-300 bg-rose-50' : 'border-amber-300 bg-amber-50';
                const colorBadge = a.severidad === 'critica' ? 'text-rose-400 bg-rose-50' : 'text-amber-400 bg-amber-50';
                html += `
                    <div class="p-3 rounded-lg border ${colorBorder} text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] uppercase font-bold tracking-wider ${colorBadge} px-1.5 py-0.5 rounded">${a.area}</span>
                            <span class="text-[10px] text-gray-500 uppercase font-semibold">${a.severidad}</span>
                        </div>
                        <h4 class="font-semibold text-gray-900 mt-1">${a.titulo}</h4>
                        <p class="text-[11px] text-gray-600 mt-0.5">${a.descripcion}</p>
                    </div>
                `;
            });
            cont.innerHTML = html;
        }

        function renderizarGraficaInventario() {
            const ctx = document.getElementById('chartInventario');
            if (!ctx) return;

            const labels = bcState.productos.map(p => p.sku);
            const dataStock = bcState.productos.map(p => p.stock_actual);
            const dataMinimo = bcState.productos.map(p => p.stock_minimo);
            const colors = dataStock.map((val, idx) => {
                return val < dataMinimo[idx] ? 'rgba(244, 63, 94, 0.85)' : 'rgba(0, 120, 212, 0.85)';
            });

            if (bcState.chartInstance) {
                bcState.chartInstance.destroy();
            }

            bcState.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Stock en Sucursal / Vista',
                            data: dataStock,
                            backgroundColor: colors,
                            borderRadius: 4
                        },
                        {
                            label: 'Umbral de Seguridad (6 pzas)',
                            data: dataMinimo,
                            type: 'line',
                            borderColor: '#f59e0b',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            pointRadius: 3,
                            pointBackgroundColor: '#f59e0b'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { color: 'rgba(229, 231, 235, 0.8)' },
                            ticks: { color: '#6b7280', font: { size: 10 } }
                        },
                        y: {
                            grid: { color: 'rgba(229, 231, 235, 0.8)' },
                            ticks: { color: '#6b7280', font: { size: 10 }, stepSize: 2 }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#374151', font: { size: 11 } }
                        }
                    }
                }
            });
        }

        function toggleDropdownSucursal() {
            const d = document.getElementById('dropdown-sucursales');
            d.classList.toggle('hidden');
        }

        document.addEventListener('click', (e) => {
            const btn = document.getElementById('btn-selector-sucursal');
            const d = document.getElementById('dropdown-sucursales');
            if (btn && d && !btn.contains(e.target) && !d.contains(e.target)) {
                d.classList.add('hidden');
            }
        });

        function renderizarDropdownSucursales() {
            const cont = document.getElementById('lista-opciones-sucursal');
            if (!cont) return;

            // Protección de rutas (SCRUM-22): solo se listan los puntos de venta asignados
            const permitidas = typeof PatternCareAuth !== 'undefined' ? PatternCareAuth.sucursalesPermitidas() : null;
            const sucursalesVisibles = permitidas === null
                ? bcState.sucursales
                : bcState.sucursales.filter(s => permitidas.includes(parseInt(s.id, 10)));

            // La vista consolidada de todas las sucursales es exclusiva del alcance global
            let html = permitidas !== null ? '' : `
                <button onclick="seleccionarSucursalGlobal(null)" class="w-full text-left px-2.5 py-1.5 rounded hover:bg-gray-100 transition flex items-center justify-between ${bcState.sucursalActivaId === null ? 'bg-blue-50 text-bc-blue font-bold' : 'text-gray-600'}">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-globe text-xs"></i>
                        <span>Todas las Sucursales (Consolidado)</span>
                    </span>
                    ${bcState.sucursalActivaId === null ? '<i class="fa-solid fa-check text-xs"></i>' : ''}
                </button>
            `;

            sucursalesVisibles.forEach(s => {
                const activo = bcState.sucursalActivaId == s.id;
                html += `
                    <button onclick="seleccionarSucursalGlobal(${s.id})" class="w-full text-left px-2.5 py-1.5 rounded hover:bg-gray-100 transition flex items-center justify-between ${activo ? 'bg-blue-50 text-bc-blue font-bold' : 'text-gray-600'}">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-building text-xs text-teal-400"></i>
                            <span class="truncate">${s.nombre}</span>
                        </span>
                        ${activo ? '<i class="fa-solid fa-check text-xs"></i>' : ''}
                    </button>
                `;
            });

            cont.innerHTML = html;
        }

        function seleccionarSucursalGlobal(sucursalId) {
            // Protección de rutas (SCRUM-22): la sucursal activa debe estar asignada
            if (typeof PatternCareAuth !== 'undefined') {
                if (sucursalId === null && !PatternCareAuth.esGlobal()) {
                    mostrarAvisoAcceso('Tu rol solo puede trabajar sobre sus puntos de venta asignados.', 'error');
                    toggleDropdownSucursal();
                    return;
                }
                if (sucursalId !== null && !PatternCareAuth.puedeEnSucursal(sucursalId, 'ver')) {
                    mostrarAvisoAcceso('No tienes asignado ese punto de venta.', 'error');
                    toggleDropdownSucursal();
                    return;
                }
                sincronizarSucursalConServidor(sucursalId);
            }

            bcState.sucursalActivaId = sucursalId;
            toggleDropdownSucursal();

            const txt = document.getElementById('txt-sucursal-actual');
            const lblVista = document.getElementById('lbl-vista-activa');

            if (sucursalId === null) {
                txt.innerText = 'Todas las Sucursales';
                lblVista.innerText = 'Consolidada';
            } else {
                const nombre = obtenerNombreSucursal(sucursalId);
                txt.innerText = nombre;
                lblVista.innerText = nombre;
            }

            renderizarDropdownSucursales();
            sincronizarDatos();
        }

        function obtenerNombreSucursal(id) {
            const s = bcState.sucursales.find(x => x.id == id);
            return s ? s.nombre : 'Desconocida';
        }

        function renderizarTablaInventario() {
            const tbody = document.getElementById('tabla-inventario-body');
            if (!tbody) return;

            const busqueda = (document.getElementById('filtro-sku-busqueda')?.value || '').toLowerCase();
            const catFiltro = document.getElementById('filtro-sku-categoria')?.value || '';
            const estFiltro = document.getElementById('filtro-sku-estado')?.value || '';

            const filtrados = bcState.productos.filter(p => {
                const matchTexto = p.sku.toLowerCase().includes(busqueda) || 
                                   p.nombre.toLowerCase().includes(busqueda) || 
                                   p.color.toLowerCase().includes(busqueda);
                const matchCat = catFiltro === '' || p.categoria === catFiltro;
                let matchEst = true;
                if (estFiltro === 'critico') matchEst = p.es_critico;
                if (estFiltro === 'optimo') matchEst = !p.es_critico;
                return matchTexto && matchCat && matchEst;
            });

            if (filtrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-6 text-center text-gray-500">No se encontraron productos con los filtros seleccionados.</td></tr>`;
                return;
            }

            let html = '';
            filtrados.forEach(p => {
                const badge = p.es_critico
                    ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-400 border border-rose-200 flex items-center gap-1 justify-center w-max mx-auto"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>Crítico (&lt;6)</span>`
                    : `<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-400 border border-emerald-200 w-max mx-auto">Óptimo</span>`;

                const isSelected = bcState.skuSeleccionadoId == p.id;

                html += `
                    <tr onclick="mostrarFactBoxProducto(${p.id})" class="cursor-pointer transition hover:bg-gray-100 ${isSelected ? 'bg-blue-50 border-l-4 border-l-bc-blue' : ''} ${p.es_critico ? 'bg-rose-50' : ''}">
                        <td class="p-3 font-mono font-bold text-gray-900">${p.sku}</td>
                        <td class="p-3">
                            <div class="font-medium text-gray-900">${p.nombre}</div>
                            <div class="text-[11px] text-gray-500">${p.notas || 'Sin notas'}</div>
                        </td>
                        <td class="p-3 text-gray-600">${p.categoria}</td>
                        <td class="p-3"><span class="text-gray-900">${p.color}</span> / <span class="text-gray-500">${p.talla}</span></td>
                        <td class="p-3 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                <button onclick="ajustarStockRapido(event, ${p.id}, -1)" class="w-5 h-5 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">-</button>
                                <span class="font-bold text-sm ${p.es_critico ? 'text-rose-400' : 'text-gray-900'}">${p.stock_actual}</span>
                                <button onclick="ajustarStockRapido(event, ${p.id}, 1)" class="w-5 h-5 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold">+</button>
                            </div>
                        </td>
                        <td class="p-3 text-center font-bold text-gray-700">${p.stock_total || p.stock_actual}</td>
                        <td class="p-3 text-center text-gray-500 font-semibold">${p.stock_minimo}</td>
                        <td class="p-3 text-center">${badge}</td>
                        <td class="p-3 text-right space-x-1 whitespace-nowrap">
                            <button onclick="iniciarTransferenciaDesdeSKU(event, ${p.id})" title="Transferir a otra sucursal" class="p-1.5 rounded bg-gray-100 hover:bg-cyan-50 text-cyan-400 transition">
                                <i class="fa-solid fa-truck-ramp-box"></i>
                            </button>
                            <button onclick="editarSKU(event, ${p.id})" title="Editar Ficha" class="p-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button onclick="eliminarSKU(event, ${p.id})" title="Eliminar" class="p-1.5 rounded bg-gray-100 hover:bg-rose-50 text-rose-400 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function filtrarTablaInventario() {
            renderizarTablaInventario();
        }

        function filtrarTablaPorCriticos() {
            const sel = document.getElementById('filtro-sku-estado');
            if (sel) {
                sel.value = 'critico';
                renderizarTablaInventario();
            }
        }

        function mostrarFactBoxProducto(productoId) {
            bcState.skuSeleccionadoId = productoId;
            const p = bcState.productos.find(x => x.id == productoId);
            const cont = document.getElementById('factbox-contenido');
            if (!p || !cont) return;

            document.querySelectorAll('#tabla-inventario-body tr').forEach(r => r.classList.remove('bg-blue-50', 'border-l-4', 'border-l-bc-blue'));

            let sucursalesRows = '';
            bcState.sucursales.forEach(s => {
                let stockEnSuc = 0;
                let minEnSuc = 6;

                const detalle = bcState.inventarioDetalle.find(d => d.producto_id == p.id && d.sucursal_id == s.id);
                if (detalle) {
                    stockEnSuc = parseInt(detalle.stock_actual);
                    minEnSuc = parseInt(detalle.stock_minimo || 6);
                } else if (p.desglose_sucursales && p.desglose_sucursales[s.id]) {
                    stockEnSuc = p.desglose_sucursales[s.id].stock_actual;
                    minEnSuc = p.desglose_sucursales[s.id].stock_minimo;
                }

                const esCritico = stockEnSuc < minEnSuc;

                sucursalesRows += `
                    <div class="p-2 rounded bg-gray-50 border border-gray-200 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-medium text-gray-700">${s.nombre}</div>
                            <div class="text-[10px] text-gray-400">${s.codigo} &bull; Mínimo: ${minEnSuc}</div>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-sm ${esCritico ? 'text-rose-400 font-extrabold' : 'text-gray-700'}">${stockEnSuc} pzas</span>
                            ${esCritico ? '<div class="text-[9px] text-rose-400 font-semibold">Alerta &lt;6</div>' : ''}
                        </div>
                    </div>
                `;
            });

            cont.innerHTML = `
                <div>
                    <span class="text-[10px] font-mono text-bc-blue font-bold">${p.sku}</span>
                    <h4 class="font-bold text-gray-900 text-sm mt-0.5">${p.nombre}</h4>
                    <div class="text-gray-500 text-[11px] mt-0.5">${p.categoria} &bull; ${p.color} (${p.talla})</div>
                </div>

                <div class="pt-2 border-t border-gray-200">
                    <span class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Desglose por Sucursales</span>
                    <div class="space-y-1.5 mt-2">
                        ${sucursalesRows}
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-200 flex items-center justify-between font-bold text-xs">
                    <span class="text-gray-600">Total en Toda la Red:</span>
                    <span class="text-base text-emerald-400">${p.stock_total || p.stock_actual} pzas</span>
                </div>

                <div class="pt-2 space-y-1.5">
                    <button onclick="iniciarTransferenciaDesdeSKU(null, ${p.id})" class="w-full py-1.5 rounded bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-truck-ramp-box"></i>
                        <span>Transferir este Producto</span>
                    </button>
                    <button onclick="editarSKU(null, ${p.id})" class="w-full py-1.5 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs flex items-center justify-center gap-1.5 border border-gray-300 transition">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Editar Ficha de SKU</span>
                    </button>
                </div>
            `;
        }

        async function ajustarStockRapido(e, id, delta) {
            if (e) e.stopPropagation();
            const sucursalId = bcState.sucursalActivaId || 1;

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'ajustar_stock', id: id, sucursal_id: sucursalId, delta: delta })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        return;
                    }
                } catch(err) {
                    console.error(err);
                }
            }

            let detalle = bcState.inventarioDetalle.find(d => d.producto_id == id && d.sucursal_id == sucursalId);
            if (detalle) {
                detalle.stock_actual = Math.max(0, parseInt(detalle.stock_actual) + delta);
            } else {
                bcState.inventarioDetalle.push({ producto_id: id, sucursal_id: sucursalId, stock_actual: Math.max(0, delta), stock_minimo: 6 });
            }
            guardarEnLocalStorage();
            renderizarTodo();
        }

        function renderizarTablaTransferencias() {
            const tbody = document.getElementById('tabla-transferencias-body');
            if (!tbody) return;

            if (bcState.transferencias.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-6 text-center text-gray-500">Sin órdenes de transferencia registradas.</td></tr>`;
                return;
            }

            let html = '';
            bcState.transferencias.forEach(t => {
                const badge = t.estado === 'En Tránsito'
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 flex items-center gap-1 w-max mx-auto"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>En Tránsito</span>'
                    : '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-400 border border-emerald-200 w-max mx-auto">Recibido</span>';

                html += `
                    <tr class="hover:bg-gray-100">
                        <td class="p-3 font-mono font-bold text-cyan-400">${t.codigo_transferencia}</td>
                        <td class="p-3 text-gray-500">${t.fecha_transferencia ? t.fecha_transferencia.substring(0, 16) : 'Reciente'}</td>
                        <td class="p-3 font-medium text-gray-700">${t.sucursal_origen || obtenerNombreSucursal(t.sucursal_origen_id)}</td>
                        <td class="p-3 font-medium text-teal-700">${t.sucursal_destino || obtenerNombreSucursal(t.sucursal_destino_id)}</td>
                        <td class="p-3">
                            <span class="font-mono text-gray-900">${t.sku || ''}</span>
                            <div class="text-[11px] text-gray-500">${t.producto_nombre || ''}</div>
                        </td>
                        <td class="p-3 text-center font-bold text-gray-900 text-sm">${t.cantidad} pzas</td>
                        <td class="p-3 text-center">${badge}</td>
                        <td class="p-3 text-gray-500">${t.responsable}</td>
                        <td class="p-3 text-gray-600">${t.notas || 'N/A'}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function abrirModalTransferencia(productoId = null) {
            actualizarSelectoresTransferencia(productoId);
            document.getElementById('modal-transferencia').classList.remove('hidden');
        }

        function cerrarModalTransferencia() {
            document.getElementById('modal-transferencia').classList.add('hidden');
        }

        function iniciarTransferenciaDesdeSKU(e, id) {
            if (e) e.stopPropagation();
            abrirModalTransferencia(id);
        }

        function actualizarSelectoresTransferencia(prodSeleccionadoId = null) {
            const selProd = document.getElementById('trf-producto');
            if (!selProd) return;

            let html = '';
            bcState.productos.forEach(p => {
                const sel = (prodSeleccionadoId && p.id == prodSeleccionadoId) ? 'selected' : '';
                html += `<option value="${p.id}" ${sel}>${p.sku} &mdash; ${p.nombre}</option>`;
            });
            selProd.innerHTML = html;
            actualizarMaxTransferible();
        }

        function actualizarMaxTransferible() {
            const origenId = parseInt(document.getElementById('trf-origen')?.value || 1);
            const prodId = parseInt(document.getElementById('trf-producto')?.value || 1);
            const hint = document.getElementById('trf-disponible-hint');

            const detalle = bcState.inventarioDetalle.find(d => d.producto_id == prodId && d.sucursal_id == origenId);
            const disp = detalle ? parseInt(detalle.stock_actual) : 0;

            if (hint) {
                hint.innerText = `Disponible en origen: ${disp} piezas`;
            }
        }

        async function guardarTransferencia(e) {
            e.preventDefault();
            const origenId = parseInt(document.getElementById('trf-origen').value);
            const destinoId = parseInt(document.getElementById('trf-destino').value);
            const prodId = parseInt(document.getElementById('trf-producto').value);
            const cantidad = parseInt(document.getElementById('trf-cantidad').value);

            if (origenId === destinoId) {
                Swal.fire('Error', 'La sucursal de origen y destino no pueden ser iguales.', 'warning');
                return;
            }

            const payload = {
                action: 'transferir',
                sucursal_origen_id: origenId,
                sucursal_destino_id: destinoId,
                producto_id: prodId,
                cantidad: cantidad,
                estado: document.getElementById('trf-estado').value,
                responsable: document.getElementById('trf-responsable').value,
                notas: document.getElementById('trf-notas').value.trim()
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/transferir_stock.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (!json.success) {
                        Swal.fire('Error', json.message || 'No fue posible transferir.', 'error');
                        return;
                    }
                    cerrarModalTransferencia();
                    sincronizarDatos();
                    Swal.fire({ icon: 'success', title: 'Transferencia Procesada', text: 'Stock actualizado en ambas sucursales.', timer: 1500, showConfirmButton: false });
                    return;
                } catch(err) { console.error(err); }
            }

            let detOrigen = bcState.inventarioDetalle.find(d => d.producto_id == prodId && d.sucursal_id == origenId);
            if (!detOrigen || parseInt(detOrigen.stock_actual) < cantidad) {
                Swal.fire('Stock Insuficiente', 'No hay suficientes piezas en la sucursal de origen.', 'error');
                return;
            }

            detOrigen.stock_actual = parseInt(detOrigen.stock_actual) - cantidad;

            if (payload.estado === 'Recibido') {
                let detDest = bcState.inventarioDetalle.find(d => d.producto_id == prodId && d.sucursal_id == destinoId);
                if (detDest) {
                    detDest.stock_actual = parseInt(detDest.stock_actual) + cantidad;
                } else {
                    bcState.inventarioDetalle.push({ producto_id: prodId, sucursal_id: destinoId, stock_actual: cantidad, stock_minimo: 6 });
                }
            }

            const p = bcState.productos.find(x => x.id == prodId);
            bcState.transferencias.unshift({
                id: Date.now(),
                codigo_transferencia: 'TRF-' + new Date().getFullYear() + '-' + Math.floor(100 + Math.random() * 900),
                sucursal_origen_id: origenId,
                sucursal_destino_id: destinoId,
                sucursal_origen: obtenerNombreSucursal(origenId),
                sucursal_destino: obtenerNombreSucursal(destinoId),
                producto_id: prodId,
                sku: p ? p.sku : '',
                producto_nombre: p ? p.nombre : '',
                cantidad: cantidad,
                estado: payload.estado,
                responsable: payload.responsable,
                notas: payload.notas,
                fecha_transferencia: new Date().toISOString().replace('T', ' ').substring(0, 16)
            });

            guardarEnLocalStorage();
            cerrarModalTransferencia();
            renderizarTodo();
            Swal.fire({ icon: 'success', title: 'Transferencia Registrada', text: 'Traspaso guardado en memoria local.', timer: 1500, showConfirmButton: false });
        }

        function renderizarTarjetasSucursales() {
            const cont = document.getElementById('grid-sucursales-cards');
            if (!cont) return;

            let html = '';
            bcState.sucursales.forEach(s => {
                let totalStockSuc = 0;
                let criticosSuc = 0;

                bcState.inventarioDetalle.forEach(inv => {
                    if (inv.sucursal_id == s.id) {
                        totalStockSuc += parseInt(inv.stock_actual);
                        if (parseInt(inv.stock_actual) < parseInt(inv.stock_minimo || 6)) {
                            criticosSuc++;
                        }
                    }
                });

                html += `
                    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col justify-between hover:border-gray-300 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-bold text-teal-400">${s.codigo}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 text-gray-600">${s.tipo}</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm mt-2">${s.nombre}</h4>
                            <p class="text-[11px] text-gray-500 mt-1"><i class="fa-solid fa-location-dot mr-1"></i>${s.direccion}</p>
                            <p class="text-[11px] text-gray-500"><i class="fa-solid fa-user mr-1"></i>Encargado: <strong class="text-gray-600">${s.encargado}</strong></p>

                            <div class="mt-4 p-2.5 bg-gray-50 rounded-lg border border-gray-200 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">Total Piezas:</span>
                                    <span class="font-bold text-gray-900">${totalStockSuc} pzas</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-500">Items en Alerta (&lt;6):</span>
                                    <span class="font-bold ${criticosSuc > 0 ? 'text-rose-400' : 'text-emerald-400'}">${criticosSuc} SKUs</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between">
                            <button onclick="seleccionarSucursalGlobal(${s.id}); cambiarModulo('inventario');" class="text-xs text-bc-blue hover:underline font-semibold">
                                Ver Inventario &rarr;
                            </button>
                            <button onclick="abrirModalTransferenciaHacia(${s.id})" class="text-[11px] px-2 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-600">
                                Reabastecer
                            </button>
                        </div>
                    </div>
                `;
            });
            cont.innerHTML = html;
        }

        function abrirModalTransferenciaHacia(destinoId) {
            abrirModalTransferencia();
            const d = document.getElementById('trf-destino');
            if (d) d.value = destinoId;
        }

        function abrirModalSKU(id = null) {
            document.getElementById('form-sku').reset();
            document.getElementById('sku-id').value = '';
            document.getElementById('modal-sku-titulo').innerText = 'Ficha de Producto &bull; Nuevo SKU';

            if (id) {
                const p = bcState.productos.find(x => x.id == id);
                if (p) {
                    document.getElementById('modal-sku-titulo').innerText = 'Editar Ficha: ' + p.sku;
                    document.getElementById('sku-id').value = p.id;
                    document.getElementById('sku-codigo').value = p.sku;
                    document.getElementById('sku-categoria').value = p.categoria;
                    document.getElementById('sku-nombre').value = p.nombre;
                    document.getElementById('sku-color').value = p.color;
                    document.getElementById('sku-talla').value = p.talla;
                    document.getElementById('sku-coleccion').value = p.coleccion || '';
                    document.getElementById('sku-stock-actual').value = p.stock_actual;
                    document.getElementById('sku-stock-minimo').value = p.stock_minimo;
                    document.getElementById('sku-notas').value = p.notas || '';
                }
            }
            document.getElementById('modal-sku').classList.remove('hidden');
        }

        function cerrarModalSKU() {
            document.getElementById('modal-sku').classList.add('hidden');
        }

        function editarSKU(e, id) {
            if (e) e.stopPropagation();
            abrirModalSKU(id);
        }

        async function guardarSKU(e) {
            e.preventDefault();
            const id = document.getElementById('sku-id').value;
            const action = id ? 'actualizar' : 'crear';

            const payload = {
                action: action,
                id: id ? parseInt(id) : undefined,
                sku: document.getElementById('sku-codigo').value.trim(),
                categoria: document.getElementById('sku-categoria').value,
                nombre: document.getElementById('sku-nombre').value.trim(),
                color: document.getElementById('sku-color').value.trim(),
                talla: document.getElementById('sku-talla').value.trim(),
                coleccion: document.getElementById('sku-coleccion').value.trim(),
                stock_actual: parseInt(document.getElementById('sku-stock-actual').value),
                stock_minimo: parseInt(document.getElementById('sku-stock-minimo').value),
                sucursal_id: parseInt(document.getElementById('sku-sucursal-inicial')?.value || 1),
                notas: document.getElementById('sku-notas').value.trim()
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (!json.success) {
                        Swal.fire('Error', json.message, 'error');
                        return;
                    }
                    cerrarModalSKU();
                    sincronizarDatos();
                    Swal.fire({ icon: 'success', title: 'Producto Guardado', text: 'Sincronizado en la base de datos de Business Central.', timer: 1500, showConfirmButton: false });
                    return;
                } catch(err) { console.error(err); }
            }

            if (id) {
                const idx = bcState.productos.findIndex(p => p.id == id);
                if (idx !== -1) bcState.productos[idx] = { ...bcState.productos[idx], ...payload };
            } else {
                payload.id = Date.now();
                bcState.productos.push(payload);
                bcState.sucursales.forEach(s => {
                    const q = (s.id == payload.sucursal_id) ? payload.stock_actual : 0;
                    bcState.inventarioDetalle.push({ producto_id: payload.id, sucursal_id: s.id, stock_actual: q, stock_minimo: payload.stock_minimo });
                });
            }

            guardarEnLocalStorage();
            cerrarModalSKU();
            renderizarTodo();
            Swal.fire({ icon: 'success', title: 'Guardado', text: 'Producto actualizado en memoria local.', timer: 1500, showConfirmButton: false });
        }

        async function eliminarSKU(e, id) {
            if (e) e.stopPropagation();
            const conf = await Swal.fire({
                title: '¿Eliminar producto?',
                text: 'Se removerá de todas las sucursales.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Sí, eliminar'
            });
            if (!conf.isConfirmed) return;

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'eliminar', id: id })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        return;
                    }
                } catch(err) { console.error(err); }
            }

            bcState.productos = bcState.productos.filter(p => p.id != id);
            bcState.inventarioDetalle = bcState.inventarioDetalle.filter(d => d.producto_id != id);
            guardarEnLocalStorage();
            renderizarTodo();
        }

        function calcularRendimientoEnVivo() {
            const tipo = document.getElementById('sim-tipo-tela').value;
            const metros = parseFloat(document.getElementById('sim-metros').value) || 0;

            let piezas = 0;
            let sobranteCm = 0;
            let formulaDesc = '';
            let aprovecha = '';

            if (tipo === 'SRA') {
                piezas = Math.floor(metros * 6);
                sobranteCm = 0;
                formulaDesc = 'Rendimiento: 6 piezas exactas por metro de tela SRA (0 cm merma).';
                aprovecha = '100% de la tela aprovechada';
                document.getElementById('res-tipo-titulo').innerText = 'Tela SRA (Gorros Quirúrgicos)';
            } else {
                piezas = Math.floor(metros * 4);
                sobranteCm = metros * 50;
                formulaDesc = 'Rendimiento: 4 piezas y 50 cm de sobrante acumulado por metro.';
                aprovecha = `Aprovechamiento con ~${(sobranteCm / 100).toFixed(2)} m recuperables`;
                document.getElementById('res-tipo-titulo').innerText = 'Tela CRO (Chamarras & Cubrepolvos)';
            }

            document.getElementById('res-formula-desc').innerText = formulaDesc;
            document.getElementById('res-piezas').innerText = piezas;
            document.getElementById('res-sobrante').innerText = sobranteCm;
            document.getElementById('res-sobrante-unidad').innerText = `cm (${(sobranteCm / 100).toFixed(2)} m)`;
            document.getElementById('res-aprovechamiento').innerText = aprovecha;
        }

        async function guardarRendimiento() {
            const tipo = document.getElementById('sim-tipo-tela').value;
            const metros = parseFloat(document.getElementById('sim-metros').value) || 0;
            if (metros <= 0) {
                Swal.fire('Atención', 'Ingresa una cantidad de metros mayor a 0.', 'info');
                return;
            }

            const payload = {
                action: 'calcular_y_guardar',
                tipo_tela: tipo,
                metros: metros,
                color: document.getElementById('sim-color').value.trim() || 'No especificado',
                coleccion: document.getElementById('sim-coleccion').value.trim() || 'General',
                talla: document.getElementById('sim-talla').value,
                lote_referencia: document.getElementById('sim-lote').value.trim() || 'LOT-' + Date.now().toString().slice(-4),
                notas: document.getElementById('sim-notas').value.trim()
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/calcular_rendimiento.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        Swal.fire({ icon: 'success', title: 'Cálculo Registrado', text: 'Guardado en MySQL.', timer: 1500, showConfirmButton: false });
                        return;
                    }
                } catch(e) { console.error(e); }
            }

            const pzas = tipo === 'SRA' ? Math.floor(metros * 6) : Math.floor(metros * 4);
            const sob = tipo === 'SRA' ? 0 : metros * 50;
            bcState.rendimientos.unshift({
                id: Date.now(),
                fecha: new Date().toISOString().replace('T', ' ').substring(0, 16),
                tipo_tela: tipo,
                metros: metros,
                piezas_estimadas: pzas,
                sobrante_cm: sob,
                lote_referencia: payload.lote_referencia,
                color: payload.color,
                coleccion: payload.coleccion
            });
            guardarEnLocalStorage();
            renderizarTablaRendimientos();
            Swal.fire({ icon: 'success', title: 'Cálculo Registrado', text: 'Guardado localmente.', timer: 1500, showConfirmButton: false });
        }

        function renderizarTablaRendimientos() {
            const tbody = document.getElementById('tabla-rendimientos-body');
            if (!tbody) return;

            if (bcState.rendimientos.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-gray-500">Sin consumos de tela calculados.</td></tr>`;
                return;
            }

            let html = '';
            bcState.rendimientos.slice(0, 8).forEach(r => {
                html += `
                    <tr class="hover:bg-gray-100">
                        <td class="p-2.5 text-gray-500 font-mono">${r.fecha ? r.fecha.substring(0, 16) : 'Reciente'}</td>
                        <td class="p-2.5 font-bold ${r.tipo_tela === 'SRA' ? 'text-teal-400' : 'text-amber-400'}">${r.tipo_tela}</td>
                        <td class="p-2.5 text-gray-900 font-semibold">${r.metros} m</td>
                        <td class="p-2.5 text-emerald-400 font-bold">${r.piezas_estimadas} pzas</td>
                        <td class="p-2.5 text-gray-600">${r.sobrante_cm} cm</td>
                        <td class="p-2.5 font-mono text-gray-600">${r.lote_referencia || 'N/A'}</td>
                        <td class="p-2.5 text-gray-500">${r.color || ''}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        const DETALLES_FASES = {
            1: { nombre: 'Fase 1: Diseño & Patronaje', responsable: 'Dueña / Encargada General + Corte y Calidad [2]', tiempo: '24 a 48 hrs', riesgo: 'Falta de graduación estandarizada en chamarras.', mitigacion: 'Patronaje digital base con tolerancias impresas.' },
            2: { nombre: 'Fase 2: Abastecimiento de Materias Primas', responsable: 'Área Administrativa & Dueña', tiempo: '1 a 3 días', riesgo: 'Punto crítico en Chamarras: Desabasto de cierres y tela impermeable.', mitigacion: 'Mantener buffer de avíos en almacén para 20 prendas mínimas.' },
            3: { nombre: 'Fase 3: Impresión / Sublimado (Taller Externo)', responsable: 'Taller Externo tercerizado (Coordinado por Admin)', tiempo: '48 a 72 hrs (CUELLO DE BOTELLA CRÍTICO)', riesgo: 'Retrasos de entrega y merma por sublimado deficiente.', mitigacion: 'Exigir muestra física autorizada previa y acuerdos SLA.' },
            4: { nombre: 'Fase 4: Trazo & Corte de Piezas', responsable: 'Corte y Control de Calidad [2 personas]', tiempo: '2 a 4 hrs por lote de 100', riesgo: 'Desperdicio no controlado en Tela CRO (50 cm sobrante/m).', mitigacion: 'Uso obligatorio del simulador para reutilizar retazos en cintas.' },
            5: { nombre: 'Fase 5: Confección & Ensamble Textil', responsable: 'Confección [2 personas]', tiempo: '15 a 30 min por prenda', riesgo: 'Desajuste de tensión que causa descosidos y retrabajos no contabilizados.', mitigacion: 'Calibración matutina de overlock y registro en bitácora.' },
            6: { nombre: 'Fase 6: Control de Calidad & Empaque', responsable: 'Corte y Control de Calidad [2 personas]', tiempo: '1 hr por lote de 50', riesgo: 'Prendas empacadas con etiquetas de talla cambiadas.', mitigacion: 'Doble chequeo visual con sello de inspector antes del embalaje.' },
            7: { nombre: 'Fase 7: Distribución & Entrega', responsable: 'Área Administrativa & Logística', tiempo: 'Mismo día a 48 hrs', riesgo: 'Falta de número de rastreo vinculado a sucursales.', mitigacion: 'Traspasos registrados en Business Central con código único.' }
        };

        function verDetalleFase(num) {
            const f = DETALLES_FASES[num];
            if (!f) return;
            const cont = document.getElementById('panel-detalle-fase');
            cont.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-200 pb-2 mb-2">
                    <span class="font-heading font-bold text-sm text-bc-blue">${f.nombre}</span>
                    <span class="text-gray-500">Responsable: <strong class="text-gray-900">${f.responsable}</strong></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div><span class="text-gray-500 font-semibold">Tiempo Estimado:</span><div class="text-gray-700">${f.tiempo}</div></div>
                    <div><span class="text-gray-500 font-semibold">Punto Crítico:</span><div class="text-amber-400">${f.riesgo}</div></div>
                    <div><span class="text-gray-500 font-semibold">Plan de Mitigación:</span><div class="text-teal-400">${f.mitigacion}</div></div>
                </div>
            `;
        }

        function seleccionarFlujoTipo(tipo) {
            const btnG = document.getElementById('btn-flujo-gorros');
            const btnC = document.getElementById('btn-flujo-chamarras');
            const nodoFase3 = document.getElementById('nodo-fase-3');

            if (tipo === 'gorros') {
                btnG.className = 'px-3 py-1.5 rounded-lg bg-bc-blue text-white text-xs font-semibold';
                btnC.className = 'px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:text-gray-900';
                nodoFase3.classList.remove('opacity-30', 'grayscale');
                verDetalleFase(3);
            } else {
                btnC.className = 'px-3 py-1.5 rounded-lg bg-teal-600 text-white text-xs font-semibold';
                btnG.className = 'px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:text-gray-900';
                nodoFase3.classList.add('opacity-30', 'grayscale');
                verDetalleFase(2);
            }
        }

        function renderizarGridOrdenes() {
            const grid = document.getElementById('ordenes-grid');
            if (!grid) return;

            if (bcState.ordenes.length === 0) {
                grid.innerHTML = `<div class="p-8 text-center text-gray-500 col-span-3">Sin órdenes de confección activas.</div>`;
                return;
            }

            let html = '';
            bcState.ordenes.forEach(o => {
                const esAlerta = o.estado === 'Detenido / Alerta';
                const badge = esAlerta
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-400 border border-rose-200 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>Detenido</span>'
                    : '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-400 border border-blue-200">En Proceso</span>';

                const fIdx = FASES_PRODUCCION.indexOf(o.fase_actual);
                const nextFase = fIdx < FASES_PRODUCCION.length - 1 ? FASES_PRODUCCION[fIdx + 1] : null;

                html += `
                    <div class="bg-white border ${esAlerta ? 'border-rose-300' : 'border-gray-200'} rounded-xl p-4 flex flex-col justify-between hover:border-gray-300 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-bc-blue">${o.codigo_orden}</span>
                                ${badge}
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 mt-1.5">${o.producto}</h4>
                            <div class="text-xs text-gray-500 mt-0.5">Lote: <strong class="text-gray-600">${o.lote}</strong> &bull; Cantidad: <strong class="text-gray-900">${o.cantidad} pzas</strong></div>

                            <div class="mt-3 p-2 bg-gray-50 rounded border border-gray-200 text-[11px]">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-gray-500">Fase:</span>
                                    <span class="font-bold text-teal-400">${o.fase_actual}</span>
                                </div>
                                <div class="w-full bg-gray-100 h-1 rounded-full overflow-hidden">
                                    <div class="bg-teal-500 h-full" style="width: ${((fIdx + 1) / FASES_PRODUCCION.length) * 100}%"></div>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-1 flex justify-between">
                                    <span>Resp: ${o.responsable}</span>
                                    <span>${fIdx + 1}/7</span>
                                </div>
                            </div>
                            ${o.notas ? `<p class="text-[11px] text-gray-600 mt-2 bg-gray-100 p-1.5 rounded">${o.notas}</p>` : ''}
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-gray-200 flex items-center justify-between">
                            ${nextFase ? `
                                <button onclick="avanzarFaseOrden(${o.id}, '${nextFase}')" class="px-2.5 py-1 rounded bg-teal-600/80 hover:bg-teal-500 text-white text-xs font-semibold flex items-center gap-1">
                                    <span>Avanzar a ${nextFase}</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            ` : `<span class="text-xs text-emerald-400 font-semibold"><i class="fa-solid fa-check"></i> Completada</span>`}
                            <button onclick="toggleAlertaOrden(${o.id})" class="p-1.5 rounded bg-gray-100 text-amber-400 hover:bg-gray-200" title="Alternar Alerta">
                                <i class="fa-solid fa-flag"></i>
                            </button>
                        </div>
                    </div>
                `;
            });
            grid.innerHTML = html;
        }

        function abrirModalOrden() {
            document.getElementById('form-orden').reset();
            document.getElementById('modal-orden').classList.remove('hidden');
        }

        function cerrarModalOrden() {
            document.getElementById('modal-orden').classList.add('hidden');
        }

        async function guardarOrden(e) {
            e.preventDefault();
            const payload = {
                action: 'crear',
                codigo_orden: 'ORD-' + new Date().getFullYear() + '-' + Math.floor(100 + Math.random() * 900),
                producto: document.getElementById('orden-producto').value.trim(),
                lote: document.getElementById('orden-lote').value.trim(),
                cantidad: parseInt(document.getElementById('orden-cantidad').value),
                fase_actual: document.getElementById('orden-fase').value,
                estado: 'En Proceso',
                responsable: document.getElementById('orden-responsable').value,
                notas: document.getElementById('orden-notas').value.trim()
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/registrar_produccion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (json.success) {
                        cerrarModalOrden();
                        sincronizarDatos();
                        return;
                    }
                } catch(e) { console.error(e); }
            }

            payload.id = Date.now();
            bcState.ordenes.unshift(payload);
            guardarEnLocalStorage();
            cerrarModalOrden();
            renderizarGridOrdenes();
        }

        async function avanzarFaseOrden(id, nextFase) {
            if (bcState.modoBackend === 'mysql') {
                try {
                    await fetchConSesion('backend/registrar_produccion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'cambiar_fase', id: id, fase_actual: nextFase })
                    });
                    sincronizarDatos();
                    return;
                } catch(e) { console.error(e); }
            }

            const o = bcState.ordenes.find(x => x.id == id);
            if (o) {
                o.fase_actual = nextFase;
                if (nextFase === 'Distribución') o.estado = 'Completado';
                guardarEnLocalStorage();
                renderizarGridOrdenes();
            }
        }

        async function toggleAlertaOrden(id) {
            const o = bcState.ordenes.find(x => x.id == id);
            if (!o) return;
            const nuevoEstado = o.estado === 'Detenido / Alerta' ? 'En Proceso' : 'Detenido / Alerta';

            if (bcState.modoBackend === 'mysql') {
                try {
                    await fetchConSesion('backend/registrar_produccion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'cambiar_estado', id: id, estado: nuevoEstado })
                    });
                    sincronizarDatos();
                    return;
                } catch(e) { console.error(e); }
            }

            o.estado = nuevoEstado;
            guardarEnLocalStorage();
            renderizarGridOrdenes();
        }

        function renderizarTablaMermas() {
            const tbody = document.getElementById('tabla-mermas-body');
            if (!tbody) return;

            let totalImpacto = 0;
            let mCount = 0;
            let rCount = 0;

            bcState.mermas.forEach(m => {
                totalImpacto += parseFloat(m.costo_impacto || 0);
                if (m.tipo === 'Merma') mCount++; else rCount++;
            });

            document.getElementById('mermas-total-impacto').innerText = `$${totalImpacto.toLocaleString('es-MX', { minimumFractionDigits: 2 })} MXN`;
            document.getElementById('mermas-conteo-merma').innerText = `${mCount} eventos`;
            document.getElementById('mermas-conteo-retrabajo').innerText = `${rCount} eventos`;

            if (bcState.mermas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-6 text-center text-gray-500">Sin incidencias registradas.</td></tr>`;
                return;
            }

            let html = '';
            bcState.mermas.forEach(m => {
                const badge = m.tipo === 'Merma'
                    ? '<span class="px-2 py-0.5 rounded bg-rose-50 text-rose-600 font-semibold text-[10px]">Merma</span>'
                    : '<span class="px-2 py-0.5 rounded bg-blue-50 text-blue-300 font-semibold text-[10px]">Retrabajo</span>';

                html += `
                    <tr class="hover:bg-gray-100">
                        <td class="p-3 font-mono text-gray-500">${m.fecha}</td>
                        <td class="p-3">${badge}</td>
                        <td class="p-3 font-semibold text-gray-700">${m.area}</td>
                        <td class="p-3 text-gray-600 max-w-xs">${m.descripcion_error}</td>
                        <td class="p-3 text-amber-600 font-medium">${m.material_desperdiciado}</td>
                        <td class="p-3 text-right font-bold text-rose-400 font-mono">$${parseFloat(m.costo_impacto).toFixed(2)}</td>
                        <td class="p-3 font-mono text-gray-500">${m.lote_afectado || 'N/A'}</td>
                        <td class="p-3 text-gray-500">${m.registrado_por}</td>
                        <td class="p-3 text-right">
                            <button onclick="eliminarMerma(${m.id})" class="p-1 rounded text-rose-400 hover:bg-rose-50">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function abrirModalMerma() {
            document.getElementById('form-merma').reset();
            document.getElementById('modal-merma').classList.remove('hidden');
        }

        function cerrarModalMerma() {
            document.getElementById('modal-merma').classList.add('hidden');
        }

        async function guardarMerma(e) {
            e.preventDefault();
            const payload = {
                action: 'crear',
                fecha: new Date().toISOString().substring(0, 10),
                tipo: document.getElementById('merma-tipo').value,
                area: document.getElementById('merma-area').value,
                descripcion_error: document.getElementById('merma-descripcion').value.trim(),
                material_desperdiciado: document.getElementById('merma-material').value.trim(),
                costo_impacto: parseFloat(document.getElementById('merma-costo').value) || 0,
                lote_afectado: document.getElementById('merma-lote').value.trim() || 'N/A',
                registrado_por: document.getElementById('merma-responsable').value
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const res = await fetchConSesion('backend/control_mermas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (json.success) {
                        cerrarModalMerma();
                        sincronizarDatos();
                        return;
                    }
                } catch(err) { console.error(err); }
            }

            payload.id = Date.now();
            bcState.mermas.unshift(payload);
            guardarEnLocalStorage();
            cerrarModalMerma();
            renderizarTodo();
        }

        async function eliminarMerma(id) {
            if (bcState.modoBackend === 'mysql') {
                try {
                    await fetchConSesion('backend/control_mermas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'eliminar', id: id })
                    });
                    sincronizarDatos();
                    return;
                } catch(e) { console.error(e); }
            }

            bcState.mermas = bcState.mermas.filter(m => m.id != id);
            guardarEnLocalStorage();
            renderizarTodo();
        }

        function generarPlanAccion() {
            Swal.fire({
                icon: 'success',
                title: 'Plan de Acción ERP Generado',
                text: 'Consolidado con los indicadores de las 4 sucursales, bitácora de mermas y hoja de ruta.',
                confirmButtonColor: '#0078d4'
            });
        }

        async function instalarBaseDatos() {
            const conf = await Swal.fire({
                title: '¿Inicializar Base de Datos MySQL?',
                text: 'Se ejecutará el instalador con el esquema multi-sucursal y datos semilla.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0078d4',
                confirmButtonText: 'Sí, inicializar'
            });
            if (!conf.isConfirmed) return;

            Swal.fire({ title: 'Instalando tablas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            try {
                const res = await fetchConSesion('backend/instalar_bd.php', { method: 'POST' });
                const json = await res.json();
                if (json.success) {
                    Swal.fire('¡Éxito!', 'Base de datos de Business Central inicializada correctamente.', 'success');
                    sincronizarDatos();
                } else {
                    Swal.fire('Atención', json.message || 'Verifica que MySQL esté activo en el puerto 3306.', 'warning');
                }
            } catch(e) {
                Swal.fire('Modo Local', 'Estás en modo standalone offline. La aplicación sigue funcionando con persistencia local.', 'info');
            }
        }

        function buscarEnBusinessCentral(e) {
            const q = e.target.value.toLowerCase().trim();
            if (e.key === 'Enter') {
                if (q.includes('transf') || q.includes('traspas')) {
                    cambiarModulo('transferencias');
                } else if (q.includes('inv') || q.includes('prod') || q.includes('sku')) {
                    cambiarModulo('inventario');
                } else if (q.includes('tela') || q.includes('calc') || q.includes('rend')) {
                    cambiarModulo('rendimiento');
                } else if (q.includes('merm') || q.includes('error')) {
                    cambiarModulo('mermas');
                } else if (q.includes('sucurs') || q.includes('almacen')) {
                    cambiarModulo('sucursales');
                } else if (q.includes('diag') || q.includes('critico')) {
                    cambiarModulo('diagnostico');
                } else if (q.includes('conf') || q.includes('orden') || q.includes('fase')) {
                    cambiarModulo('produccion');
                }
            }
        }

        window.addEventListener('keydown', (e) => {
            if (e.altKey && (e.key === 'q' || e.key === 'Q')) {
                e.preventDefault();
                const inp = document.getElementById('bc-search-input');
                if (inp) {
                    inp.focus();
                    inp.select();
                }
            }
        });

        function cambiarModulo(key) {
            // Protección de rutas (SCRUM-22): el rol decide a qué módulos entra
            if (typeof moduloPermitido === 'function' && !moduloPermitido(key)) {
                mostrarAvisoAcceso('Tu rol no tiene acceso a ese módulo.', 'error');
                return;
            }

            document.querySelectorAll('.bc-seccion').forEach(s => s.classList.add('hidden'));
            document.querySelectorAll('.bc-tab-btn').forEach(b => {
                b.classList.remove('active', 'text-bc-blue', 'bg-blue-50', 'font-semibold', 'border-b-2', 'border-bc-blue');
                b.classList.add('text-gray-500');
            });

            const targetId = 'modulo-' + key;
            const targetSec = document.getElementById(targetId);
            if (targetSec) targetSec.classList.remove('hidden');

            const activeBtn = document.querySelector(`.bc-tab-btn[data-target="${targetId}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'text-bc-blue', 'bg-blue-50', 'font-semibold', 'border-b-2', 'border-bc-blue');
                activeBtn.classList.remove('text-gray-500');
            }

            if (key === 'rolecenter' || key === 'inventario') {
                setTimeout(renderizarGraficaInventario, 50);
            }
            if (key === 'catalogo') {
                renderizarCatalogo();
            }
            if (key === 'accesos') {
                cargarAccesos();
            }
        }
