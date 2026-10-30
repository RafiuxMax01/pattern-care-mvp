<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50 text-gray-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Care Business Central | Pattern Care ERP Textil</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Segoe UI style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Segoe UI', 'system-ui', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        bc: {
                            blue: '#0078d4',       // Dynamics Blue
                            darkBlue: '#005a9e',
                            navy: '#002050',
                            teal: '#008272',       // Dynamics Accent
                            gray: '#f3f2f1',
                            cardBg: '#ffffff',
                            cardBorder: '#e5e7eb',
                            lightBg: '#f9fafb'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: #f3f4f6; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        .bc-cue-card {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bc-cue-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
        }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans bg-gray-50 text-gray-800 selection:bg-bc-blue selection:text-gray-900">

    <!-- ============================================================== -->
    <!-- 1. BARRA SUPERIOR MICROSOFT Pattern Care APP BAR               -->
    <!-- ============================================================== -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 text-xs shadow-sm">
        <div class="px-4 lg:px-6 py-2.5 flex items-center justify-between gap-4">
            
            <div class="flex items-center gap-3 sm:gap-4">
                <button title="Selector de Aplicaciones Microsoft 365" class="w-8 h-8 rounded hover:bg-gray-100 text-gray-600 flex items-center justify-center transition">
                    <i class="fa-solid fa-table-cells text-base text-bc-blue"></i>
                </button>

                <div class="flex items-center gap-2">
                    <span class="font-heading font-extrabold text-sm tracking-tight text-gray-900 flex items-center gap-1.5">
                        <span class="text-bc-blue font-black">Pattern Care</span>
                        <span class="text-gray-400 font-normal">|</span>
                        <span>Business Central</span>
                    </span>
                    <span class="hidden sm:inline-block px-2 py-0.5 rounded bg-blue-50 text-bc-blue font-semibold text-[10px] border border-blue-200">
                        ERP MVP Textil
                    </span>
                </div>

                <div class="h-5 w-[1px] bg-slate-700 hidden md:block"></div>

                <!-- SWITCHER MULTI-SUCURSAL -->
                <div class="relative">
                    <button onclick="toggleDropdownSucursal()" id="btn-selector-sucursal" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-700 transition font-medium">
                        <i class="fa-solid fa-building text-teal-400 text-xs"></i>
                        <span id="txt-sucursal-actual" class="truncate max-w-[180px] sm:max-w-[240px]">Todas las Sucursales</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-500"></i>
                    </button>

                    <div id="dropdown-sucursales" class="hidden absolute left-0 mt-1.5 w-72 rounded-xl bg-white border border-gray-300 shadow-lg z-50 p-1.5 text-xs">
                        <div class="px-3 py-1.5 text-[10px] uppercase font-bold text-gray-500 border-b border-gray-200">
                            Cambiar Entorno / Sucursal Activa
                        </div>
                        <div id="lista-opciones-sucursal" class="space-y-0.5 mt-1">
                            <!-- Inyectado dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Búsqueda Global "Tell Me" -->
            <div class="hidden lg:flex items-center flex-1 max-w-md mx-4">
                <div class="relative w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                    <input type="text" id="bc-search-input" onkeyup="buscarEnBusinessCentral(event)" placeholder="Dígame qué desea hacer... (ej. Transferir, Inventario, Merma)" class="w-full bg-gray-100/90 border border-gray-300 rounded-lg pl-8 pr-12 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-bc-blue focus:ring-1 focus:ring-bc-blue">
                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] bg-slate-700 text-gray-600 px-1.5 py-0.5 rounded font-mono">Alt+Q</span>
                </div>
            </div>

            <!-- Conectividad, Notificaciones y Perfil -->
            <div class="flex items-center gap-2 sm:gap-3">
                <div id="bc-db-badge" class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-100 border border-gray-300 text-gray-600">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    <span id="bc-db-text" class="hidden sm:inline">Modo Standalone / Local</span>
                </div>

                <button onclick="cambiarModulo('inventario'); filtrarTablaPorCriticos();" title="Alertas de Stock Crítico (< 6 pzas)" class="relative p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 border border-gray-300 transition">
                    <i class="fa-solid fa-bell text-xs"></i>
                    <span id="badge-notif-count" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-600 text-white rounded-full text-[9px] font-bold flex items-center justify-center">0</span>
                </button>

                <button onclick="sincronizarDatos()" title="Sincronizar Datos (F5)" class="p-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 border border-gray-300 transition">
                    <i class="fa-solid fa-rotate text-xs" id="sync-icon"></i>
                </button>

                <div class="flex items-center gap-2 pl-2 border-l border-gray-300">
                    <div class="w-7 h-7 rounded-full bg-bc-blue text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        EG
                    </div>
                    <div class="hidden xl:block text-left">
                        <div class="font-semibold text-gray-900 leading-tight">Dueña / Encargada General</div>
                        <div class="text-[10px] text-gray-500">Rol: Dirección y Operaciones</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Ribbon de Módulos Business Central -->
        <div class="bg-gray-50 border-t border-gray-200 px-4 lg:px-6 flex items-center gap-1 overflow-x-auto py-1 font-medium no-print">
            <button onclick="cambiarModulo('rolecenter')" class="bc-tab-btn active px-3 py-1.5 rounded text-bc-blue bg-blue-50 font-semibold border-b-2 border-bc-blue flex items-center gap-1.5 transition" data-target="modulo-rolecenter">
                <i class="fa-solid fa-table-columns text-bc-blue"></i>
                <span>Centro de Funciones</span>
            </button>
            <button onclick="cambiarModulo('inventario')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-inventario">
                <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                <span>Productos & Sucursales</span>
            </button>
            <button onclick="cambiarModulo('transferencias')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-transferencias">
                <i class="fa-solid fa-truck-ramp-box text-cyan-400"></i>
                <span>Transferencias de Stock</span>
            </button>
            <button onclick="cambiarModulo('produccion')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-produccion">
                <i class="fa-solid fa-industry text-indigo-400"></i>
                <span>Producción (7 Fases)</span>
            </button>
            <button onclick="cambiarModulo('rendimiento')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-rendimiento">
                <i class="fa-solid fa-calculator text-amber-400"></i>
                <span>Cálculo de Tela (SRA/CRO)</span>
            </button>
            <button onclick="cambiarModulo('mermas')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-mermas">
                <i class="fa-solid fa-file-circle-exclamation text-rose-400"></i>
                <span>Bitácora de Mermas</span>
            </button>
            <button onclick="cambiarModulo('sucursales')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-sucursales">
                <i class="fa-solid fa-store text-teal-400"></i>
                <span>Directorio Sucursales</span>
            </button>
            <button onclick="cambiarModulo('diagnostico')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-diagnostico">
                <i class="fa-solid fa-shield-virus text-amber-500"></i>
                <span>Matriz de Puntos Críticos</span>
            </button>
            <button onclick="cambiarModulo('reportes')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-reportes">
                <i class="fa-solid fa-file-contract text-blue-400"></i>
                <span>Plan de Acción</span>
            </button>
            <button onclick="cambiarModulo('catalogo')" class="bc-tab-btn px-3 py-1.5 rounded text-gray-500 hover:text-bc-blue hover:bg-blue-50/60 flex items-center gap-1.5 transition" data-target="modulo-catalogo">
                <i class="fa-solid fa-tag text-violet-500"></i>
                <span>Catálogo de Productos</span>
            </button>
        </div>

        <!-- Action Bar -->
        <div class="bg-white border-t border-gray-200 px-4 lg:px-6 py-1.5 flex items-center justify-between text-xs no-print">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mr-1">Acciones Rápidas:</span>
                <button onclick="abrirModalSKU()" class="px-2.5 py-1 rounded bg-bc-blue hover:bg-bc-darkBlue text-white font-medium flex items-center gap-1 transition shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Nuevo Producto (SKU)</span>
                </button>
                <button onclick="abrirModalProducto()" class="px-2.5 py-1 rounded bg-violet-600 hover:bg-violet-700 text-white font-medium flex items-center gap-1 transition shadow-sm">
                    <i class="fa-solid fa-tag text-[10px]"></i>
                    <span>Nuevo Modelo</span>
                </button>
                <button onclick="abrirModalTransferencia()" class="px-2.5 py-1 rounded bg-gray-100 hover:bg-gray-200 text-teal-700 border border-gray-300 font-medium flex items-center gap-1 transition">
                    <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                    <span>Transferir Stock</span>
                </button>
                <button onclick="abrirModalOrden()" class="px-2.5 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 font-medium flex items-center gap-1 transition">
                    <i class="fa-solid fa-scissors text-[10px] text-amber-400"></i>
                    <span>Nueva Orden de Confección</span>
                </button>
                <button onclick="abrirModalMerma()" class="px-2.5 py-1 rounded bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 font-medium flex items-center gap-1 transition">
                    <i class="fa-solid fa-triangle-exclamation text-[10px] text-rose-400"></i>
                    <span>Registrar Merma</span>
                </button>
            </div>
            <div class="hidden sm:flex items-center gap-2 text-gray-500 text-[11px]">
                <span>Vista activa: <strong class="text-gray-900" id="lbl-vista-activa">Consolidada</strong></span>
            </div>
        </div>
    </header>

    <!-- ============================================================== -->
    <!-- CONTENEDOR PRINCIPAL ERP                                       -->
    <!-- ============================================================== -->
    <main class="flex-1 p-4 lg:p-6 max-w-[1600px] w-full mx-auto space-y-6">

        <!-- MÓDULO 0: ROLE CENTER -->
        <section id="modulo-rolecenter" class="bc-seccion space-y-6">
            <div class="bg-gradient-to-r from-blue-50 via-white to-blue-50 border border-gray-200 rounded-xl p-5 relative overflow-hidden shadow-sm">
                <div class="max-w-4xl relative z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-bc-blue animate-pulse"></span>
                        <span class="text-xs font-bold text-bc-blue uppercase tracking-wider">Centro de Funciones &bull; Resumen Operativo</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-heading font-bold text-gray-900 mt-1" id="headline-texto">
                        Buenos días, Encargada General. Hay 4 productos con stock crítico y 1 transferencia en tránsito.
                    </h2>
                    <p class="text-xs text-gray-600 mt-1.5 leading-relaxed">
                        Control centralizado de las <strong>4 sucursales</strong> (Planta Matriz, Hospital General, Zona Norte y Almacén Digital). Supervisión de 6 personas en planta, mitigo de mermas y cuello de botella externo en sublimado.
                    </p>
                    <div class="flex flex-wrap items-center gap-2 mt-3.5">
                        <button onclick="cambiarModulo('transferencias')" class="px-3 py-1.5 rounded-lg bg-bc-blue hover:bg-bc-darkBlue text-white text-xs font-semibold flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Gestionar Transferencias Inter-Sucursal</span>
                        </button>
                        <button onclick="cambiarModulo('rendimiento')" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-xs font-medium flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-calculator text-amber-400"></i>
                            <span>Calcular Rendimiento de Tela</span>
                        </button>
                    </div>
                </div>
                <div class="absolute right-0 bottom-0 translate-x-4 translate-y-4 text-blue-100 text-8xl pointer-events-none">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>

            <!-- CUES / MOSAICOS DE ACTIVIDADES -->
            <div>
                <div class="flex items-center justify-between mb-2.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-gauge-high text-bc-blue"></i>
                        <span>Actividades Principales (Cues)</span>
                    </h3>
                    <span class="text-[11px] text-gray-500" id="lbl-cues-contexto">Filtro: Todas las Sucursales</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    <div onclick="cambiarModulo('inventario'); filtrarTablaPorCriticos();" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-rose-500 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Stock Crítico (&lt;6)</span>
                            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span id="cue-skus-criticos" class="text-3xl font-heading font-extrabold text-rose-400">0</span>
                            <span class="text-[10px] text-gray-500">SKUs</span>
                        </div>
                        <div class="mt-1 text-[11px] text-rose-400/90 font-medium truncate">Norma 6 pzas/sucursal</div>
                    </div>

                    <div onclick="cambiarModulo('transferencias')" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-cyan-500 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Traspasos Activos</span>
                            <i class="fa-solid fa-truck-fast text-cyan-400"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span id="cue-transferencias" class="text-3xl font-heading font-extrabold text-cyan-400">0</span>
                            <span class="text-[10px] text-gray-500">órdenes</span>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500 truncate">Inter-sucursales</div>
                    </div>

                    <div onclick="cambiarModulo('produccion')" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-bc-blue rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Órdenes en Planta</span>
                            <i class="fa-solid fa-industry text-bc-blue"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span id="cue-ordenes" class="text-3xl font-heading font-extrabold text-gray-900">0</span>
                            <span class="text-[10px] text-gray-500">lotes</span>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500 truncate">7 fases de confección</div>
                    </div>

                    <div onclick="cambiarModulo('mermas')" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-amber-500 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Mermas / Retrabajo</span>
                            <i class="fa-solid fa-dollar-sign text-amber-400"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span id="cue-mermas-costo" class="text-2xl font-heading font-extrabold text-amber-400">$0</span>
                            <span class="text-[10px] text-gray-500">MXN</span>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500 truncate" id="cue-mermas-conteo">0 registros</div>
                    </div>

                    <div onclick="cambiarModulo('sucursales')" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-teal-500 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Sucursales</span>
                            <i class="fa-solid fa-store text-teal-400"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-3xl font-heading font-extrabold text-teal-400">4</span>
                            <span class="text-[10px] text-gray-500">locaciones</span>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500 truncate">1 Planta + 3 Puntos</div>
                    </div>

                    <div onclick="cambiarModulo('rolecenter')" class="bc-cue-card bg-white border border-gray-200 border-b-4 border-b-purple-500 rounded-xl p-4 cursor-pointer hover:border-gray-300">
                        <div class="flex items-center justify-between text-gray-500 text-xs">
                            <span class="font-medium">Personal Clave</span>
                            <i class="fa-solid fa-users text-purple-400"></i>
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-3xl font-heading font-extrabold text-purple-400">6</span>
                            <span class="text-[10px] text-gray-500">integrantes</span>
                        </div>
                        <div class="mt-1 text-[11px] text-gray-500 truncate">Dueña, Admin, 2 Corte, 2 Conf</div>
                    </div>
                </div>
            </div>

            <!-- Gráfico Comparativo & Alertas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-5 lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-heading font-semibold text-gray-900 text-sm">Existencias por Producto vs. Umbral Mínimo (6 pzas)</h3>
                            <p class="text-xs text-gray-500">Monitoreo consolidado e individual para prevenir desabasto en sucursales hospitalarias.</p>
                        </div>
                        <button onclick="cambiarModulo('inventario')" class="text-xs text-bc-blue hover:text-blue-600 font-semibold">
                            Ver Lista Completa &rarr;
                        </button>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chartInventario"></canvas>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-heading font-semibold text-gray-900 text-sm">Alertas del Sistema ERP</h3>
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        </div>
                        <div class="space-y-3" id="rolecenter-alertas-lista">
                            <!-- Inyectado dinámicamente -->
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-200 mt-4">
                        <button onclick="cambiarModulo('diagnostico')" class="w-full py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold text-center border border-gray-300 transition">
                            Matriz de Diagnóstico y Mitigación
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- MÓDULO 1: PRODUCTOS & INVENTARIO MULTI-SUCURSAL CON FACTBOX -->
        <section id="modulo-inventario" class="bc-seccion hidden space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                        <span>Catálogo de Productos & Inventario por Sucursal</span>
                    </h2>
                    <p class="text-xs text-gray-500">Control estricto de existencias con alerta visual para stock menor a 6 piezas por sucursal.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                        <input type="text" id="filtro-sku-busqueda" oninput="filtrarTablaInventario()" placeholder="Buscar SKU, Nombre, Color..." class="bg-gray-50 border border-gray-300 rounded-lg pl-8 pr-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-bc-blue">
                    </div>
                    <select id="filtro-sku-categoria" onchange="filtrarTablaInventario()" class="bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs text-gray-600 focus:outline-none focus:border-bc-blue">
                        <option value="">Todas las Categorías</option>
                        <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                        <option value="Chamarras Médicas">Chamarras Médicas</option>
                        <option value="Cubrepolvos">Cubrepolvos</option>
                    </select>
                    <select id="filtro-sku-estado" onchange="filtrarTablaInventario()" class="bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs text-gray-600 focus:outline-none focus:border-bc-blue">
                        <option value="">Todos los Estados</option>
                        <option value="critico">Solo Críticos (&lt; 6 pzas)</option>
                        <option value="optimo">Stock Óptimo (&gt;= 6 pzas)</option>
                    </select>
                    <button onclick="abrirModalSKU()" class="px-3 py-1.5 rounded-lg bg-bc-blue hover:bg-bc-darkBlue text-white font-semibold text-xs flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>Nuevo Producto</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-3 bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 text-gray-500 uppercase font-semibold border-b border-gray-200 text-[11px]">
                                <tr>
                                    <th class="p-3">Nº / SKU</th>
                                    <th class="p-3">Descripción de Prenda</th>
                                    <th class="p-3">Categoría</th>
                                    <th class="p-3">Color / Talla</th>
                                    <th class="p-3 text-center">Stock Sucursal</th>
                                    <th class="p-3 text-center">Stock Global</th>
                                    <th class="p-3 text-center">Mínimo</th>
                                    <th class="p-3 text-center">Estado</th>
                                    <th class="p-3 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-inventario-body" class="divide-y divide-gray-200">
                                <!-- Inyectado dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FactBox Lateral -->
                <div class="bg-white border border-gray-200 rounded-xl p-4 text-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2.5">
                        <h3 class="font-heading font-bold text-gray-900 uppercase text-[11px] tracking-wider text-bc-blue flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>FactBox &bull; Detalle de SKU</span>
                        </h3>
                        <span class="text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-500">Multi-Sucursal</span>
                    </div>

                    <div id="factbox-contenido">
                        <div class="p-6 text-center text-gray-500">
                            <i class="fa-solid fa-hand-pointer text-2xl text-gray-400 mb-2"></i>
                            <p>Selecciona un producto en la tabla para ver su distribución exacta en las 4 sucursales.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MÓDULO 2: TRANSFERENCIAS ENTRE SUCURSALES -->
        <section id="modulo-transferencias" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-truck-ramp-box text-cyan-400"></i>
                        <span>Órdenes de Transferencia Inter-Sucursal (Traspasos de Stock)</span>
                    </h2>
                    <p class="text-xs text-gray-500">Reabastece puntos de venta hospitalarios y almacenes desde la Planta Matriz de confección.</p>
                </div>
                <button onclick="abrirModalTransferencia()" class="px-3.5 py-2 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs flex items-center gap-2 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nueva Transferencia</span>
                </button>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold border-b border-gray-200 text-[11px]">
                            <tr>
                                <th class="p-3">Nº Transferencia</th>
                                <th class="p-3">Fecha</th>
                                <th class="p-3">Sucursal Origen</th>
                                <th class="p-3">Sucursal Destino</th>
                                <th class="p-3">Producto / SKU</th>
                                <th class="p-3 text-center">Cantidad</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3">Responsable</th>
                                <th class="p-3">Notas</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-transferencias-body" class="divide-y divide-gray-200">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- MÓDULO 3: PRODUCCIÓN EN 7 FASES -->
        <section id="modulo-produccion" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-industry text-indigo-400"></i>
                        <span>Control de Producción & Cadena de Suministro Textil</span>
                    </h2>
                    <p class="text-xs text-gray-500">Seguimiento de lotes por las 7 fases operativas con contraste Gorros vs Chamarras.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="seleccionarFlujoTipo('gorros')" id="btn-flujo-gorros" class="px-3 py-1.5 rounded-lg bg-bc-blue text-white text-xs font-semibold">
                        Gorros (Con Sublimado)
                    </button>
                    <button onclick="seleccionarFlujoTipo('chamarras')" id="btn-flujo-chamarras" class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 text-xs font-medium hover:text-gray-900">
                        Chamarras (Sin Sublimado)
                    </button>
                    <button onclick="abrirModalOrden()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-plus"></i>
                        <span>Nueva Orden</span>
                    </button>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5 overflow-x-auto">
                <div class="min-w-[850px] relative">
                    <div class="absolute top-1/2 left-8 right-8 h-1 bg-gray-100 -translate-y-1/2 z-0"></div>
                    <div class="grid grid-cols-7 gap-3 relative z-10 text-center">
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(1)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-bc-blue text-bc-blue flex items-center justify-center font-bold text-xs shadow-md">1</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Diseño</span>
                            <span class="text-[10px] text-gray-500">Patrones</span>
                        </div>
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(2)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-teal-500 text-teal-400 flex items-center justify-center font-bold text-xs shadow-md">2</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Abastecimiento</span>
                            <span class="text-[10px] text-gray-500">SRA / CRO</span>
                        </div>
                        <div id="nodo-fase-3" class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(3)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-amber-500 text-amber-400 flex items-center justify-center font-bold text-xs shadow-md animate-pulse">3</div>
                            <span class="mt-2 text-xs font-semibold text-amber-400 flex items-center gap-1">Impresión <i class="fa-solid fa-triangle-exclamation text-[9px]"></i></span>
                            <span class="text-[10px] text-gray-500">Taller Externo</span>
                        </div>
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(4)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-blue-500 text-blue-400 flex items-center justify-center font-bold text-xs shadow-md">4</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Corte</span>
                            <span class="text-[10px] text-gray-500">Trazo</span>
                        </div>
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(5)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-indigo-500 text-indigo-400 flex items-center justify-center font-bold text-xs shadow-md">5</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Confección</span>
                            <span class="text-[10px] text-gray-500">Ensamble</span>
                        </div>
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(6)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-emerald-500 text-emerald-400 flex items-center justify-center font-bold text-xs shadow-md">6</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Empaque</span>
                            <span class="text-[10px] text-gray-500">Calidad</span>
                        </div>
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(7)">
                            <div class="w-11 h-11 rounded-full bg-white border-2 border-purple-500 text-purple-400 flex items-center justify-center font-bold text-xs shadow-md">7</div>
                            <span class="mt-2 text-xs font-semibold text-gray-900">Distribución</span>
                            <span class="text-[10px] text-gray-500">Entrega</span>
                        </div>
                    </div>
                </div>
                <div id="panel-detalle-fase" class="mt-5 p-3.5 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                    <!-- Inyectado dinámicamente -->
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="ordenes-grid">
                <!-- Inyectado dinámicamente -->
            </div>
        </section>

        <!-- MÓDULO 4: RENDIMIENTO DE TELA SRA / CRO -->
        <section id="modulo-rendimiento" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-amber-400"></i>
                        <span>Simulador de Rendimiento de Tela & Parámetros de Lote</span>
                    </h2>
                    <p class="text-xs text-gray-500">Cálculo de aprovechamiento: SRA (6 pzas/m, 0cm merma) vs CRO (4 pzas/m + 50cm merma).</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-5 lg:col-span-2 space-y-4">
                    <h3 class="font-heading font-semibold text-gray-900 text-xs uppercase tracking-wider text-amber-400">
                        Calculadora de Corte & Trazabilidad
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Tipo de Tela *</label>
                            <select id="sim-tipo-tela" onchange="calcularRendimientoEnVivo()" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                                <option value="SRA">Tela SRA (6 pzas/m - Gorros Quirúrgicos)</option>
                                <option value="CRO">Tela CRO (4 pzas/m + 50cm merma - Chamarras/Cubrepolvos)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Metros Lineales *</label>
                            <input type="number" id="sim-metros" value="10" min="0.5" step="0.5" oninput="calcularRendimientoEnVivo()" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Color de Tela</label>
                            <input type="text" id="sim-color" placeholder="Ej. Azul Quirúrgico" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Colección / Estampado</label>
                            <input type="text" id="sim-coleccion" placeholder="Ej. Odonto Smile" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Talla</label>
                            <select id="sim-talla" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                                <option value="Unitalla">Unitalla</option>
                                <option value="S">S (Chica)</option>
                                <option value="M">M (Mediana)</option>
                                <option value="L">L (Grande)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Código de Lote</label>
                            <input type="text" id="sim-lote" placeholder="Ej. LOT-2026-X1" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Notas de Trazo</label>
                        <input type="text" id="sim-notas" placeholder="Ej. Margen de costura de 1 cm con fijación" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-xs text-gray-900">
                    </div>
                    <div class="flex justify-end pt-2">
                        <button onclick="guardarRendimiento()" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-semibold text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Registrar Consumo de Tela</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-400">Resultado Técnico Estimado</span>
                        <h4 class="text-base font-bold text-gray-900 mt-1" id="res-tipo-titulo">Tela SRA</h4>
                        <p class="text-xs text-gray-500" id="res-formula-desc">6 piezas por metro.</p>

                        <div class="mt-4 space-y-3">
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-xs text-gray-500">Piezas Obtenidas:</span>
                                <div class="text-2xl font-bold text-gray-900 mt-0.5"><span id="res-piezas">60</span> pzas</div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-xs text-gray-500">Sobrante / Merma Estimada:</span>
                                <div class="text-xl font-bold text-amber-400 mt-0.5"><span id="res-sobrante">0</span> <span id="res-sobrante-unidad">cm</span></div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-xs text-gray-500">Aprovechamiento:</span>
                                <div class="text-sm font-bold text-emerald-400 mt-0.5" id="res-aprovechamiento">100% de la tela</div>
                            </div>
                        </div>
                    </div>
                    <div class="text-[11px] text-gray-500 pt-3 border-t border-gray-200">
                        <i class="fa-solid fa-recycle text-teal-400 mr-1"></i>
                        <span>Reutilizar sobrante de tela CRO para tiras y bolsas protectoras.</span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <h3 class="font-heading font-semibold text-gray-900 text-xs mb-3">Historial de Consumo de Telas</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold border-b border-gray-200 text-[10px]">
                            <tr>
                                <th class="p-2.5">Fecha</th>
                                <th class="p-2.5">Tela</th>
                                <th class="p-2.5">Metros</th>
                                <th class="p-2.5">Piezas Est.</th>
                                <th class="p-2.5">Sobrante</th>
                                <th class="p-2.5">Lote</th>
                                <th class="p-2.5">Color / Colección</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-rendimientos-body" class="divide-y divide-gray-200">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- MÓDULO 5: BITÁCORA DE MERMAS -->
        <section id="modulo-mermas" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-circle-exclamation text-rose-400"></i>
                        <span>Bitácora de Control de Mermas & Retrabajos</span>
                    </h2>
                    <p class="text-xs text-gray-500">Visibilidad de costos ocultos: errores en estampado externo, descosidos y fallas de empaque.</p>
                </div>
                <button onclick="abrirModalMerma()" class="px-3.5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs flex items-center gap-2 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Registrar Merma / Retrabajo</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <span class="text-xs text-gray-500">Impacto Económico Acumulado</span>
                    <div class="text-2xl font-bold text-rose-400 mt-1" id="mermas-total-impacto">$0.00 MXN</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <span class="text-xs text-gray-500">Mermas Directas (Desecho)</span>
                    <div class="text-2xl font-bold text-amber-400 mt-1" id="mermas-conteo-merma">0 eventos</div>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <span class="text-xs text-gray-500">Retrabajos (Horas Absorvidas)</span>
                    <div class="text-2xl font-bold text-sky-400 mt-1" id="mermas-conteo-retrabajo">0 eventos</div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50 text-gray-500 uppercase font-semibold border-b border-gray-200 text-[11px]">
                            <tr>
                                <th class="p-3">Fecha</th>
                                <th class="p-3">Tipo</th>
                                <th class="p-3">Área de Origen</th>
                                <th class="p-3">Descripción de Incidencia</th>
                                <th class="p-3">Material Desperdiciado</th>
                                <th class="p-3 text-right">Costo / Impacto</th>
                                <th class="p-3">Lote</th>
                                <th class="p-3">Reportó</th>
                                <th class="p-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-mermas-body" class="divide-y divide-gray-200">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

<!-- MÓDULO 6: DIRECTORIO DE SUCURSALES -->
<section id="modulo-sucursales" class="bc-seccion hidden space-y-6">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
    <div>
      <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
        <i class="fa-solid fa-store text-teal-400"></i>
        <span>Administración de Sucursales</span>
      </h2>
      <p class="text-xs text-gray-500">
        Registrar, consultar, editar y dar de baja sucursales.
      </p>
    </div>

    <div class="flex gap-2">
      <button type="button" onclick="cargarSucursalesAdmin()"
        class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50">
        <i class="fa-solid fa-rotate mr-1"></i> Actualizar
      </button>
      <button type="button" onclick="nuevaSucursal()"
        class="px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700">
        <i class="fa-solid fa-plus mr-1"></i> Nueva sucursal
      </button>
    </div>
  </div>

  <div id="sucursales-mensaje" class="hidden rounded-lg p-3 text-sm" role="status"></div>

  <!-- FORMULARIO -->
  <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
    <h3 id="titulo-form-sucursal" class="font-bold text-gray-800 mb-4">
      Registrar sucursal
    </h3>

    <form id="form-sucursal-admin" onsubmit="guardarSucursalAdmin(event)"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

      <input type="hidden" id="admin-sucursal-id">

      <div>
        <label for="admin-sucursal-codigo" class="block text-sm font-medium mb-1">CÃ³digo *</label>
        <input id="admin-sucursal-codigo" maxlength="20" required
          class="w-full rounded-lg border border-gray-300 p-2 text-sm"
          placeholder="Ej. SUC-005">
      </div>

      <div>
        <label for="admin-sucursal-nombre" class="block text-sm font-medium mb-1">Nombre *</label>
        <input id="admin-sucursal-nombre" maxlength="100" required
          class="w-full rounded-lg border border-gray-300 p-2 text-sm"
          placeholder="Nombre de la sucursal">
      </div>

      <div>
        <label for="admin-sucursal-tipo" class="block text-sm font-medium mb-1">Tipo de canal *</label>
        <select id="admin-sucursal-tipo" required
          class="w-full rounded-lg border border-gray-300 p-2 text-sm">
          <option value="Planta / Taller Central">Planta / Taller Central</option>
          <option value="Punto de Venta / Showroom">Punto de Venta / Showroom</option>
          <option value="AlmacÃ©n Digital">AlmacÃ©n Digital</option>
        </select>
      </div>

      <div>
        <label for="admin-sucursal-encargado" class="block text-sm font-medium mb-1">Encargado *</label>
        <input id="admin-sucursal-encargado" maxlength="100" required
          class="w-full rounded-lg border border-gray-300 p-2 text-sm"
          placeholder="Nombre del encargado">
      </div>

      <div>
        <label for="admin-sucursal-telefono" class="block text-sm font-medium mb-1">TelÃ©fono</label>
        <input id="admin-sucursal-telefono" maxlength="50"
          class="w-full rounded-lg border border-gray-300 p-2 text-sm"
          placeholder="TelÃ©fono de contacto">
      </div>

      <div>
        <label for="admin-sucursal-direccion" class="block text-sm font-medium mb-1">DirecciÃ³n *</label>
        <input id="admin-sucursal-direccion" maxlength="200" required
          class="w-full rounded-lg border border-gray-300 p-2 text-sm"
          placeholder="DirecciÃ³n completa">
      </div>

      <div class="md:col-span-2 lg:col-span-3 flex flex-wrap gap-2 pt-2">
        <button type="submit"
          class="px-5 py-2 rounded-lg bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700">
          <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar sucursal
        </button>
        <button type="button" onclick="cancelarEdicionSucursal()"
          class="px-5 py-2 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50">
          Limpiar formulario
        </button>
      </div>
    </form>
  </div>

  <!-- TABLA DINÃMICA -->
  <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-200">
      <h3 class="font-bold text-gray-800">Directorio de sucursales</h3>
      <p class="text-xs text-gray-500 mt-1">La informaciÃ³n se consulta desde la API.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
          <tr>
            <th class="p-3">CÃ³digo / Nombre</th>
            <th class="p-3">Tipo</th>
            <th class="p-3">Encargado / Contacto</th>
            <th class="p-3">Estado</th>
            <th class="p-3">Acciones</th>
          </tr>
        </thead>
        <tbody id="tabla-sucursales-admin">
          <tr>
            <td colspan="5" class="p-5 text-center text-gray-500">
              Pulsa Â«ActualizarÂ» para consultar las sucursales.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TARJETAS EXISTENTES: SE CONSERVAN -->
  <div>
    <h3 class="font-bold text-gray-800 mb-3">Resumen de sucursales e inventario</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
      id="grid-sucursales-cards">
      <!-- Inyectado dinámicamente -->
    </div>
  </div>

</section>
<!-- FIN MÓDULO 6 -->
        <!-- MÓDULO 7: PUNTOS CRÍTICOS -->
        <section id="modulo-diagnostico" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-shield-virus text-amber-500"></i>
                        <span>Matriz de Diagnóstico Operativo & Puntos Críticos</span>
                    </h2>
                    <p class="text-xs text-gray-500">Clasificación en Calidad, Logística Multi-Sucursal e Infraestructura de Datos.</p>
                </div>
                <button onclick="cambiarModulo('reportes')" class="px-3.5 py-1.5 rounded-lg bg-bc-blue hover:bg-bc-darkBlue text-white text-xs font-semibold">
                    Generar Reporte Formal &rarr;
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-bold text-sm text-gray-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-scissors text-rose-400"></i>
                        <span>1. Problemas Operativos y de Calidad</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="font-bold text-rose-400">Falta de Línea Formal:</span> Lotes heterogéneos y tiempos no controlados.
                            <div class="text-teal-400 mt-1"><strong>Mitigación:</strong> Fichas de corte y 7 fases con asignación de personal.</div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="font-bold text-amber-400">Mermas Ocultas:</span> Desperdicio en tela CRO y horas de descosido no costeadas.
                            <div class="text-teal-400 mt-1"><strong>Mitigación:</strong> Bitácora de mermas con costo en dinero.</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-bold text-sm text-gray-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-truck-fast text-cyan-400"></i>
                        <span>2. Logística, Inventarios y Cuellos de Botella</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="font-bold text-rose-400">Desabasto en Sucursal Hospitalaria:</span> Stock < 6 piezas pone en riesgo licitaciones y ventas directas.
                            <div class="text-teal-400 mt-1"><strong>Mitigación:</strong> Órdenes de transferencia automática desde Matriz.</div>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="font-bold text-amber-400">Cuello de Botella Sublimado Externo:</span> Retraso de 48-72h en gorros quirúrgicos.
                            <div class="text-teal-400 mt-1"><strong>Mitigación:</strong> Acuerdo SLA con penalización y taller de respaldo.</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <h3 class="font-bold text-sm text-gray-900 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-purple-400"></i>
                        <span>3. Infraestructura y Seguridad de la Información</span>
                    </h3>
                    <div class="p-3.5 bg-rose-50 border border-rose-300 rounded-lg">
                        <span class="font-bold text-rose-600">Vulnerabilidad Crítica: Falta de Respaldos en la Nube</span>
                        <p class="text-gray-600 mt-1">Los registros locales en computadoras o chats de WhatsApp corren riesgo de pérdida ante fallas físicas.</p>
                        <div class="text-emerald-700 mt-2"><strong>Mitigación Inmediata:</strong> Respaldo programado de base de datos MySQL hacia Google Drive / OneDrive.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MÓDULO 8: PLAN DE ACCIÓN FORMAL -->
        <section id="modulo-reportes" class="bc-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-200 pb-4 no-print">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-contract text-blue-400"></i>
                        <span>Plan de Acción Inmediato & Reporte Ejecutivo ERP</span>
                    </h2>
                    <p class="text-xs text-gray-500">Hoja de ruta estructurada en 3 fases para la dirección y jefaturas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="generarPlanAccion()" class="px-3.5 py-1.5 rounded-lg bg-bc-blue hover:bg-bc-darkBlue text-white text-xs font-semibold">
                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Generar Plan
                    </button>
                    <button onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-xs font-medium">
                        <i class="fa-solid fa-print mr-1"></i>Imprimir / PDF
                    </button>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 sm:p-8 space-y-6 text-xs text-gray-600">
                <div class="border-b border-gray-200 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-bc-blue font-bold tracking-wider uppercase text-[10px]">Pattern Care MVP</span>
                        <h2 class="text-xl font-heading font-bold text-gray-900 mt-0.5">Pattern Care Textil &mdash; Informe Ejecutivo</h2>
                        <p class="text-gray-500">Dirección General &bull; Control Multi-Sucursal (4 Ubicaciones)</p>
                    </div>
                    <div class="text-left sm:text-right text-gray-500">
                        <div>Fecha: <strong class="text-gray-900" id="rep-fecha">13 de Octubre de 2026</strong></div>
                        <div>Personal Asignado: 6 Integrantes Clave</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="p-3.5 bg-gray-50 rounded-lg border-l-4 border-teal-500 border-y border-r border-gray-200">
                        <div class="font-bold text-teal-400 text-sm">Fase 1: Estandarización de Corte y Ficha Técnica (Semanas 1 - 2)</div>
                        <p class="mt-1">Implementar el simulador de Tela SRA (6 pzas/m) y CRO (4 pzas/m + 50cm merma) y registro estricto en bitácora de mermas.</p>
                    </div>
                    <div class="p-3.5 bg-gray-50 rounded-lg border-l-4 border-bc-blue border-y border-r border-gray-200">
                        <div class="font-bold text-bc-blue text-sm">Fase 2: Despliegue en IIS, Multi-Sucursal y Respaldos (Semanas 3 - 4)</div>
                        <p class="mt-1">Poner en marcha este Business Central ERP en el servidor local IIS de la empresa con MySQL y backups en la nube.</p>
                    </div>
                    <div class="p-3.5 bg-gray-50 rounded-lg border-l-4 border-purple-500 border-y border-r border-gray-200">
                        <div class="font-bold text-purple-400 text-sm">Fase 3: Transferencias Automáticas y Desacople de Taller Externo (Mes 2)</div>
                        <p class="mt-1">Regla de reabastecimiento inter-sucursal cuando el stock baje de 6 pzas y convenio de entrega rápida con taller de estampado.</p>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-200 grid grid-cols-2 sm:grid-cols-3 gap-6 text-center text-gray-500">
                    <div>
                        <div class="border-b border-gray-300 pb-6"></div>
                        <p class="font-semibold text-gray-700 mt-1">Dueña / Encargada General</p>
                    </div>
                    <div>
                        <div class="border-b border-gray-300 pb-6"></div>
                        <p class="font-semibold text-gray-700 mt-1">Área Administrativa</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <div class="border-b border-gray-300 pb-6"></div>
                        <p class="font-semibold text-gray-700 mt-1">Corte, Calidad & Confección (4)</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================ -->
        <!-- MÓDULO 9: CATÁLOGO DE PRODUCTOS CON VARIANTES                -->
        <!-- ============================================================ -->
        <section id="modulo-catalogo" class="bc-seccion hidden space-y-5">

            <!-- Header del módulo -->
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                <div>
                    <h2 class="text-lg font-heading font-bold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-tag text-violet-500"></i>
                        <span>Catálogo de Productos &amp; Variantes (Color / Talla)</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Gestiona modelos base y sus SKUs individuales por color y talla. Cada modelo tiene un Número de Pieza único auto-generado.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                        <input type="text" id="filtro-catalogo-busqueda" oninput="filtrarCatalogo()" placeholder="Buscar modelo, número de pieza..." class="bg-gray-50 border border-gray-300 rounded-lg pl-8 pr-3 py-1.5 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:border-bc-blue focus:ring-1 focus:ring-bc-blue">
                    </div>
                    <select id="filtro-catalogo-cat" onchange="filtrarCatalogo()" class="bg-gray-50 border border-gray-300 rounded-lg px-2.5 py-1.5 text-xs text-gray-700 focus:outline-none focus:border-bc-blue">
                        <option value="">Todas las Categorías</option>
                        <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                        <option value="Chamarras Médicas">Chamarras Médicas</option>
                        <option value="Cubrepolvos">Cubrepolvos</option>
                        <option value="Otros">Otros</option>
                    </select>
                    <button onclick="abrirModalProducto()" class="px-3 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-semibold text-xs flex items-center gap-1.5 transition shadow-sm">
                        <i class="fa-solid fa-plus"></i>
                        <span>Nuevo Modelo</span>
                    </button>
                </div>
            </div>

            <!-- Grid de tarjetas de productos -->
            <div id="catalogo-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Inyectado dinámicamente -->
            </div>

        </section>

    </main>

    <!-- FOOTER GLOBAL -->
    <footer class="bg-white border-t borader-slate-800 px-4 py-3 text-center text-xs text-gray-500 no-print">
        <div class="max-w-[1600px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px]">
            <div>
                <strong>Pattern Care Business Central ERP MVP &bull; Pattern Care</strong> &copy; 2026.
            </div>
            <div class="flex items-center gap-3">
                <span>Modo: Multi-Sucursal (4 Almacenes)</span>
                <button onclick="instalarBaseDatos()" class="text-bc-blue hover:underline font-semibold">
                    <i class="fa-solid fa-database mr-1"></i>Verificar / Inicializar BD
                </button>
            </div>
        </div>
    </footer>

    <!-- MODAL 1: SKU -->
    <div id="modal-sku" class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-xl max-w-lg w-full p-5 space-y-3 shadow-lg relative text-xs">
            <button onclick="cerrarModalSKU()" class="absolute right-4 top-4 text-gray-500 hover:text-gray-900">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="border-b border-gray-200 pb-2">
                <h3 class="font-heading font-bold text-gray-900 text-base" id="modal-sku-titulo">Ficha de Producto &bull; Nuevo SKU</h3>
                <p class="text-gray-500 text-[11px]">Catálogo maestro de Business Central.</p>
            </div>
            <form id="form-sku" onsubmit="guardarSKU(event)" class="space-y-3">
                <input type="hidden" id="sku-id" value="">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Nº / Código SKU *</label>
                        <input type="text" id="sku-codigo" required placeholder="Ej. GQ-AZC-U" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Categoría *</label>
                        <select id="sku-categoria" required class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                            <option value="Chamarras Médicas">Chamarras Médicas</option>
                            <option value="Cubrepolvos">Cubrepolvos</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Descripción / Nombre *</label>
                    <input type="text" id="sku-nombre" required placeholder="Ej. Gorro Quirúrgico Clásico Antifluido" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Color *</label>
                        <input type="text" id="sku-color" required placeholder="Azul Cielo" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Talla *</label>
                        <input type="text" id="sku-talla" required value="Unitalla" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Colección</label>
                        <input type="text" id="sku-coleccion" placeholder="Clásica" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Stock Inicial *</label>
                        <input type="number" id="sku-stock-actual" required min="0" value="10" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Stock Mínimo (Alerta &lt;6) *</label>
                        <input type="number" id="sku-stock-minimo" required min="1" value="6" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Sucursal Inicial *</label>
                        <select id="sku-sucursal-inicial" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="1">1. Matriz Planta</option>
                            <option value="2">2. Suc. Hospital</option>
                            <option value="3">3. Suc. Zona Norte</option>
                            <option value="4">4. Almacén Digital</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Notas / Observaciones</label>
                    <input type="text" id="sku-notas" placeholder="Ej. Alta rotación en hospitales" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalSKU()" class="px-3 py-1.5 rounded bg-gray-100 text-gray-600">Cancelar</button>
                    <button type="submit" class="px-4 py-1.5 rounded bg-bc-blue hover:bg-bc-darkBlue text-white font-semibold">Guardar Ficha</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: TRANSFERENCIA -->
    <div id="modal-transferencia" class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-xl max-w-lg w-full p-5 space-y-3 shadow-lg relative text-xs">
            <button onclick="cerrarModalTransferencia()" class="absolute right-4 top-4 text-gray-500 hover:text-gray-900">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="border-b border-gray-200 pb-2">
                <h3 class="font-heading font-bold text-gray-900 text-base">Orden de Transferencia de Stock</h3>
                <p class="text-gray-500 text-[11px]">Traspaso entre almacenes y puntos de venta.</p>
            </div>
            <form id="form-transferencia" onsubmit="guardarTransferencia(event)" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Sucursal Origen *</label>
                        <select id="trf-origen" required onchange="actualizarMaxTransferible()" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="1">1. Matriz Planta de Confección</option>
                            <option value="2">2. Sucursal Hospital General</option>
                            <option value="3">3. Sucursal Zona Norte</option>
                            <option value="4">4. Almacén Digital</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Sucursal Destino *</label>
                        <select id="trf-destino" required class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="2">2. Sucursal Hospital General</option>
                            <option value="1">1. Matriz Planta de Confección</option>
                            <option value="3">3. Sucursal Zona Norte</option>
                            <option value="4">4. Almacén Digital</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Producto a Transferir *</label>
                    <select id="trf-producto" required onchange="actualizarMaxTransferible()" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                        <!-- Inyectado dinámicamente -->
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Cantidad de Piezas *</label>
                        <input type="number" id="trf-cantidad" required min="1" value="5" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                        <span class="text-[10px] text-gray-500" id="trf-disponible-hint">Disponible en origen: --</span>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Estado de Entrega *</label>
                        <select id="trf-estado" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Recibido">Recibido (Inmediato)</option>
                            <option value="En Tránsito">En Tránsito (Por mensajería)</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Responsable del Envío</label>
                    <select id="trf-responsable" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                        <option value="Área Administrativa">Área Administrativa</option>
                        <option value="Dueña / Encargada General">Dueña / Encargada General</option>
                        <option value="Corte y Calidad [2]">Corte y Calidad [2]</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Motivo / Notas</label>
                    <input type="text" id="trf-notas" placeholder="Ej. Reabastecimiento urgente de punto hospitalario" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalTransferencia()" class="px-3 py-1.5 rounded bg-gray-100 text-gray-600">Cancelar</button>
                    <button type="submit" class="px-4 py-1.5 rounded bg-cyan-600 hover:bg-cyan-500 text-white font-semibold">Procesar Traspaso</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: ORDEN DE PRODUCCIÓN -->
    <div id="modal-orden" class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-xl max-w-lg w-full p-5 space-y-3 shadow-lg relative text-xs">
            <button onclick="cerrarModalOrden()" class="absolute right-4 top-4 text-gray-500 hover:text-gray-900">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="border-b border-gray-200 pb-2">
                <h3 class="font-heading font-bold text-gray-900 text-base">Crear Orden de Confección</h3>
                <p class="text-gray-500 text-[11px]">Control de lote en Planta Matriz.</p>
            </div>
            <form id="form-orden" onsubmit="guardarOrden(event)" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Prenda / Producto *</label>
                        <input type="text" id="orden-producto" required placeholder="Gorro Quirúrgico Clásico" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Código de Lote *</label>
                        <input type="text" id="orden-lote" required placeholder="LOT-2026-N1" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Cantidad de Piezas *</label>
                        <input type="number" id="orden-cantidad" required min="1" value="50" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Fase Inicial</label>
                        <select id="orden-fase" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Diseño">1. Diseño</option>
                            <option value="Abastecimiento">2. Abastecimiento</option>
                            <option value="Impresión">3. Impresión (Taller Externo)</option>
                            <option value="Corte" selected>4. Corte</option>
                            <option value="Confección">5. Confección</option>
                            <option value="Empaque">6. Empaque</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Responsable Operativo</label>
                    <select id="orden-responsable" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                        <option value="Corte y Calidad [2]">Corte y Calidad [2]</option>
                        <option value="Confección [2]">Confección [2]</option>
                        <option value="Área Administrativa">Área Administrativa</option>
                        <option value="Dueña / Encargada General">Dueña / Encargada General</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Instrucciones</label>
                    <input type="text" id="orden-notas" placeholder="Verificar muestra física de estampado antes de cortar" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalOrden()" class="px-3 py-1.5 rounded bg-gray-100 text-gray-600">Cancelar</button>
                    <button type="submit" class="px-4 py-1.5 rounded bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">Iniciar Orden</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: MERMA -->
    <div id="modal-merma" class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-xl max-w-lg w-full p-5 space-y-3 shadow-lg relative text-xs">
            <button onclick="cerrarModalMerma()" class="absolute right-4 top-4 text-gray-500 hover:text-gray-900">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            <div class="border-b border-gray-200 pb-2">
                <h3 class="font-heading font-bold text-gray-900 text-base">Registrar Merma o Retrabajo</h3>
                <p class="text-gray-500 text-[11px]">Costeo de pérdidas ocultas en el taller.</p>
            </div>
            <form id="form-merma" onsubmit="guardarMerma(event)" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Tipo *</label>
                        <select id="merma-tipo" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Merma">Merma (Desecho total)</option>
                            <option value="Retrabajo">Retrabajo (Costura corregible)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Área *</label>
                        <select id="merma-area" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Corte">Corte & Trazo</option>
                            <option value="Impresión externa">Impresión Externa</option>
                            <option value="Confección">Confección</option>
                            <option value="Empaque">Empaque</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-600 mb-1">Descripción del Error *</label>
                    <textarea id="merma-descripcion" required rows="2" placeholder="Ej. Tono fuera de tolerancia en 15 gorros" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Material Desperdiciado *</label>
                        <input type="text" id="merma-material" required placeholder="Ej. 2.5 metros tela CRO" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Costo / Impacto ($ MXN)</label>
                        <input type="number" id="merma-costo" step="0.01" min="0" value="0.00" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Lote</label>
                        <input type="text" id="merma-lote" placeholder="LOT-GQ-01" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-600 mb-1">Reportado Por</label>
                        <select id="merma-responsable" class="w-full bg-gray-50 border border-gray-300 rounded px-2.5 py-1.5 text-gray-900">
                            <option value="Corte y Calidad [2]">Corte y Calidad [2]</option>
                            <option value="Confección [2]">Confección [2]</option>
                            <option value="Área Administrativa">Área Administrativa</option>
                            <option value="Dueña / Encargada General">Dueña / Encargada General</option>
                        </select>
                    </div>
                </div>
                <div class="pt-2 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalMerma()" class="px-3 py-1.5 rounded bg-gray-100 text-gray-600">Cancelar</button>
                    <button type="submit" class="px-4 py-1.5 rounded bg-rose-600 hover:bg-rose-500 text-white font-semibold">Guardar en Bitácora</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 5: NUEVO PRODUCTO (MULTI-PASO)                         -->
    <!-- ============================================================ -->
    <div id="modal-producto" class="fixed inset-0 z-50 bg-gray-900/40 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white border border-gray-200 rounded-xl w-full max-w-2xl shadow-xl relative text-xs flex flex-col max-h-[92vh]">

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 shrink-0">
                <div>
                    <h3 class="font-heading font-bold text-gray-900 text-base" id="modal-prod-titulo">Nuevo Modelo de Prenda</h3>
                    <p class="text-gray-500 text-[11px]">Catálogo maestro · Pattern Care Business Central</p>
                </div>
                <button onclick="cerrarModalProducto()" class="text-gray-400 hover:text-gray-700 p-1 rounded-lg hover:bg-gray-100 transition">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Indicador de pasos -->
            <div class="px-5 pt-4 pb-0 shrink-0">
                <div class="flex items-center gap-0">
                    <div class="flex-1 flex items-center">
                        <div id="step-indicator-1" class="w-7 h-7 rounded-full bg-bc-blue text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-1" id="step-line-1"></div>
                    </div>
                    <div class="flex-1 flex items-center">
                        <div id="step-indicator-2" class="w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-xs shrink-0">2</div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-1" id="step-line-2"></div>
                    </div>
                    <div id="step-indicator-3" class="w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-xs shrink-0">3</div>
                </div>
                <div class="flex justify-between text-[10px] text-gray-500 mt-1 px-0">
                    <span id="step-label-1" class="font-semibold text-bc-blue">Información General</span>
                    <span id="step-label-2">Variantes (Color/Talla)</span>
                    <span id="step-label-3">Foto de Estampado</span>
                </div>
            </div>

            <!-- Cuerpo del modal — scrollable -->
            <div class="overflow-y-auto flex-1 px-5 py-4">

                <!-- PASO 1: Información General -->
                <div id="paso-1" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block font-semibold text-gray-700 mb-1">Nombre del Modelo *
                                <span id="nombre-dup-icon" class="hidden ml-1"></span>
                            </label>
                            <input type="text" id="prod-nombre" required
                                oninput="verificarNombreProducto()"
                                placeholder="Ej. Gorro Quirúrgico Antifluido Pastel"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue focus:ring-1 focus:ring-bc-blue">
                            <div id="nombre-dup-msg" class="hidden text-rose-600 text-[11px] mt-0.5 flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="nombre-dup-text">Ya existe un producto con este nombre.</span>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Categoría *</label>
                            <select id="prod-categoria" required onchange="solicitarNumeroPieza()" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                                <option value="">-- Seleccionar --</option>
                                <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                                <option value="Chamarras Médicas">Chamarras Médicas</option>
                                <option value="Cubrepolvos">Cubrepolvos</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">
                                Número de Pieza
                                <span class="text-gray-400 font-normal">(auto-generado)</span>
                            </label>
                            <div class="flex gap-1">
                                <input type="text" id="prod-numero-pieza"
                                    placeholder="GQ-NUE-0001"
                                    pattern="[A-Z]{2,4}-[A-Z0-9]{2,6}-[0-9]{4}"
                                    class="flex-1 bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 font-mono focus:outline-none focus:border-bc-blue">
                                <button type="button" onclick="solicitarNumeroPieza()" title="Generar nuevo número"
                                    class="px-2.5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 border border-gray-300 transition">
                                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-0.5">Formato: XX-XXX-NNNN · Editable o auto</p>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Colección / Línea</label>
                            <input type="text" id="prod-coleccion" placeholder="Ej. Clásica Hospitalaria"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Tipo de Tela</label>
                            <select id="prod-tipo-tela" class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                                <option value="SRA">SRA (Gorros · 6 pzas/m)</option>
                                <option value="CRO">CRO (Chamarras/Cubrepolvos · 4 pzas/m)</option>
                                <option value="Mixta">Mixta</option>
                                <option value="Otra">Otra</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Precio Base (MXN) *</label>
                            <input type="number" id="prod-precio" min="0" step="0.01" value="0.00"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Stock Mínimo (Alerta &lt;N)</label>
                            <input type="number" id="prod-minimo" min="1" value="6"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                        </div>
                        <div class="col-span-2">
                            <label class="block font-semibold text-gray-700 mb-1">Descripción / Especificaciones</label>
                            <textarea id="prod-descripcion" rows="2" placeholder="Materiales, acabados, normas..."
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue resize-none"></textarea>
                        </div>
                        <div class="col-span-2">
                            <label class="block font-semibold text-gray-700 mb-1">Notas internas</label>
                            <input type="text" id="prod-notas" placeholder="Ej. Alta rotación en hospitales"
                                class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 focus:outline-none focus:border-bc-blue">
                        </div>
                    </div>
                </div>

                <!-- PASO 2: Variantes (Color / Talla) -->
                <div id="paso-2" class="hidden space-y-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-xs text-blue-700">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Agrega las combinaciones de <strong>Color</strong> y <strong>Talla</strong> disponibles.
                        Cada combinación generará un SKU único automáticamente.
                    </div>

                    <!-- Formulario de nueva variante -->
                    <div class="grid grid-cols-3 gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Color *</label>
                            <input type="text" id="var-color" placeholder="Azul Cielo"
                                class="w-full bg-white border border-gray-300 rounded px-2.5 py-1.5 text-gray-900 focus:outline-none focus:border-bc-blue">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-600 mb-1">Talla *</label>
                            <select id="var-talla" class="w-full bg-white border border-gray-300 rounded px-2.5 py-1.5 text-gray-900 focus:outline-none focus:border-bc-blue">
                                <option value="Unitalla">Unitalla</option>
                                <option value="XS">XS</option>
                                <option value="S">S</option>
                                <option value="M">M</option>
                                <option value="L">L</option>
                                <option value="XL">XL</option>
                                <option value="XXL">XXL</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="agregarVarianteLista()" class="w-full py-1.5 rounded bg-bc-blue hover:bg-bc-darkBlue text-white font-semibold flex items-center justify-center gap-1 transition">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Agregar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Colores rápidos -->
                    <div>
                        <p class="text-[10px] font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Colores frecuentes</p>
                        <div class="flex flex-wrap gap-1.5">
                            <button type="button" onclick="setColorRapido('Azul Cielo')" class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[11px] font-medium hover:bg-blue-200 border border-blue-200">Azul Cielo</button>
                            <button type="button" onclick="setColorRapido('Azul Marino')" class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[11px] font-medium hover:bg-indigo-200 border border-indigo-200">Azul Marino</button>
                            <button type="button" onclick="setColorRapido('Verde Quirófano')" class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-medium hover:bg-emerald-200 border border-emerald-200">Verde Quirófano</button>
                            <button type="button" onclick="setColorRapido('Lila')" class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-medium hover:bg-violet-200 border border-violet-200">Lila</button>
                            <button type="button" onclick="setColorRapido('Rosa Palo')" class="px-2 py-0.5 rounded-full bg-pink-100 text-pink-700 text-[11px] font-medium hover:bg-pink-200 border border-pink-200">Rosa Palo</button>
                            <button type="button" onclick="setColorRapido('Blanco Óptico')" class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[11px] font-medium hover:bg-gray-200 border border-gray-200">Blanco Óptico</button>
                            <button type="button" onclick="setColorRapido('Negro Oxford')" class="px-2 py-0.5 rounded-full bg-gray-800 text-white text-[11px] font-medium hover:bg-gray-900">Negro Oxford</button>
                            <button type="button" onclick="setColorRapido('Multicolor')" class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[11px] font-medium hover:bg-orange-200 border border-orange-200">Multicolor</button>
                        </div>
                    </div>

                    <!-- Lista de variantes agregadas -->
                    <div>
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Variantes en este modelo</p>
                        <div id="variantes-lista" class="space-y-1.5 min-h-[60px]">
                            <div id="variantes-vacio" class="text-center text-gray-400 py-4 text-xs border border-dashed border-gray-300 rounded-lg">
                                <i class="fa-solid fa-layer-group mb-1 text-gray-300 text-lg block"></i>
                                Sin variantes aún. Agrega al menos una combinación Color/Talla.
                            </div>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[11px] text-gray-500"><span id="variantes-count">0</span> variante(s) · cada una generará un SKU único</span>
                        </div>
                    </div>
                </div>

                <!-- PASO 3: Foto del Estampado -->
                <div id="paso-3" class="hidden space-y-4">
                    <div class="bg-violet-50 border border-violet-200 rounded-lg p-3 text-xs text-violet-700">
                        <i class="fa-solid fa-image mr-1"></i>
                        Sube una foto del estampado, patronaje o muestra física del modelo.
                        Se guardará en la nube (Cloudinary) o en el servidor local.
                    </div>

                    <!-- Área de drag & drop -->
                    <div id="foto-dropzone"
                        ondragover="event.preventDefault(); this.classList.add('border-bc-blue','bg-blue-50')"
                        ondragleave="this.classList.remove('border-bc-blue','bg-blue-50')"
                        ondrop="handleFotoDrop(event)"
                        onclick="document.getElementById('foto-input').click()"
                        class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-bc-blue hover:bg-blue-50/40 transition">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-300 mb-2"></i>
                        <p class="font-semibold text-gray-600 text-sm">Arrastra la imagen aquí o haz clic para seleccionar</p>
                        <p class="text-gray-400 text-[11px] mt-1">JPG, PNG, WEBP · máx. 5 MB</p>
                        <input type="file" id="foto-input" accept="image/*" class="hidden" onchange="handleFotoChange(event)">
                    </div>

                    <!-- Preview de imagen -->
                    <div id="foto-preview-wrap" class="hidden">
                        <div class="relative inline-block">
                            <img id="foto-preview-img" src="" alt="Preview" class="h-48 w-auto rounded-xl border border-gray-200 object-contain shadow-sm">
                            <button type="button" onclick="limpiarFoto()"
                                class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-rose-500 text-white flex items-center justify-center text-xs shadow">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1" id="foto-nombre-txt"></p>
                    </div>

                    <!-- Tipo de imagen -->
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Tipo de imagen</label>
                        <select id="foto-tipo" class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 text-xs focus:outline-none focus:border-bc-blue">
                            <option value="Estampado">Estampado / Diseño</option>
                            <option value="Principal">Foto Principal del Modelo</option>
                            <option value="Detalle">Detalle de Acabado</option>
                            <option value="Empaque">Empaque</option>
                        </select>
                    </div>

                    <!-- URL manual (alternativo) -->
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">O pega una URL directa de imagen</label>
                        <input type="url" id="foto-url-manual" placeholder="https://..." oninput="previewUrlManual()"
                            class="w-full bg-gray-50 border border-gray-300 rounded-lg px-3 py-2 text-gray-900 text-xs focus:outline-none focus:border-bc-blue">
                    </div>
                </div>

            </div><!-- /body scrollable -->

            <!-- Footer del modal (botones de navegación) -->
            <div class="px-5 py-3 border-t border-gray-200 flex items-center justify-between shrink-0 bg-gray-50 rounded-b-xl">
                <div>
                    <button type="button" id="btn-paso-atras" onclick="cambiarPasoProducto(-1)" class="hidden px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs transition">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Atrás
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="cerrarModalProducto()" class="px-4 py-2 rounded-lg bg-white hover:bg-gray-100 text-gray-600 font-semibold text-xs border border-gray-300 transition">
                        Cancelar
                    </button>
                    <button type="button" id="btn-paso-siguiente" onclick="cambiarPasoProducto(1)" class="px-5 py-2 rounded-lg bg-bc-blue hover:bg-bc-darkBlue text-white font-semibold text-xs transition shadow-sm">
                        Siguiente <i class="fa-solid fa-arrow-right ml-1"></i>
                    </button>
                    <button type="button" id="btn-guardar-producto" onclick="guardarProducto()" class="hidden px-5 py-2 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-semibold text-xs transition shadow-sm">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Modelo
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- MOTOR JAVASCRIPT BUSINESS CENTRAL & MULTI-SUCURSAL -->
    <script>
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

        document.addEventListener('DOMContentLoaded', () => {
            renderizarDropdownSucursales();
            cargarSucursalesAdmin();
            verDetalleFase(1);
            calcularRendimientoEnVivo();
            sincronizarDatos();
        });

        async function sincronizarDatos() {
            const syncIcon = document.getElementById('sync-icon');
            if (syncIcon) syncIcon.classList.add('fa-spin');
            const badgeDiv = document.getElementById('bc-db-badge');

            const urlParams = bcState.sucursalActivaId ? `?sucursal_id=${bcState.sucursalActivaId}` : '';

            try {
                const res = await fetch(`backend/obtener_datos.php${urlParams}`, {
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

            let html = `
                <button onclick="seleccionarSucursalGlobal(null)" class="w-full text-left px-2.5 py-1.5 rounded hover:bg-gray-100 transition flex items-center justify-between ${bcState.sucursalActivaId === null ? 'bg-blue-50 text-bc-blue font-bold' : 'text-gray-600'}">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-globe text-xs"></i>
                        <span>Todas las Sucursales (Consolidado)</span>
                    </span>
                    ${bcState.sucursalActivaId === null ? '<i class="fa-solid fa-check text-xs"></i>' : ''}
                </button>
            `;

            bcState.sucursales.forEach(s => {
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
                    const res = await fetch('backend/guardar_inventario.php', {
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
                    const res = await fetch('backend/transferir_stock.php', {
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
                    const res = await fetch('backend/guardar_inventario.php', {
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
                    const res = await fetch('backend/guardar_inventario.php', {
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
                    const res = await fetch('backend/calcular_rendimiento.php', {
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
                    const res = await fetch('backend/registrar_produccion.php', {
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
                    await fetch('backend/registrar_produccion.php', {
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
                    await fetch('backend/registrar_produccion.php', {
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
                    const res = await fetch('backend/control_mermas.php', {
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
                    await fetch('backend/control_mermas.php', {
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
                const res = await fetch('backend/instalar_bd.php', { method: 'POST' });
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
        }
    </script>

    <!-- ============================================================ -->
    <!-- MÓDULO CATÁLOGO: JAVASCRIPT COMPLETO                         -->
    <!-- ============================================================ -->
    <script>
        // ── Estado local del catálogo de productos ────────────────
        const catalogoState = {
            productos: [],        // lista de productos con variantes
            pasoActual: 1,        // 1 = Info, 2 = Variantes, 3 = Foto
            variantes: [],        // variantes del formulario en curso
            fotoBase64: null,     // imagen seleccionada en base64
            fotoNombre: null,
            fotoUrlManual: null,
            editandoId: null      // null = nuevo, número = editar
        };

        // Datos semilla para modo standalone (sin backend)
        const CATALOGO_SEMILLA = [
            { id: 1, numero_pieza: 'GQ-CLA-0001', nombre: 'Gorro Quirúrgico Clásico Antifluido', categoria: 'Gorros Quirúrgicos', coleccion: 'Clásica Hospitalaria', tipo_tela: 'SRA', precio_base: 120, stock_minimo: 6, foto_url: null, notas: 'Alta demanda hospitalaria', variantes: [
                { id: 1, sku: 'GQ-CLA-0001-AZC-U', color: 'Azul Cielo',     talla: 'Unitalla', activo: 1 },
                { id: 2, sku: 'GQ-CLA-0001-VER-U', color: 'Verde Quirófano', talla: 'Unitalla', activo: 1 },
                { id: 3, sku: 'GQ-CLA-0001-LIL-U', color: 'Lila',           talla: 'Unitalla', activo: 1 }
            ]},
            { id: 2, numero_pieza: 'GQ-EST-0002', nombre: 'Gorro Quirúrgico Estampado Odonto Smile', categoria: 'Gorros Quirúrgicos', coleccion: 'Odontología Smile', tipo_tela: 'SRA', precio_base: 150, stock_minimo: 6, foto_url: null, notas: 'Taller externo sublimado', variantes: [
                { id: 4, sku: 'GQ-EST-0002-MUL-U', color: 'Multicolor', talla: 'Unitalla', activo: 1 }
            ]},
            { id: 3, numero_pieza: 'CH-PRE-0001', nombre: 'Chamarra Médica Antifluido Stretch Premier', categoria: 'Chamarras Médicas', coleccion: 'Línea Premier', tipo_tela: 'CRO', precio_base: 680, stock_minimo: 6, foto_url: null, notas: 'Desabasto de cierres', variantes: [
                { id: 5, sku: 'CH-PRE-0001-BLA-S', color: 'Blanco Óptico', talla: 'S', activo: 1 },
                { id: 6, sku: 'CH-PRE-0001-BLA-M', color: 'Blanco Óptico', talla: 'M', activo: 1 },
                { id: 7, sku: 'CH-PRE-0001-BLA-L', color: 'Blanco Óptico', talla: 'L', activo: 1 },
                { id: 8, sku: 'CH-PRE-0001-AZM-M', color: 'Azul Marino',   talla: 'M', activo: 1 },
                { id: 9, sku: 'CH-PRE-0001-VER-S', color: 'Verde Quirófano', talla: 'S', activo: 1 }
            ]},
            { id: 4, numero_pieza: 'CP-IMP-0001', nombre: 'Cubrepolvo Impermeable con Ajuste', categoria: 'Cubrepolvos', coleccion: 'Protección Total', tipo_tela: 'CRO', precio_base: 320, stock_minimo: 6, foto_url: null, notas: 'Alta demanda institucional', variantes: [
                { id: 10, sku: 'CP-IMP-0001-NEG-U', color: 'Negro Oxford', talla: 'Unitalla', activo: 1 },
                { id: 11, sku: 'CP-IMP-0001-AZM-U', color: 'Azul Marino',  talla: 'Unitalla', activo: 1 }
            ]}
        ];

        // ─────────────────────────────────────────────────────────
        // RENDERIZADO CATÁLOGO
        // ─────────────────────────────────────────────────────────
        function renderizarCatalogo() {
            // Intentar cargar desde backend; si falla, usar estado local o semilla
            if (bcState.modoBackend === 'mysql') {
                fetch('backend/gestionar_productos.php?action=listar')
                    .then(r => r.json())
                    .then(j => {
                        if (j.success) {
                            catalogoState.productos = j.data || [];
                        } else {
                            _cargarCatalogoLocal();
                        }
                        _pintarCatalogo();
                    })
                    .catch(() => { _cargarCatalogoLocal(); _pintarCatalogo(); });
            } else {
                _cargarCatalogoLocal();
                _pintarCatalogo();
            }
        }

        function _cargarCatalogoLocal() {
            const raw = localStorage.getItem('pattern_care_catalogo');
            if (raw) {
                try { catalogoState.productos = JSON.parse(raw); return; } catch(e) {}
            }
            catalogoState.productos = JSON.parse(JSON.stringify(CATALOGO_SEMILLA));
        }

        function _guardarCatalogoLocal() {
            localStorage.setItem('pattern_care_catalogo', JSON.stringify(catalogoState.productos));
        }

        function _pintarCatalogo() {
            const grid = document.getElementById('catalogo-grid');
            if (!grid) return;

            const busq = (document.getElementById('filtro-catalogo-busqueda')?.value || '').toLowerCase();
            const cat  = document.getElementById('filtro-catalogo-cat')?.value || '';

            const filtrados = catalogoState.productos.filter(p => {
                const matchBusq = !busq || p.nombre.toLowerCase().includes(busq) || (p.numero_pieza || '').toLowerCase().includes(busq) || (p.coleccion || '').toLowerCase().includes(busq);
                const matchCat  = !cat  || p.categoria === cat;
                return matchBusq && matchCat;
            });

            if (filtrados.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                        <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300"></i>
                        <p class="font-semibold text-sm">Sin productos en el catálogo</p>
                        <p class="text-xs mt-1">Agrega un modelo con el botón "Nuevo Modelo"</p>
                        <button onclick="abrirModalProducto()" class="mt-4 px-4 py-2 rounded-lg bg-violet-600 text-white text-xs font-semibold shadow">
                            <i class="fa-solid fa-plus mr-1"></i> Nuevo Modelo
                        </button>
                    </div>`;
                return;
            }

            const catColor = { 'Gorros Quirúrgicos': 'bg-blue-50 text-blue-700 border-blue-200', 'Chamarras Médicas': 'bg-indigo-50 text-indigo-700 border-indigo-200', 'Cubrepolvos': 'bg-teal-50 text-teal-700 border-teal-200', 'Otros': 'bg-gray-100 text-gray-600 border-gray-200' };

            grid.innerHTML = filtrados.map(p => {
                const variActivas = (p.variantes || []).filter(v => v.activo !== 0);
                const colores = [...new Set(variActivas.map(v => v.color))];
                const tallas  = [...new Set(variActivas.map(v => v.talla))];
                const badgeCat = catColor[p.categoria] || catColor['Otros'];
                const imgSrc = p.foto_url 
                    ? `<img src="${p.foto_url}" alt="${p.nombre}" class="w-full h-32 object-cover rounded-t-xl">`
                    : `<div class="w-full h-32 rounded-t-xl bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center text-gray-300 border-b border-gray-200">
                            <i class="fa-regular fa-image text-3xl mb-1"></i>
                            <span class="text-[10px]">Sin foto</span>
                       </div>`;

                return `
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-violet-300 transition group">
                        ${imgSrc}
                        <div class="p-3.5 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-mono text-[11px] font-bold text-bc-blue">${p.numero_pieza || '—'}</span>
                                    <h4 class="font-bold text-gray-900 text-sm leading-tight mt-0.5 truncate" title="${p.nombre}">${p.nombre}</h4>
                                </div>
                                <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold border ${badgeCat}">${p.categoria.split(' ')[0]}</span>
                            </div>

                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-violet-400"></i>
                                <span>${variActivas.length} variante(s)</span>
                                <span class="text-gray-300">·</span>
                                <i class="fa-solid fa-tshirt text-gray-400"></i>
                                <span>${p.tipo_tela}</span>
                            </div>

                            ${colores.length ? `
                            <div class="flex flex-wrap gap-1">
                                ${colores.slice(0,4).map(c => `<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px] border border-gray-200">${c}</span>`).join('')}
                                ${colores.length > 4 ? `<span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px]">+${colores.length-4}</span>` : ''}
                            </div>` : ''}

                            ${tallas.length ? `
                            <div class="flex flex-wrap gap-1">
                                ${tallas.map(t => `<span class="px-1.5 py-0.5 rounded bg-blue-50 text-blue-600 text-[10px] font-semibold border border-blue-100">${t}</span>`).join('')}
                            </div>` : ''}

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="font-bold text-gray-900 text-sm">$${Number(p.precio_base).toLocaleString('es-MX', {minimumFractionDigits:2})}</span>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                    <button onclick="editarProducto(${p.id})" title="Editar" class="p-1.5 rounded bg-gray-100 hover:bg-blue-100 text-gray-500 hover:text-bc-blue transition">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>
                                    <button onclick="abrirDetProducto(${p.id})" title="Ver variantes" class="p-1.5 rounded bg-gray-100 hover:bg-violet-100 text-gray-500 hover:text-violet-600 transition">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                    <button onclick="eliminarProductoCatalogo(${p.id})" title="Eliminar" class="p-1.5 rounded bg-gray-100 hover:bg-rose-100 text-gray-500 hover:text-rose-600 transition">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }).join('');
        }

        function filtrarCatalogo() {
            _pintarCatalogo();
        }

        // ─────────────────────────────────────────────────────────
        // MODAL MULTI-PASO
        // ─────────────────────────────────────────────────────────
        function abrirModalProducto(id = null) {
            catalogoState.pasoActual = 1;
            catalogoState.variantes  = [];
            catalogoState.fotoBase64 = null;
            catalogoState.fotoNombre = null;
            catalogoState.fotoUrlManual = null;
            catalogoState.editandoId = id;

            // Reset form
            ['prod-nombre','prod-coleccion','prod-descripcion','prod-notas','prod-numero-pieza','foto-url-manual'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
            const cat = document.getElementById('prod-categoria');
            if (cat) cat.value = '';
            document.getElementById('prod-precio').value = '0.00';
            document.getElementById('prod-minimo').value = '6';
            document.getElementById('prod-tipo-tela').value = 'SRA';
            limpiarFoto();
            _actualizarListaVariantes();

            if (id) {
                const prod = catalogoState.productos.find(p => p.id == id);
                if (prod) {
                    document.getElementById('modal-prod-titulo').innerText = 'Editar Modelo: ' + prod.numero_pieza;
                    document.getElementById('prod-nombre').value       = prod.nombre;
                    document.getElementById('prod-categoria').value    = prod.categoria;
                    document.getElementById('prod-numero-pieza').value = prod.numero_pieza || '';
                    document.getElementById('prod-coleccion').value    = prod.coleccion || '';
                    document.getElementById('prod-tipo-tela').value    = prod.tipo_tela || 'SRA';
                    document.getElementById('prod-precio').value       = prod.precio_base || 0;
                    document.getElementById('prod-minimo').value       = prod.stock_minimo || 6;
                    document.getElementById('prod-descripcion').value  = prod.descripcion || '';
                    document.getElementById('prod-notas').value        = prod.notas || '';
                    catalogoState.variantes = (prod.variantes || []).filter(v => v.activo !== 0).map(v => ({...v}));
                    _actualizarListaVariantes();

                    if (prod.foto_url) {
                        document.getElementById('foto-url-manual').value = prod.foto_url;
                        previewUrlManual();
                    }
                }
            } else {
                document.getElementById('modal-prod-titulo').innerText = 'Nuevo Modelo de Prenda';
            }

            _irAPaso(1);
            document.getElementById('modal-producto').classList.remove('hidden');
        }

        function cerrarModalProducto() {
            document.getElementById('modal-producto').classList.add('hidden');
        }

        function _irAPaso(num) {
            catalogoState.pasoActual = num;
            [1,2,3].forEach(n => {
                document.getElementById('paso-' + n)?.classList.toggle('hidden', n !== num);
                const ind = document.getElementById('step-indicator-' + n);
                const lbl = document.getElementById('step-label-' + n);
                if (ind) {
                    if (n < num) {
                        ind.className = 'w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shrink-0';
                        ind.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
                    } else if (n === num) {
                        ind.className = 'w-7 h-7 rounded-full bg-bc-blue text-white flex items-center justify-center font-bold text-xs shrink-0';
                        ind.textContent = n;
                    } else {
                        ind.className = 'w-7 h-7 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center font-bold text-xs shrink-0';
                        ind.textContent = n;
                    }
                }
                if (lbl) {
                    lbl.className = n === num ? 'font-semibold text-bc-blue' : (n < num ? 'text-emerald-600 font-medium' : 'text-gray-400');
                }
            });

            const btnAtras    = document.getElementById('btn-paso-atras');
            const btnSig      = document.getElementById('btn-paso-siguiente');
            const btnGuardar  = document.getElementById('btn-guardar-producto');
            if (btnAtras)   btnAtras.classList.toggle('hidden', num === 1);
            if (btnSig)     btnSig.classList.toggle('hidden', num === 3);
            if (btnGuardar) btnGuardar.classList.toggle('hidden', num !== 3);
        }

        function cambiarPasoProducto(delta) {
            const actual = catalogoState.pasoActual;
            const nuevo  = actual + delta;

            // Validación paso 1
            if (actual === 1 && delta > 0) {
                const nombre = document.getElementById('prod-nombre').value.trim();
                const cat    = document.getElementById('prod-categoria').value;
                if (!nombre) { Swal.fire('Atención', 'El nombre del modelo es obligatorio.', 'warning'); return; }
                if (!cat)    { Swal.fire('Atención', 'Selecciona una categoría.', 'warning'); return; }
                // Si no tiene número de pieza, generar uno
                const np = document.getElementById('prod-numero-pieza').value.trim();
                if (!np) solicitarNumeroPieza();
            }

            // Validación paso 2
            if (actual === 2 && delta > 0) {
                if (catalogoState.variantes.length === 0) {
                    const ok = confirm('No has agregado variantes. ¿Continuar sin variantes?');
                    if (!ok) return;
                }
            }

            if (nuevo >= 1 && nuevo <= 3) _irAPaso(nuevo);
        }

        // ─────────────────────────────────────────────────────────
        // NÚMERO DE PIEZA AUTO
        // ─────────────────────────────────────────────────────────
        async function solicitarNumeroPieza() {
            const cat = document.getElementById('prod-categoria').value;
            if (!cat) return;

            const input = document.getElementById('prod-numero-pieza');
            if (input.value.trim()) return; // ya tiene valor

            if (bcState.modoBackend === 'mysql') {
                try {
                    const r = await fetch('backend/gestionar_productos.php?action=generar_numero&categoria=' + encodeURIComponent(cat));
                    const j = await r.json();
                    if (j.success) { input.value = j.data.numero_pieza; return; }
                } catch(e) {}
            }
            // Offline: generar local
            const mapa = { 'Gorros Quirúrgicos':'GQ','Chamarras Médicas':'CH','Cubrepolvos':'CP','Otros':'OT' };
            const cod  = mapa[cat] || 'OT';
            const num  = (catalogoState.productos.filter(p => (p.numero_pieza||'').startsWith(cod)).length + 1);
            input.value = `${cod}-NUE-${String(num).padStart(4,'0')}`;
        }

        // ─────────────────────────────────────────────────────────
        // VERIFICAR NOMBRE DUPLICADO
        // ─────────────────────────────────────────────────────────
        let _verNombreTimer = null;
        function verificarNombreProducto() {
            clearTimeout(_verNombreTimer);
            _verNombreTimer = setTimeout(async () => {
                const nombre = document.getElementById('prod-nombre').value.trim();
                const icon   = document.getElementById('nombre-dup-icon');
                const msg    = document.getElementById('nombre-dup-msg');
                if (!nombre) { msg.classList.add('hidden'); return; }

                // Verificar en estado local primero
                const existe = catalogoState.productos.some(p =>
                    p.nombre.toLowerCase().trim() === nombre.toLowerCase().trim() &&
                    p.id != catalogoState.editandoId
                );

                if (existe) {
                    msg.classList.remove('hidden');
                    return;
                }

                // Si hay backend, verificar también
                if (bcState.modoBackend === 'mysql') {
                    try {
                        const url = `backend/gestionar_productos.php?action=verificar_nombre&nombre=${encodeURIComponent(nombre)}&excluir_id=${catalogoState.editandoId || 0}`;
                        const r = await fetch(url);
                        const j = await r.json();
                        msg.classList.toggle('hidden', j.data?.disponible !== false);
                    } catch(e) { msg.classList.add('hidden'); }
                } else {
                    msg.classList.add('hidden');
                }
            }, 400);
        }

        // ─────────────────────────────────────────────────────────
        // VARIANTES
        // ─────────────────────────────────────────────────────────
        function agregarVarianteLista() {
            const color = document.getElementById('var-color').value.trim();
            const talla = document.getElementById('var-talla').value;
            if (!color) { Swal.fire('Atención','Escribe el nombre del color.','info'); return; }

            // Verificar duplicado
            const dup = catalogoState.variantes.find(v => v.color.toLowerCase() === color.toLowerCase() && v.talla === talla);
            if (dup) { Swal.fire('Duplicado',`Ya existe la variante ${color} / ${talla}.`,'warning'); return; }

            const np = document.getElementById('prod-numero-pieza').value.trim() || 'XX-NUE-0000';
            const colCod = color.replace(/[^a-zA-Z]/g,'').toUpperCase().slice(0,3);
            const talCod = talla.replace(/[^a-zA-Z0-9]/g,'').toUpperCase();
            const sku = `${np}-${colCod}-${talCod}`;

            catalogoState.variantes.push({ id: Date.now(), sku, color, talla, activo: 1, _nuevo: true });
            document.getElementById('var-color').value = '';
            _actualizarListaVariantes();
        }

        function setColorRapido(color) {
            document.getElementById('var-color').value = color;
            document.getElementById('var-color').focus();
        }

        function eliminarVarianteLista(idx) {
            catalogoState.variantes.splice(idx, 1);
            _actualizarListaVariantes();
        }

        function _actualizarListaVariantes() {
            const lista  = document.getElementById('variantes-lista');
            const vacio  = document.getElementById('variantes-vacio');
            const count  = document.getElementById('variantes-count');
            const activas = catalogoState.variantes.filter(v => v.activo !== 0);

            if (count) count.textContent = activas.length;

            if (activas.length === 0) {
                lista.innerHTML = '';
                lista.appendChild(document.getElementById('variantes-vacio'));
                vacio?.classList.remove('hidden');
                return;
            }
            vacio?.classList.add('hidden');

            lista.innerHTML = activas.map((v, i) => {
                const colores = { 'Azul': 'bg-blue-100 text-blue-700', 'Verde': 'bg-emerald-100 text-emerald-700', 'Lila': 'bg-violet-100 text-violet-700', 'Rosa': 'bg-pink-100 text-pink-700', 'Blanco': 'bg-gray-100 text-gray-700', 'Negro': 'bg-gray-800 text-white', 'Azul Marino': 'bg-indigo-100 text-indigo-700', 'Multi': 'bg-orange-100 text-orange-700' };
                const colorKey = Object.keys(colores).find(k => v.color.startsWith(k)) || 'Azul';
                const cls = colores[colorKey] || 'bg-gray-100 text-gray-700';
                return `
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full ${cls.split(' ')[0]} border border-gray-300 shrink-0"></span>
                            <span class="font-semibold text-gray-800">${v.color}</span>
                            <span class="text-gray-400">/</span>
                            <span class="font-semibold text-blue-600">${v.talla}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] text-gray-400">${v.sku}</span>
                            <button onclick="eliminarVarianteLista(${i})" class="text-rose-400 hover:text-rose-600 p-1 rounded hover:bg-rose-50">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>`;
            }).join('');
        }

        // ─────────────────────────────────────────────────────────
        // FOTO / IMAGEN
        // ─────────────────────────────────────────────────────────
        function handleFotoChange(event) {
            const file = event.target.files[0];
            if (file) procesarFotoFile(file);
        }

        function handleFotoDrop(event) {
            event.preventDefault();
            document.getElementById('foto-dropzone').classList.remove('border-bc-blue','bg-blue-50');
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) procesarFotoFile(file);
        }

        function procesarFotoFile(file) {
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Archivo muy grande', 'La imagen no debe superar 5 MB.', 'warning');
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                catalogoState.fotoBase64 = e.target.result;
                catalogoState.fotoNombre = file.name;
                document.getElementById('foto-preview-img').src = e.target.result;
                document.getElementById('foto-nombre-txt').textContent = file.name + ' · ' + (file.size/1024).toFixed(0) + ' KB';
                document.getElementById('foto-preview-wrap').classList.remove('hidden');
                document.getElementById('foto-dropzone').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }

        function previewUrlManual() {
            const url = document.getElementById('foto-url-manual').value.trim();
            if (!url) return;
            catalogoState.fotoUrlManual = url;
            document.getElementById('foto-preview-img').src = url;
            document.getElementById('foto-nombre-txt').textContent = 'URL externa';
            document.getElementById('foto-preview-wrap').classList.remove('hidden');
            document.getElementById('foto-dropzone').classList.add('hidden');
        }

        function limpiarFoto() {
            catalogoState.fotoBase64 = null;
            catalogoState.fotoNombre = null;
            catalogoState.fotoUrlManual = null;
            document.getElementById('foto-preview-img').src = '';
            document.getElementById('foto-preview-wrap').classList.add('hidden');
            document.getElementById('foto-dropzone').classList.remove('hidden');
            document.getElementById('foto-input').value = '';
            const urlEl = document.getElementById('foto-url-manual');
            if (urlEl) urlEl.value = '';
        }

        // ─────────────────────────────────────────────────────────
        // GUARDAR PRODUCTO COMPLETO
        // ─────────────────────────────────────────────────────────
        async function guardarProducto() {
            const nombre    = document.getElementById('prod-nombre').value.trim();
            const categoria = document.getElementById('prod-categoria').value;
            const np        = document.getElementById('prod-numero-pieza').value.trim();
            const precio    = parseFloat(document.getElementById('prod-precio').value) || 0;
            const minimo    = parseInt(document.getElementById('prod-minimo').value) || 6;

            if (!nombre || !categoria) {
                Swal.fire('Campos incompletos', 'Nombre y Categoría son obligatorios.', 'warning');
                _irAPaso(1); return;
            }

            // Determinar foto_url final
            let fotoUrl = catalogoState.fotoUrlManual || null;

            // Si hay base64 e internet, subir a Cloudinary
            if (catalogoState.fotoBase64 && bcState.modoBackend === 'mysql') {
                try {
                    const r = await fetch('backend/gestionar_productos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'subir_foto',
                            base64: catalogoState.fotoBase64,
                            product_id: catalogoState.editandoId || 0,
                            tipo: document.getElementById('foto-tipo').value,
                            nombre_archivo: catalogoState.fotoNombre || 'foto'
                        })
                    });
                    const j = await r.json();
                    if (j.success) fotoUrl = j.data.url;
                } catch(e) {
                    // Guardar base64 localmente si falla el upload
                    fotoUrl = catalogoState.fotoBase64;
                }
            } else if (catalogoState.fotoBase64) {
                fotoUrl = catalogoState.fotoBase64; // modo standalone: guardar como data URL
            }

            const payload = {
                action: catalogoState.editandoId ? 'actualizar' : 'crear',
                id: catalogoState.editandoId || undefined,
                nombre, categoria,
                numero_pieza: np,
                coleccion: document.getElementById('prod-coleccion').value.trim() || 'General',
                tipo_tela:  document.getElementById('prod-tipo-tela').value,
                precio_base: precio,
                stock_minimo: minimo,
                descripcion: document.getElementById('prod-descripcion').value.trim(),
                notas: document.getElementById('prod-notas').value.trim(),
                foto_url: fotoUrl,
                variantes: catalogoState.variantes.filter(v => v.activo !== 0)
            };

            if (bcState.modoBackend === 'mysql') {
                try {
                    const r = await fetch('backend/gestionar_productos.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const j = await r.json();
                    if (!j.success) {
                        Swal.fire('Error', j.message || 'No se pudo guardar.', 'error');
                        return;
                    }
                    cerrarModalProducto();
                    renderizarCatalogo();
                    Swal.fire({ icon: 'success', title: '¡Guardado!', text: j.message, timer: 2000, showConfirmButton: false });
                    return;
                } catch(e) { /* fallthrough a local */ }
            }

            // MODO LOCAL
            if (catalogoState.editandoId) {
                const idx = catalogoState.productos.findIndex(p => p.id == catalogoState.editandoId);
                if (idx !== -1) {
                    catalogoState.productos[idx] = { ...catalogoState.productos[idx], ...payload, variantes: payload.variantes };
                }
            } else {
                const newId = Date.now();
                catalogoState.productos.push({
                    id: newId,
                    numero_pieza: np,
                    nombre, categoria,
                    coleccion: payload.coleccion,
                    tipo_tela: payload.tipo_tela,
                    precio_base: precio,
                    stock_minimo: minimo,
                    descripcion: payload.descripcion,
                    foto_url: fotoUrl,
                    notas: payload.notas,
                    variantes: payload.variantes.map((v,i) => ({ ...v, id: Date.now()+i }))
                });
            }

            _guardarCatalogoLocal();
            cerrarModalProducto();
            _pintarCatalogo();
            Swal.fire({ icon: 'success', title: 'Guardado localmente', text: `Modelo "${nombre}" guardado con ${payload.variantes.length} variante(s).`, timer: 2000, showConfirmButton: false });
        }

        // ─────────────────────────────────────────────────────────
        // EDITAR / VER / ELIMINAR
        // ─────────────────────────────────────────────────────────
        function editarProducto(id) {
            abrirModalProducto(id);
        }

        function abrirDetProducto(id) {
            const prod = catalogoState.productos.find(p => p.id == id);
            if (!prod) return;
            const vars = (prod.variantes || []).filter(v => v.activo !== 0);
            const rows = vars.map(v => `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 font-mono text-xs text-bc-blue font-bold">${v.sku}</td>
                    <td class="px-3 py-2 text-xs text-gray-800">${v.color}</td>
                    <td class="px-3 py-2 text-xs text-center"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 font-semibold border border-blue-100 text-[11px]">${v.talla}</span></td>
                    <td class="px-3 py-2 text-xs text-center text-gray-500">${v.precio_variante ? '$'+Number(v.precio_variante).toFixed(2) : '—'}</td>
                </tr>`).join('');

            Swal.fire({
                title: prod.nombre,
                html: `
                    <div class="text-left text-xs">
                        <p class="text-gray-500 mb-3"><span class="font-mono font-bold text-blue-600">${prod.numero_pieza}</span> · ${prod.categoria} · ${prod.tipo_tela}</p>
                        ${prod.foto_url ? `<img src="${prod.foto_url}" class="w-full h-32 object-contain rounded-lg mb-3 border border-gray-200">` : ''}
                        <table class="w-full border border-gray-200 rounded-lg overflow-hidden text-left">
                            <thead class="bg-gray-50 text-[10px] uppercase text-gray-500 border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2">SKU</th>
                                    <th class="px-3 py-2">Color</th>
                                    <th class="px-3 py-2 text-center">Talla</th>
                                    <th class="px-3 py-2 text-center">Precio</th>
                                </tr>
                            </thead>
                            <tbody>${rows || '<tr><td colspan="4" class="text-center p-4 text-gray-400">Sin variantes</td></tr>'}</tbody>
                        </table>
                        <p class="text-gray-400 mt-2">${vars.length} variante(s) · Stock mínimo: ${prod.stock_minimo} pzas</p>
                    </div>`,
                confirmButtonColor: '#0078d4',
                confirmButtonText: 'Cerrar',
                width: '500px'
            });
        }

        async function eliminarProductoCatalogo(id) {
            const conf = await Swal.fire({
                title: '¿Eliminar modelo?',
                text: 'Se desactivará el modelo y todas sus variantes.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!conf.isConfirmed) return;

            if (bcState.modoBackend === 'mysql') {
                try {
                    const r = await fetch('backend/gestionar_productos.php', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'eliminar', id })
                    });
                    const j = await r.json();
                    if (j.success) { renderizarCatalogo(); return; }
                } catch(e) {}
            }

            catalogoState.productos = catalogoState.productos.filter(p => p.id != id);
            _guardarCatalogoLocal();
            _pintarCatalogo();
        }
    </script>
</body>
</html>
