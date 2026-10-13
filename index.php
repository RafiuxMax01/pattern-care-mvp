<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattern Care - Sistema de Gestión Operativa, Producción e Inventarios</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
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
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        },
                        medical: {
                            500: '#0d9488',
                            600: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Scrollbar estilizado */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        @keyframes pulse-subtle {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .pulse-alert {
            animation: pulse-subtle 2s infinite ease-in-out;
        }
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col font-sans selection:bg-brand-500 selection:text-white">

    <!-- BARRA SUPERIOR / HEADER GLOBAL -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-40 px-4 lg:px-8 py-3">
        <div class="flex items-center justify-between gap-4">
            <!-- Logotipo y Nombre -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                    <i class="fa-solid fa-vest-patches text-xl"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-heading font-bold tracking-tight text-white">Pattern Care</h1>
                        <span class="text-xs bg-brand-500/20 text-brand-400 border border-brand-500/30 px-2 py-0.5 rounded-full font-medium">Textil & Médico</span>
                    </div>
                    <p class="text-xs text-slate-400 hidden sm:block">Diagnóstico Operativo, Producción & Inventarios</p>
                </div>
            </div>

            <!-- Centro: Badges de Estado del Servidor & Alerta Crítica -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Indicador de Base de Datos -->
                <div id="db-status-badge" class="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-800 border border-slate-700 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    <span id="db-status-text">Detectando backend...</span>
                </div>

                <!-- Alerta de Stock Mínimo -->
                <div id="stock-alert-pill" class="hidden md:flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-rose-950/70 text-rose-400 border border-rose-800/80">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Stock Crítico (&lt; 6 pzas): <strong id="kpi-alerta-count">0</strong></span>
                </div>
            </div>

            <!-- Acciones Rápidas del Header -->
            <div class="flex items-center gap-2">
                <button onclick="abrirModalSKU()" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold transition shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nuevo SKU</span>
                </button>
                <button onclick="abrirModalMerma()" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition">
                    <i class="fa-solid fa-file-circle-exclamation text-amber-400"></i>
                    <span>Registrar Merma</span>
                </button>
                <button onclick="sincronizarDatos()" title="Recargar y sincronizar datos" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                    <i class="fa-solid fa-rotate text-sm" id="sync-icon"></i>
                </button>
            </div>
        </div>

        <!-- Barra de Navegación por Módulos (Tabs) -->
        <nav class="flex items-center gap-1 sm:gap-2 mt-3 overflow-x-auto pb-1 text-xs sm:text-sm font-medium border-t border-slate-800/80 pt-2 no-print">
            <button onclick="cambiarModulo('dashboard')" class="tab-btn active px-3 py-1.5 rounded-lg text-slate-200 bg-slate-800 border border-slate-700 transition flex items-center gap-2" data-target="modulo-dashboard">
                <i class="fa-solid fa-chart-pie text-brand-400"></i>
                <span>Dashboard</span>
            </button>
            <button onclick="cambiarModulo('organizacion')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-organizacion">
                <i class="fa-solid fa-sitemap text-teal-400"></i>
                <span>Organización</span>
            </button>
            <button onclick="cambiarModulo('flujo')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-flujo">
                <i class="fa-solid fa-arrows-split-up-and-left text-cyan-400"></i>
                <span>Flujo de Producción</span>
            </button>
            <button onclick="cambiarModulo('rendimiento')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-rendimiento">
                <i class="fa-solid fa-calculator text-amber-400"></i>
                <span>Rendimiento de Tela</span>
            </button>
            <button onclick="cambiarModulo('inventario')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-inventario">
                <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                <span>Inventario & SKUs</span>
            </button>
            <button onclick="cambiarModulo('ordenes')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-ordenes">
                <i class="fa-solid fa-clipboard-list text-indigo-400"></i>
                <span>Órdenes de Trabajo</span>
            </button>
            <button onclick="cambiarModulo('mermas')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-mermas">
                <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                <span>Bitácora Mermas</span>
            </button>
            <button onclick="cambiarModulo('diagnostico')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-diagnostico">
                <i class="fa-solid fa-shield-virus text-amber-500"></i>
                <span>Puntos Críticos</span>
            </button>
            <button onclick="cambiarModulo('reportes')" class="tab-btn px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition flex items-center gap-2" data-target="modulo-reportes">
                <i class="fa-solid fa-file-contract text-blue-400"></i>
                <span>Plan de Acción</span>
            </button>
        </nav>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <main class="flex-1 p-4 lg:p-8 max-w-7xl w-full mx-auto space-y-8">

        <!-- ============================================================== -->
        <!-- MÓDULO 0: DASHBOARD PRINCIPAL / RESUMEN EJECUTIVO              -->
        <!-- ============================================================== -->
        <section id="modulo-dashboard" class="modulo-seccion space-y-6">
            
            <!-- Banner de Diagnóstico y Propósito -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-brand-950/40 border border-slate-800 rounded-2xl p-6 relative overflow-hidden">
                <div class="relative z-10 max-w-3xl">
                    <span class="text-xs uppercase tracking-wider font-semibold text-brand-400">Diagnóstico Operativo Textil</span>
                    <h2 class="text-2xl font-heading font-bold text-white mt-1">Línea de Producción, Rendimientos & Inventario</h2>
                    <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                        Sistema integral para resolver la informalidad operativa, mitigar mermas ocultas, controlar el stock mínimo normativo (6 piezas por producto) y desacoplar cuellos de botella externos en estampados y confección de uniformes médicos.
                    </p>
                    <div class="flex flex-wrap items-center gap-3 mt-4">
                        <button onclick="cambiarModulo('reportes')" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold flex items-center gap-2 transition">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Ver Plan de Mitigación Inmediato</span>
                        </button>
                        <button onclick="cambiarModulo('flujo')" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium flex items-center gap-2 transition">
                            <i class="fa-solid fa-diagram-project"></i>
                            <span>Inspeccionar Cadena de Suministro</span>
                        </button>
                    </div>
                </div>
                <div class="absolute right-0 bottom-0 translate-x-8 translate-y-8 text-slate-800/40 text-9xl pointer-events-none select-none">
                    <i class="fa-solid fa-scissors"></i>
                </div>
            </div>

            <!-- KPIs Ejecutivos Principales -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- KPI 1: Personal Total -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Personal Operativo Total</span>
                        <div class="w-8 h-8 rounded-lg bg-teal-500/10 text-teal-400 flex items-center justify-center">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-white">6</span>
                        <span class="text-xs text-slate-400">personas clave</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                        <span>1 Dueña, 1 Admin, 2 Corte, 2 Confección</span>
                    </div>
                </div>

                <!-- KPI 2: Productos Activos -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Catálogo de Productos</span>
                        <div class="w-8 h-8 rounded-lg bg-brand-500/10 text-brand-400 flex items-center justify-center">
                            <i class="fa-solid fa-shirt"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span id="kpi-productos-activos" class="text-3xl font-heading font-bold text-white">3</span>
                        <span class="text-xs text-slate-400">líneas activas</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-400"></span>
                        <span>Gorros, Chamarras, Cubrepolvos</span>
                    </div>
                </div>

                <!-- KPI 3: Stock Mínimo & Alertas -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Nivel de Stock Mínimo</span>
                        <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 flex items-center justify-center">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span class="text-3xl font-heading font-bold text-white">6</span>
                        <span class="text-xs text-slate-400">pzas por SKU norma</span>
                    </div>
                    <div class="mt-2 text-xs text-rose-400 flex items-center gap-1.5 font-medium">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span id="kpi-skus-criticos-txt">0 SKUs por debajo del mínimo</span>
                    </div>
                </div>

                <!-- KPI 4: Mermas y Retrabajos Acumulados -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-slate-700 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-400">Impacto Financiero de Mermas</span>
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <i class="fa-solid fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="mt-3 flex items-baseline gap-2">
                        <span id="kpi-costo-mermas" class="text-3xl font-heading font-bold text-amber-400">$0.00</span>
                        <span class="text-xs text-slate-400">MXN</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-400 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <span id="kpi-mermas-conteo">0 registros de merma/retrabajo</span>
                    </div>
                </div>

            </div>

            <!-- Gráficos y Panel de Alertas Críticas Rápidas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Gráfica de Distribución de Stock -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-heading font-semibold text-white">Existencias de Stock vs Umbral Mínimo</h3>
                            <p class="text-xs text-slate-400">Monitoreo en tiempo real de piezas disponibles por producto (Alerta roja &lt; 6)</p>
                        </div>
                        <button onclick="cambiarModulo('inventario')" class="text-xs text-brand-400 hover:text-brand-300 font-medium">
                            Administrar Inventario &rarr;
                        </button>
                    </div>
                    <div class="h-64 relative">
                        <canvas id="chartInventario"></canvas>
                    </div>
                </div>

                <!-- Resumen de Alertas Críticas del Negocio -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-heading font-semibold text-white">Alertas Operativas Críticas</h3>
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        </div>
                        <div class="space-y-3" id="dashboard-alertas-lista">
                            <!-- Inyectado dinámicamente -->
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-800 mt-4">
                        <button onclick="cambiarModulo('diagnostico')" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold text-center border border-slate-700 transition">
                            Revisar Matriz de Puntos Críticos Completa
                        </button>
                    </div>
                </div>

            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 1: INFORMACIÓN GENERAL Y ESTRUCTURA ORGANIZACIONAL       -->
        <!-- ============================================================== -->
        <section id="modulo-organizacion" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Estructura Organizacional & Canales de Venta</h2>
                    <p class="text-xs text-slate-400">Distribución de responsabilidades del personal (6 personas), canales comerciales y comunicación.</p>
                </div>
                <span class="text-xs px-3 py-1 bg-slate-800 text-slate-300 rounded-full border border-slate-700">6 Colaboradores en Planta</span>
            </div>

            <!-- Organigrama / Roles del Personal -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Rol 1: Dueña / Encargada General -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-brand-500/50 transition relative overflow-hidden group">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-crown text-lg"></i>
                    </div>
                    <span class="text-xs uppercase font-semibold text-purple-400 tracking-wider">1 Persona</span>
                    <h3 class="text-base font-heading font-bold text-white mt-1">Dueña / Encargada General</h3>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Toma de decisiones estratégicas, supervisión general, compras de materia prima mayoritaria y aprobación final de pedidos especiales para hospitales.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 text-xs space-y-1">
                        <div class="text-slate-400"><strong class="text-slate-300">Riesgo detectado:</strong> Sobrecarga de validación manual.</div>
                        <div class="text-teal-400 font-medium">Recomendación: Delegar reglas de calidad.</div>
                    </div>
                </div>

                <!-- Rol 2: Área Administrativa -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-brand-500/50 transition relative overflow-hidden group">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-briefcase text-lg"></i>
                    </div>
                    <span class="text-xs uppercase font-semibold text-blue-400 tracking-wider">1 Persona</span>
                    <h3 class="text-base font-heading font-bold text-white mt-1">Área Administrativa</h3>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Atención directa a clientes, cotizaciones por WhatsApp, facturación, seguimiento de cobros y coordinación logística con el taller externo de estampado.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 text-xs space-y-1">
                        <div class="text-slate-400"><strong class="text-slate-300">Riesgo detectado:</strong> Cuello de botella en mensajes.</div>
                        <div class="text-teal-400 font-medium">Recomendación: Centralizar catálogo digital.</div>
                    </div>
                </div>

                <!-- Rol 3: Corte y Control de Calidad -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-brand-500/50 transition relative overflow-hidden group">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-ruler-combined text-lg"></i>
                    </div>
                    <span class="text-xs uppercase font-semibold text-emerald-400 tracking-wider">2 Personas</span>
                    <h3 class="text-base font-heading font-bold text-white mt-1">Corte & Control de Calidad</h3>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Tendido, trazo y corte de telas SRA/CRO; inspección dimensional de patrones, separación de lotes y validación rigurosa de piezas terminadas.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 text-xs space-y-1">
                        <div class="text-slate-400"><strong class="text-slate-300">Riesgo detectado:</strong> Desperdicio de tela no registrado.</div>
                        <div class="text-teal-400 font-medium">Recomendación: Ficha formal de consumo.</div>
                    </div>
                </div>

                <!-- Rol 4: Confección -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 hover:border-brand-500/50 transition relative overflow-hidden group">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-angles-right text-lg"></i>
                    </div>
                    <span class="text-xs uppercase font-semibold text-amber-400 tracking-wider">2 Personas</span>
                    <h3 class="text-base font-heading font-bold text-white mt-1">Confección Textil</h3>
                    <p class="text-xs text-slate-300 mt-2 leading-relaxed">
                        Armado y costura con máquinas rectas y overlock. Ensamble de gorros quirúrgicos, chamarras antifluido con cierres y cubrepolvos médicos.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 text-xs space-y-1">
                        <div class="text-slate-400"><strong class="text-slate-300">Riesgo detectado:</strong> Retrabajos no contabilizados.</div>
                        <div class="text-teal-400 font-medium">Recomendación: Bitácora de incidencias mecánicas.</div>
                    </div>
                </div>

            </div>

            <!-- Canales Comerciales y de Comunicación -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                
                <!-- Canales de Venta -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-brand-500/20 text-brand-400 flex items-center justify-center">
                            <i class="fa-solid fa-store"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white">Canales de Venta Activos</h3>
                            <p class="text-xs text-slate-400">Diversificación del mercado y demanda</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60 flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs mt-0.5">1</span>
                            <div>
                                <h4 class="text-sm font-semibold text-white">Hospitales y Clínicas (Venta Institucional)</h4>
                                <p class="text-xs text-slate-300 mt-0.5">Pedidos por volumen de uniformes completos y gorros estandarizados. Requisitos estrictos de fecha de entrega y consistencia de tono.</p>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60 flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs mt-0.5">2</span>
                            <div>
                                <h4 class="text-sm font-semibold text-white">Venta Directa a Médicos y Especialistas</h4>
                                <p class="text-xs text-slate-300 mt-0.5">Venta por menudeo en consultorios y ferias médicas. Alta exigencia en diseño, estampados originales y comodidad de tela.</p>
                            </div>
                        </div>
                        <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60 flex items-start gap-3">
                            <span class="w-6 h-6 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center text-xs mt-0.5">3</span>
                            <div>
                                <h4 class="text-sm font-semibold text-white">Venta en Línea & Redes Sociales</h4>
                                <p class="text-xs text-slate-300 mt-0.5">Catálogo digital con envíos por paquetería. Requiere stock de respuesta rápida para no demorar entregas.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Canal de Comunicación Crítico: WhatsApp -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white">Canal Principal: WhatsApp Business</h3>
                            <p class="text-xs text-slate-400">Concentrador operativo de pedidos y cotizaciones</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="p-4 bg-amber-950/20 border border-amber-800/50 rounded-xl">
                            <div class="flex items-center gap-2 text-amber-400 text-xs font-semibold">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <span>Punto Crítico Diagnosticado</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                El 90% de los acuerdos comerciales y especificaciones de pedido viajan por chats de WhatsApp. Si el dispositivo falla o no se respalda, se pierden historiales de órdenes y comprobantes de pago.
                            </p>
                        </div>
                        <div class="p-4 bg-slate-800/50 border border-slate-700 rounded-xl">
                            <h4 class="text-xs font-semibold text-brand-400 uppercase tracking-wider">Protocolo de Formalización Propuesto</h4>
                            <ul class="text-xs text-slate-300 mt-2 space-y-1.5 list-disc list-inside">
                                <li>Asignar código SKU y número de orden único a cada cotización de WhatsApp.</li>
                                <li>Respaldar automáticamente las conversaciones de WhatsApp a Google Drive / OneDrive diariamente.</li>
                                <li>Establecer plantilla de confirmación de pedido con detalles de tela y fecha promesa.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 2: CADENA DE SUMINISTRO Y FLUJO DE PRODUCCIÓN           -->
        <!-- ============================================================== -->
        <section id="modulo-flujo" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Cadena de Suministro & Flujograma de Confección</h2>
                    <p class="text-xs text-slate-400">Mapeo secuencial de 7 fases y contraste entre Gorros Quirúrgicos vs Chamarras/Cubrepolvos.</p>
                </div>
                <!-- Selector de Tipo de Producto para el Flujo -->
                <div class="inline-flex rounded-lg bg-slate-800 p-1 border border-slate-700 text-xs font-medium">
                    <button onclick="seleccionarFlujoTipo('gorros')" id="btn-flujo-gorros" class="px-3 py-1 rounded-md bg-brand-600 text-white transition">
                        Gorros Quirúrgicos (Con Sublimado)
                    </button>
                    <button onclick="seleccionarFlujoTipo('chamarras')" id="btn-flujo-chamarras" class="px-3 py-1 rounded-md text-slate-300 hover:text-white transition">
                        Chamarras & Cubrepolvos (Sin Sublimado)
                    </button>
                </div>
            </div>

            <!-- Diagrama Interactivo de Flujo (Flowchart Horizontal) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 overflow-x-auto">
                <div class="min-w-[850px] relative">
                    <!-- Línea conectora -->
                    <div class="absolute top-1/2 left-8 right-8 h-1 bg-slate-800 -translate-y-1/2 z-0"></div>

                    <!-- Nodos de las 7 Fases -->
                    <div class="grid grid-cols-7 gap-3 relative z-10 text-center">
                        
                        <!-- Fase 1 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(1)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-brand-500 text-brand-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                1
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Diseño</span>
                            <span class="text-[10px] text-slate-400">Patronaje & Colección</span>
                        </div>

                        <!-- Fase 2 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(2)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-teal-500 text-teal-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                2
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Abastecimiento</span>
                            <span class="text-[10px] text-slate-400">Telas SRA / CRO & Avíos</span>
                        </div>

                        <!-- Fase 3: Impresión (Crítica en Gorros) -->
                        <div id="nodo-fase-3" class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(3)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-amber-500 text-amber-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900 pulse-alert">
                                3
                            </div>
                            <span class="mt-2 text-xs font-semibold text-amber-400 flex items-center gap-1">
                                Impresión <i class="fa-solid fa-triangle-exclamation text-[10px]"></i>
                            </span>
                            <span class="text-[10px] text-slate-400">Taller Externo (Gorros)</span>
                        </div>

                        <!-- Fase 4 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(4)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-blue-500 text-blue-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                4
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Corte</span>
                            <span class="text-[10px] text-slate-400">Trazado & Habilitado</span>
                        </div>

                        <!-- Fase 5 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(5)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-indigo-500 text-indigo-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                5
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Confección</span>
                            <span class="text-[10px] text-slate-400">Ensamble & Overlock</span>
                        </div>

                        <!-- Fase 6 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(6)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-emerald-500 text-emerald-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                6
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Empaque</span>
                            <span class="text-[10px] text-slate-400">Control Calidad & Etiqueta</span>
                        </div>

                        <!-- Fase 7 -->
                        <div class="flow-step flex flex-col items-center group cursor-pointer" onclick="verDetalleFase(7)">
                            <div class="w-12 h-12 rounded-full bg-slate-800 border-2 border-purple-500 text-purple-400 flex items-center justify-center font-bold text-sm shadow-md group-hover:scale-110 transition bg-slate-900">
                                7
                            </div>
                            <span class="mt-2 text-xs font-semibold text-white">Distribución</span>
                            <span class="text-[10px] text-slate-400">Hospitales / Paquetería</span>
                        </div>

                    </div>
                </div>

                <!-- Panel de Detalle Dinámico de la Fase Seleccionada -->
                <div id="panel-detalle-fase" class="mt-6 p-4 rounded-xl bg-slate-950/80 border border-slate-800 text-xs">
                    <!-- Contenido inyectado por JS -->
                </div>
            </div>

            <!-- Contraste de Especificaciones Técnicas por Tipo de Producto -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Especificación: Gorros Quirúrgicos -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg text-brand-400"><i class="fa-solid fa-head-side-mask"></i></span>
                            <h3 class="font-heading font-bold text-white">Gorros Quirúrgicos</h3>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-brand-500/20 text-brand-300 font-semibold border border-brand-500/30">Línea Principal</span>
                    </div>
                    <ul class="text-xs text-slate-300 space-y-2">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-teal-400 mt-0.5"></i>
                            <span><strong>Tela Predominante:</strong> Tela SRA (Rendimiento estándar: 6 gorros por metro).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-400 mt-0.5"></i>
                            <span><strong>Cuello de Botella Crítico:</strong> Requiere impresión/sublimado en taller externo. Dependencia de terceros que genera retrasos de hasta 72 hrs.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-blue-400 mt-0.5"></i>
                            <span><strong>Control de Calidad:</strong> Validación de nitidez de estampado y resistencia de cintas de ajuste.</span>
                        </li>
                    </ul>
                </div>

                <!-- Especificación: Chamarras y Cubrepolvos -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg text-teal-400"><i class="fa-solid fa-vest"></i></span>
                            <h3 class="font-heading font-bold text-white">Chamarras Médicas & Cubrepolvos</h3>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-teal-500/20 text-teal-300 font-semibold border border-teal-500/30">Línea de Protección</span>
                    </div>
                    <ul class="text-xs text-slate-300 space-y-2">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-teal-400 mt-0.5"></i>
                            <span><strong>Tela Predominante:</strong> Tela CRO (Rendimiento: 4 piezas y 50 cm sobrantes por metro) y telas antifluido certificadas.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-rose-400 mt-0.5"></i>
                            <span><strong>Punto Crítico Diagnosticado:</strong> Riesgo recurrente de desabasto de insumos (cremalleras reforzadas, bies elástico, tela impermeable).</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-circle-info text-blue-400 mt-0.5"></i>
                            <span><strong>Flujo Operativo:</strong> No requiere taller de impresión externo; pasa directo de abastecimiento a trazo y corte interno.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 3: GESTIÓN DE LOTES, RENDIMIENTO Y MATERIALES           -->
        <!-- ============================================================== -->
        <section id="modulo-rendimiento" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Simulador de Rendimiento de Tela & Gestión de Lotes</h2>
                    <p class="text-xs text-slate-400">Calculadora técnica para Tela SRA (6 pzas/m) y CRO (4 pzas/m + 50cm merma) con trazabilidad completa.</p>
                </div>
                <span class="text-xs px-3 py-1 bg-amber-500/10 text-amber-400 rounded-full border border-amber-500/30 font-medium">Control de Merma Textil</span>
            </div>

            <!-- Calculadora Interactiva de Rendimiento -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Formulario de Entrada -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 lg:col-span-2 space-y-4">
                    <h3 class="font-heading font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-brand-400"></i>
                        <span>Calculadora de Consumo y Aprovechamiento</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Selector de Tipo de Tela -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Tipo de Tela *</label>
                            <select id="sim-tipo-tela" onchange="calcularRendimientoEnVivo()" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                                <option value="SRA">Tela SRA (6 piezas por metro - Gorros)</option>
                                <option value="CRO">Tela CRO (4 piezas y 50 cm sobrante/m - Chamarras/Cubrepolvos)</option>
                            </select>
                        </div>

                        <!-- Metros a Cortar -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Metros Lineales de Tela *</label>
                            <input type="number" id="sim-metros" value="10" min="0.5" step="0.5" oninput="calcularRendimientoEnVivo()" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Color de Tela</label>
                            <input type="text" id="sim-color" placeholder="Ej. Azul Quirúrgico, Blanco, Lila" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>

                        <!-- Colección -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Colección / Estampado</label>
                            <input type="text" id="sim-coleccion" placeholder="Ej. Odontología, Smile, Clásica" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>

                        <!-- Talla -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Talla Destino</label>
                            <select id="sim-talla" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                                <option value="Unitalla">Unitalla (Estándar)</option>
                                <option value="S">Chica (S)</option>
                                <option value="M">Mediana (M)</option>
                                <option value="L">Grande (L)</option>
                                <option value="XL">Extra Grande (XL)</option>
                            </select>
                        </div>

                        <!-- Código de Lote de Referencia -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Código de Lote / Orden</label>
                            <input type="text" id="sim-lote" placeholder="Ej. LOT-2026-X1" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                        </div>
                    </div>

                    <!-- Notas adicionales -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Notas del Trazo y Corte</label>
                        <input type="text" id="sim-notas" placeholder="Ej. Corte al hilo con margen de costura de 1 cm" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-white focus:outline-none focus:border-brand-500">
                    </div>

                    <!-- Botón para Guardar Simulación -->
                    <div class="pt-2 flex justify-end">
                        <button onclick="guardarRendimiento()" class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar Registro de Consumo de Tela</span>
                        </button>
                    </div>
                </div>

                <!-- Resultados en Tiempo Real -->
                <div class="bg-gradient-to-b from-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-teal-400">Resultado Técnico Estimado</span>
                        <h4 class="text-lg font-heading font-bold text-white mt-1" id="res-tipo-titulo">Tela SRA</h4>
                        <p class="text-xs text-slate-400 mt-1" id="res-formula-desc">Rendimiento: 6 piezas completas por metro.</p>

                        <!-- Métricas grandes -->
                        <div class="mt-6 space-y-4">
                            <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60">
                                <span class="text-xs text-slate-400">Piezas Totales Obtenidas</span>
                                <div class="text-3xl font-heading font-extrabold text-white mt-1 flex items-baseline gap-2">
                                    <span id="res-piezas">60</span>
                                    <span class="text-xs font-normal text-teal-400">piezas terminadas</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60">
                                <span class="text-xs text-slate-400">Sobrante / Merma Estimada</span>
                                <div class="text-2xl font-heading font-bold text-amber-400 mt-1 flex items-baseline gap-2">
                                    <span id="res-sobrante">0</span>
                                    <span class="text-xs font-normal text-slate-300" id="res-sobrante-unidad">cm (0.00 m)</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-800/60 rounded-xl border border-slate-700/60">
                                <span class="text-xs text-slate-400">Aprovechamiento Teórico</span>
                                <div class="text-xl font-heading font-bold text-emerald-400 mt-1" id="res-aprovechamiento">
                                    100% de la tela
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-400 mt-4 pt-3 border-t border-slate-800">
                        <i class="fa-solid fa-lightbulb text-amber-400 mr-1"></i>
                        <span>Los sobrantes de Tela CRO pueden recuperarse para cintas de ajuste o bolsas de cubrepolvos.</span>
                    </div>
                </div>

            </div>

            <!-- Historial de Cálculos Recientes -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-heading font-semibold text-white">Historial de Consumo de Telas por Lote</h3>
                    <span class="text-xs text-slate-400">Últimos registros guardados</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase font-semibold border-b border-slate-800">
                            <tr>
                                <th class="p-3">Fecha</th>
                                <th class="p-3">Tela</th>
                                <th class="p-3">Metros</th>
                                <th class="p-3">Piezas Est.</th>
                                <th class="p-3">Sobrante</th>
                                <th class="p-3">Lote</th>
                                <th class="p-3">Color / Colección</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-rendimientos-body" class="divide-y divide-slate-800/60">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 4: PARÁMETROS DE STOCK E INVENTARIO (CONTROL SKU)        -->
        <!-- ============================================================== -->
        <section id="modulo-inventario" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Control de Inventario & Gestión de SKUs</h2>
                    <p class="text-xs text-slate-400">Regla estricta de negocio: Alerta visual inmediata para cualquier producto con menos de 6 piezas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="abrirModalSKU()" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                        <i class="fa-solid fa-plus"></i>
                        <span>Agregar Nuevo SKU</span>
                    </button>
                </div>
            </div>

            <!-- Filtros Rápidos y Buscador -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="filtro-sku-busqueda" oninput="filtrarTablaInventario()" placeholder="Buscar por SKU, Nombre o Color..." class="w-full bg-slate-900 border border-slate-800 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <select id="filtro-sku-categoria" onchange="filtrarTablaInventario()" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-brand-500">
                        <option value="">Todas las Categorías</option>
                        <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                        <option value="Chamarras Médicas">Chamarras Médicas</option>
                        <option value="Cubrepolvos">Cubrepolvos</option>
                    </select>
                </div>
                <div>
                    <select id="filtro-sku-estado" onchange="filtrarTablaInventario()" class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 focus:outline-none focus:border-brand-500">
                        <option value="">Todos los Estados de Stock</option>
                        <option value="critico">Solo Stock Crítico (&lt; 6 pzas)</option>
                        <option value="optimo">Stock Óptimo (&gt;= 6 pzas)</option>
                    </select>
                </div>
            </div>

            <!-- Tabla Interactiva de Inventario -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800 text-[11px]">
                            <tr>
                                <th class="p-3.5">ID / SKU</th>
                                <th class="p-3.5">Producto & Características</th>
                                <th class="p-3.5">Categoría</th>
                                <th class="p-3.5">Color / Talla</th>
                                <th class="p-3.5">Colección</th>
                                <th class="p-3.5 text-center">Stock Actual</th>
                                <th class="p-3.5 text-center">Stock Mínimo</th>
                                <th class="p-3.5 text-center">Estado</th>
                                <th class="p-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-inventario-body" class="divide-y divide-slate-800/60">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 5: SEGUIMIENTO DE ÓRDENES DE PRODUCCIÓN                -->
        <!-- ============================================================== -->
        <section id="modulo-ordenes" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Seguimiento de Órdenes de Producción</h2>
                    <p class="text-xs text-slate-400">Control de flujo secuencial por fases, asignación de responsables y detección de demoras.</p>
                </div>
                <button onclick="abrirModalOrden()" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-plus"></i>
                    <span>Nueva Orden de Producción</span>
                </button>
            </div>

            <!-- Listado de Órdenes en Curso -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="ordenes-grid">
                <!-- Inyectado dinámicamente -->
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 6: BITÁCORA DE MERMAS Y RETRABAJOS                     -->
        <!-- ============================================================== -->
        <section id="modulo-mermas" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Bitácora de Control de Mermas & Retrabajos</h2>
                    <p class="text-xs text-slate-400">Visibilidad de costos ocultos: errores de estampado, costuras desalineadas y empaque incorrecto.</p>
                </div>
                <button onclick="abrirModalMerma()" class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>Registrar Incidencia / Merma</span>
                </button>
            </div>

            <!-- Resumen Financiero de Mermas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <span class="text-xs text-slate-400">Impacto Económico Total</span>
                    <div class="text-2xl font-heading font-bold text-rose-400 mt-1" id="mermas-total-impacto">$0.00 MXN</div>
                    <span class="text-[11px] text-slate-500">Costo acumulado registrado</span>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <span class="text-xs text-slate-400">Mermas Directas (Desecho)</span>
                    <div class="text-2xl font-heading font-bold text-amber-400 mt-1" id="mermas-conteo-merma">0 eventos</div>
                    <span class="text-[11px] text-slate-500">Material inservible</span>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                    <span class="text-xs text-slate-400">Retrabajos (Horas Extra)</span>
                    <div class="text-2xl font-heading font-bold text-blue-400 mt-1" id="mermas-conteo-retrabajo">0 eventos</div>
                    <span class="text-[11px] text-slate-500">Tiempo de corrección absorbido</span>
                </div>
            </div>

            <!-- Tabla de Bitácora de Mermas -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-semibold border-b border-slate-800 text-[11px]">
                            <tr>
                                <th class="p-3.5">Fecha</th>
                                <th class="p-3.5">Tipo</th>
                                <th class="p-3.5">Área de Origen</th>
                                <th class="p-3.5">Descripción de la Incidencia</th>
                                <th class="p-3.5">Material Perdido</th>
                                <th class="p-3.5 text-right">Costo / Impacto</th>
                                <th class="p-3.5">Lote</th>
                                <th class="p-3.5">Reportó</th>
                                <th class="p-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-mermas-body" class="divide-y divide-slate-800/60">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 7: MATRIZ DE PROBLEMAS CRÍTICOS Y PLAN DE MITIGACIÓN     -->
        <!-- ============================================================== -->
        <section id="modulo-diagnostico" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Matriz de Diagnóstico de Puntos Críticos</h2>
                    <p class="text-xs text-slate-400">Evaluación profunda clasificada por Operaciones, Logística e Infraestructura Tecnológica.</p>
                </div>
                <button onclick="cambiarModulo('reportes')" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Generar Reporte Formal</span>
                </button>
            </div>

            <!-- Acordeón / Tarjetas por Área Crítica -->
            <div class="space-y-6">
                
                <!-- Área 1: Operativos y de Calidad -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-industry"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base">1. Problemas Operativos y de Calidad</h3>
                            <p class="text-xs text-slate-400">Línea de confección, errores de confección y validación</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-400">Falta de Línea Formal de Producción</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-semibold">Severidad: Alta</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                Las prendas cambian de operario sin fichas de corte ni tiempos estandarizados, generando lotes heterogéneos y retrasos impredecibles.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Implementar órdenes de corte con plantilla numerada y control de fases en este sistema.
                            </div>
                        </div>

                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-400">Ausencia de Control de Mermas y Retrabajos</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-semibold">Severidad: Media-Alta</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                Las mermas de tela (especialmente en Tela CRO) y las prendas descosidas por errores de costura se absorbían sin costeo, ocultando pérdidas reales.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Registro obligatorio en la bitácora de mermas con cálculo de costo por metro desperdiciado.
                            </div>
                        </div>

                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-400">Errores de Estampados y Empaquetado</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-semibold">Severidad: Media</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                Confusiones de etiquetas de tallas al empacar y variaciones de tono en gorros estampados al no tener muestra física autorizada.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Doble checklist visual antes del embolsado con muestrario físico patrón.
                            </div>
                        </div>

                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-blue-400">Dependencia de Validación Externa</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-semibold">Severidad: Media</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                La dueña o clientes externos deben dar el visto bueno pieza por pieza, deteniendo el flujo cuando no están disponibles.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Empoderar al equipo de Corte y Calidad (2 personas) con criterios de tolerancia específicos.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Área 2: Logística e Inventarios -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base">2. Logística, Inventarios y Cuellos de Botella</h3>
                            <p class="text-xs text-slate-400">Desabasto, proveedores y rotación comercial</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-rose-400">Desabasto de Insumos (Chamarras & Cubrepolvos)</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-semibold">Severidad: Crítica</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                Falta recurrente de cierres, bies y telas especiales antifluido, provocando que los pedidos de chamarras queden inconclusos.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Stock de seguridad de avíos para al menos 20 prendas completas en bodega.
                            </div>
                        </div>

                        <div class="p-4 bg-slate-950/70 border border-slate-800 rounded-xl">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-amber-400">Cuello de Botella en Taller de Sublimado Externo</span>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-semibold">Severidad: Alta</span>
                            </div>
                            <p class="text-xs text-slate-300 mt-2">
                                La confección de gorros quirurgicos se frena hasta 3 días esperando que el taller externo entregue los lienzos impresos.
                            </p>
                            <div class="mt-3 text-xs text-teal-400 bg-teal-950/30 p-2 rounded border border-teal-900/50">
                                <strong>Plan de Mitigación:</strong> Establecer acuerdos de entrega (SLA) con penalización o validar un segundo taller de respaldo.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Área 3: Infraestructura y Datos -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-white text-base">3. Infraestructura y Seguridad de la Información</h3>
                            <p class="text-xs text-slate-400">Resguardo tecnológico y continuidad del negocio</p>
                        </div>
                    </div>
                    <div class="p-4 bg-rose-950/30 border border-rose-800/80 rounded-xl">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-300 flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                                <span>Vulnerabilidad Crítica: Falta de Respaldos en la Nube</span>
                            </span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-semibold">Severidad: Máxima</span>
                        </div>
                        <p class="text-xs text-slate-200 mt-2 leading-relaxed">
                            Actualmente los datos residen exclusivamente en computadoras locales o chats móviles. Una falla en disco duro, extravío de equipo o malware resultaría en la pérdida irreversible de listas de precios, fórmulas de tela, patrones de corte e historiales de clientes institucionales.
                        </p>
                        <div class="mt-3 text-xs text-emerald-300 bg-emerald-950/50 p-2.5 rounded border border-emerald-800">
                            <strong>Acción Correctiva Inmediata:</strong> Implementar script de respaldo diario automatizado de la base de datos MySQL hacia almacenamiento en la nube cifrado (AWS S3, Google Drive o OneDrive) y réplica en servidor web IIS seguro.
                        </div>
                    </div>
                </div>

            </div>

        </section>

        <!-- ============================================================== -->
        <!-- MÓDULO 8: GENERADOR DE REPORTES Y RECOMENDACIONES ESTRATÉGICAS  -->
        <!-- ============================================================== -->
        <section id="modulo-reportes" class="modulo-seccion hidden space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4 no-print">
                <div>
                    <h2 class="text-xl font-heading font-bold text-white">Plan de Acción Estratégico & Reporte Ejecutivo</h2>
                    <p class="text-xs text-slate-400">Consolidación formal para la dirección general y el personal operativo.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="generarPlanAccion()" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Generar Plan de Acción Inmediato</span>
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium flex items-center gap-2 transition">
                        <i class="fa-solid fa-print"></i>
                        <span>Imprimir / Exportar PDF</span>
                    </button>
                </div>
            </div>

            <!-- Contenedor del Reporte Formal -->
            <div id="contenedor-reporte" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6">
                <!-- Encabezado del Reporte -->
                <div class="border-b border-slate-800 pb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-brand-500"></span>
                            <span class="text-xs uppercase tracking-widest text-brand-400 font-bold">Reporte de Diagnóstico Operativo</span>
                        </div>
                        <h2 class="text-2xl font-heading font-bold text-white mt-1">Pattern Care - Taller de Confección Médica</h2>
                        <p class="text-xs text-slate-400">Elaborado para: Dirección General y Jefaturas de Producción</p>
                    </div>
                    <div class="text-left sm:text-right text-xs text-slate-400">
                        <div><strong>Fecha de Emisión:</strong> <span id="reporte-fecha">13 de Octubre de 2026</span></div>
                        <div><strong>Personal Asignado:</strong> 6 Integrantes Clave</div>
                        <div><strong>Estatus Operativo:</strong> En Fase de Formalización</div>
                    </div>
                </div>

                <!-- Resumen de Hallazgos Clave -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800">
                        <span class="text-xs text-slate-400">SKUs Bajo Stock Crítico</span>
                        <div class="text-2xl font-bold text-rose-400 mt-1" id="rep-skus-criticos">0</div>
                        <p class="text-[11px] text-slate-400 mt-1">Prendas con inventario menor a la norma de 6 piezas.</p>
                    </div>
                    <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800">
                        <span class="text-xs text-slate-400">Pérdida por Mermas Registradas</span>
                        <div class="text-2xl font-bold text-amber-400 mt-1" id="rep-costo-mermas">$0.00 MXN</div>
                        <p class="text-[11px] text-slate-400 mt-1">Impacto económico acumulado en el taller.</p>
                    </div>
                    <div class="p-4 bg-slate-950/80 rounded-xl border border-slate-800">
                        <span class="text-xs text-slate-400">Cuellos de Botella Activos</span>
                        <div class="text-2xl font-bold text-teal-400 mt-1">2 Áreas</div>
                        <p class="text-[11px] text-slate-400 mt-1">Taller externo de impresión & insumos de chamarras.</p>
                    </div>
                </div>

                <!-- Plan de Mitigación en 3 Fases -->
                <div>
                    <h3 class="text-base font-heading font-bold text-white mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-road text-brand-400"></i>
                        <span>Hoja de Ruta de Implementación Inmediata</span>
                    </h3>

                    <div class="space-y-4">
                        <!-- Fase 1 -->
                        <div class="p-4 bg-slate-950/60 rounded-xl border-l-4 border-teal-500 border-y border-r border-slate-800">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-teal-400">Fase 1: Estandarización de Corte y Control de Mermas (Semanas 1 - 2)</h4>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-teal-500/10 text-teal-300 font-bold">Prioridad Alta</span>
                            </div>
                            <ul class="text-xs text-slate-300 mt-2 space-y-1 list-disc list-inside">
                                <li>Establecer las fichas de corte con los rendimientos validados: 6 piezas/m para Tela SRA y 4 piezas/m (+50cm sobrante) para Tela CRO.</li>
                                <li>Obligatoriedad de registro en la bitácora de mermas para toda falla de corte o tensión mecánica antes de desarmar una prenda.</li>
                                <li>Revisión semanal de costos acumulados por la Dueña y el Área Administrativa.</li>
                            </ul>
                        </div>

                        <!-- Fase 2 -->
                        <div class="p-4 bg-slate-950/60 rounded-xl border-l-4 border-brand-500 border-y border-r border-slate-800">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-brand-400">Fase 2: Digitalización en Servidor IIS y Respaldos en Nube (Semanas 3 - 4)</h4>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-brand-500/10 text-brand-300 font-bold">Urgente</span>
                            </div>
                            <ul class="text-xs text-slate-300 mt-2 space-y-1 list-disc list-inside">
                                <li>Montar esta aplicación Pattern Care en el servidor local IIS de la empresa con base de datos MySQL activa.</li>
                                <li>Configurar tarea programada de Windows (Task Scheduler) que exporte un volcado `mysqldump` diario hacia almacenamiento en la nube (Google Drive / OneDrive).</li>
                                <li>Migrar los pedidos de WhatsApp a códigos de orden únicos dentro del sistema.</li>
                            </ul>
                        </div>

                        <!-- Fase 3 -->
                        <div class="p-4 bg-slate-950/60 rounded-xl border-l-4 border-purple-500 border-y border-r border-slate-800">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-purple-400">Fase 3: Mitigación de Proveedores Externos y Buffer de Insumos (Mes 2)</h4>
                                <span class="text-[10px] px-2 py-0.5 rounded bg-purple-500/10 text-purple-300 font-bold">Estratégica</span>
                            </div>
                            <ul class="text-xs text-slate-300 mt-2 space-y-1 list-disc list-inside">
                                <li>Homologar un taller de sublimado de respaldo y pactar tiempos máximos de entrega de 24 horas con el taller principal.</li>
                                <li>Adquirir lote de seguridad de cierres y telas antifluido para evitar desabasto en chamarras médicas y cubrepolvos.</li>
                                <li>Capacitar a los 2 operarios de Corte y Calidad en inspección final autónoma sin detener la producción para validación manual.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Firmas de Conformidad -->
                <div class="pt-8 border-t border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-6 text-center text-xs text-slate-400">
                    <div class="space-y-1">
                        <div class="border-b border-slate-700 pb-8"></div>
                        <p class="font-semibold text-slate-300 mt-1">Dueña / Encargada General</p>
                        <p class="text-[10px]">Aprobación Estratégica</p>
                    </div>
                    <div class="space-y-1">
                        <div class="border-b border-slate-700 pb-8"></div>
                        <p class="font-semibold text-slate-300 mt-1">Área Administrativa</p>
                        <p class="text-[10px]">Control de Pedidos & Logística</p>
                    </div>
                    <div class="space-y-1 col-span-2 sm:col-span-1">
                        <div class="border-b border-slate-700 pb-8"></div>
                        <p class="font-semibold text-slate-300 mt-1">Corte, Calidad & Confección</p>
                        <p class="text-[10px]">Equipo Operativo (4 personas)</p>
                    </div>
                </div>
            </div>

        </section>

    </main>

    <!-- FOOTER GLOBAL -->
    <footer class="bg-slate-900 border-t border-slate-800 px-4 py-4 text-center text-xs text-slate-400 no-print">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                <strong>Pattern Care &copy; 2026</strong> &mdash; Sistema de Formalización y Diagnóstico Operativo Textil.
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span>Plataforma: PHP 8.x + MySQL PDO / IIS</span>
                <button onclick="instalarBaseDatos()" class="text-brand-400 hover:text-brand-300 underline font-medium">
                    <i class="fa-solid fa-database mr-1"></i>Reinstalar / Sembrar BD
                </button>
            </div>
        </div>
    </footer>

    <!-- ============================================================== -->
    <!-- MODALES INTERACTIVOS (SKU, MERMA, ORDEN DE PRODUCCIÓN)         -->
    <!-- ============================================================== -->

    <!-- Modal 1: Agregar / Editar SKU -->
    <div id="modal-sku" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
            <button onclick="cerrarModalSKU()" class="absolute right-4 top-4 text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="border-b border-slate-800 pb-3">
                <h3 class="font-heading font-bold text-white text-lg" id="modal-sku-titulo">Registrar Nuevo SKU de Inventario</h3>
                <p class="text-xs text-slate-400">Ingresa los parámetros de identificación y control de stock mínimo.</p>
            </div>

            <form id="form-sku" onsubmit="guardarSKU(event)" class="space-y-3 text-xs">
                <input type="hidden" id="sku-id" value="">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Código SKU *</label>
                        <input type="text" id="sku-codigo" required placeholder="Ej. GQ-AZC-U" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Categoría *</label>
                        <select id="sku-categoria" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                            <option value="Gorros Quirúrgicos">Gorros Quirúrgicos</option>
                            <option value="Chamarras Médicas">Chamarras Médicas</option>
                            <option value="Cubrepolvos">Cubrepolvos</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nombre / Características *</label>
                    <input type="text" id="sku-nombre" required placeholder="Ej. Gorro Quirúrgico Clásico Antifluido" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Color *</label>
                        <input type="text" id="sku-color" required placeholder="Azul Cielo" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Talla *</label>
                        <input type="text" id="sku-talla" required value="Unitalla" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Colección</label>
                        <input type="text" id="sku-coleccion" placeholder="Clásica" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Stock Actual (Piezas) *</label>
                        <input type="number" id="sku-stock-actual" required min="0" value="10" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Stock Mínimo (Alerta si &lt; 6) *</label>
                        <input type="number" id="sku-stock-minimo" required min="1" value="6" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Notas / Observaciones</label>
                    <input type="text" id="sku-notas" placeholder="Ej. Alta demanda en temporada hospitalaria" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalSKU()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white font-semibold">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Registrar Merma / Retrabajo -->
    <div id="modal-merma" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
            <button onclick="cerrarModalMerma()" class="absolute right-4 top-4 text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="border-b border-slate-800 pb-3">
                <h3 class="font-heading font-bold text-white text-lg">Registrar Incidencia de Merma o Retrabajo</h3>
                <p class="text-xs text-slate-400">Control de desperdicio de tela, errores de estampado y tiempo de corrección.</p>
            </div>

            <form id="form-merma" onsubmit="guardarMerma(event)" class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Tipo de Evento *</label>
                        <select id="merma-tipo" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                            <option value="Merma">Merma (Desecho total de material)</option>
                            <option value="Retrabajo">Retrabajo (Tiempo/costura corregible)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Área de Ocurrencia *</label>
                        <select id="merma-area" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                            <option value="Corte">Corte & Trazado</option>
                            <option value="Impresión externa">Impresión / Taller Externo</option>
                            <option value="Confección">Confección & Costura</option>
                            <option value="Empaque">Empaque & Etiquetado</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Descripción del Error *</label>
                    <textarea id="merma-descripcion" required rows="2" placeholder="Ej. Tono descalibrado por taller externo en 15 lienzos de gorro" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Material Desperdiciado *</label>
                        <input type="text" id="merma-material" required placeholder="Ej. 2.5 metros tela CRO, 15 gorros" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Costo / Impacto Económico ($ MXN)</label>
                        <input type="number" id="merma-costo" step="0.01" min="0" value="0.00" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Lote Afectado</label>
                        <input type="text" id="merma-lote" placeholder="Ej. LOT-GQ-01" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Registrado Por</label>
                        <select id="merma-responsable" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                            <option value="Corte y Calidad [2]">Corte y Calidad [2]</option>
                            <option value="Confección [2]">Confección [2]</option>
                            <option value="Área Administrativa">Área Administrativa</option>
                            <option value="Dueña/Encargada General">Dueña/Encargada General</option>
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalMerma()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-semibold">Guardar en Bitácora</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Nueva Orden de Producción -->
    <div id="modal-orden" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl relative">
            <button onclick="cerrarModalOrden()" class="absolute right-4 top-4 text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
            
            <div class="border-b border-slate-800 pb-3">
                <h3 class="font-heading font-bold text-white text-lg">Crear Nueva Orden de Producción</h3>
                <p class="text-xs text-slate-400">Formaliza una orden de trabajo por lote y asigna responsable.</p>
            </div>

            <form id="form-orden" onsubmit="guardarOrden(event)" class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Producto a Confeccionar *</label>
                        <input type="text" id="orden-producto" required placeholder="Ej. Gorro Quirúrgico Clásico" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Código de Lote *</label>
                        <input type="text" id="orden-lote" required placeholder="Ej. LOT-2026-N1" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Cantidad de Piezas *</label>
                        <input type="number" id="orden-cantidad" required min="1" value="50" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Fase Inicial *</label>
                        <select id="orden-fase" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
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
                    <label class="block font-semibold text-slate-300 mb-1">Responsable Operativo *</label>
                    <select id="orden-responsable" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                        <option value="Corte y Calidad [2]">Corte y Calidad [2]</option>
                        <option value="Confección [2]">Confección [2]</option>
                        <option value="Área Administrativa">Área Administrativa / Taller Externo</option>
                        <option value="Dueña/Encargada General">Dueña/Encargada General</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Instrucciones / Observaciones</label>
                    <input type="text" id="orden-notas" placeholder="Ej. Verificar tono de hilo azul marino y remate doble" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-brand-500">
                </div>

                <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalOrden()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300">Cancelar</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">Iniciar Orden</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- LOGICA JAVASCRIPT: MOTOR COMPLETO Y PERSISTENCIA               -->
    <!-- ============================================================== -->
    <script>
        // ESTADO GLOBAL DE LA APLICACIÓN
        let appState = {
            modoBackend: 'detectando', // 'mysql' | 'local_storage'
            productos: [],
            ordenes: [],
            mermas: [],
            rendimientos: [],
            kpis: {},
            alertasCriticas: [],
            chartInstance: null
        };

        // Fases secuenciales de producción
        const FASES_PRODUCCION = ['Diseño', 'Abastecimiento', 'Impresión', 'Corte', 'Confección', 'Empaque', 'Distribución'];

        // Datos semilla por defecto para modo offline / inicial
        const DATOS_SEMILLA_OFFLINE = {
            productos: [
                { id: 1, sku: 'GQ-AZC-U', nombre: 'Gorro Quirúrgico Clásico Antifluido', categoria: 'Gorros Quirúrgicos', color: 'Azul Cielo', talla: 'Unitalla', coleccion: 'Clásica Hospitalaria', stock_actual: 4, stock_minimo: 6, notas: 'Stock crítico por alta demanda en Hospital General' },
                { id: 2, sku: 'GQ-EST-OD', nombre: 'Gorro Quirúrgico Estampado Odonto', categoria: 'Gorros Quirúrgicos', color: 'Multicolor', talla: 'Unitalla', coleccion: 'Odontología Smile', stock_actual: 14, stock_minimo: 6, notas: 'Lote reciente con estampado externo' },
                { id: 3, sku: 'GQ-LIL-U', nombre: 'Gorro Quirúrgico Quirófano Pastel', categoria: 'Gorros Quirúrgicos', color: 'Lila', talla: 'Unitalla', coleccion: 'Colección Pastel', stock_actual: 11, stock_minimo: 6, notas: 'Buena rotación en venta en línea' },
                { id: 4, sku: 'CH-BLA-M', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Blanco Óptico', talla: 'M', coleccion: 'Línea Premier', stock_actual: 3, stock_minimo: 6, notas: 'Desabasto de insumos (cierres y tela blanca)' },
                { id: 5, sku: 'CH-AZM-L', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Azul Marino', talla: 'L', coleccion: 'Línea Premier', stock_actual: 8, stock_minimo: 6, notas: 'Stock estable' },
                { id: 6, sku: 'CH-VER-S', nombre: 'Chamarra Médica Antifluido Stretch', categoria: 'Chamarras Médicas', color: 'Verde Quirófano', talla: 'S', coleccion: 'Línea Premier', stock_actual: 5, stock_minimo: 6, notas: 'Bajo nivel de inventario' },
                { id: 7, sku: 'CP-NEG-U', nombre: 'Cubrepolvo Impermeable con Ajuste', categoria: 'Cubrepolvos', color: 'Negro Oxford', talla: 'Unitalla', coleccion: 'Protección Total', stock_actual: 2, stock_minimo: 6, notas: 'Riesgo inminente de agotamiento' },
                { id: 8, sku: 'CP-AZM-U', nombre: 'Cubrepolvo Quirúrgico Repelente', categoria: 'Cubrepolvos', color: 'Azul Marino', talla: 'Unitalla', coleccion: 'Protección Total', stock_actual: 9, stock_minimo: 6, notas: 'Demanda constante en canal directo' }
            ],
            ordenes: [
                { id: 1, codigo_orden: 'ORD-2026-001', producto: 'Gorro Quirúrgico Odonto Smile', lote: 'LOT-GQ-01', cantidad: 120, fase_actual: 'Impresión', estado: 'Detenido / Alerta', responsable: 'Área Administrativa', notas: 'Retraso de 48 horas en taller de estampado externo. Cuello de botella detectado.' },
                { id: 2, codigo_orden: 'ORD-2026-002', producto: 'Chamarra Médica Premier Azul Marino', lote: 'LOT-CH-04', cantidad: 50, fase_actual: 'Confección', estado: 'En Proceso', responsable: 'Confección [2]', notas: 'Ensamble de cremalleras y pespuntes dobles.' },
                { id: 3, codigo_orden: 'ORD-2026-003', producto: 'Cubrepolvo Impermeable Negro', lote: 'LOT-CP-02', cantidad: 80, fase_actual: 'Corte', estado: 'En Proceso', responsable: 'Corte y Calidad [2]', notas: 'Trazado y corte con tela CRO.' },
                { id: 4, codigo_orden: 'ORD-2026-004', producto: 'Gorro Quirúrgico Clásico Azul Cielo', lote: 'LOT-GQ-03', cantidad: 150, fase_actual: 'Empaque', estado: 'En Proceso', responsable: 'Corte y Calidad [2]', notas: 'Control de calidad final y etiquetado para entrega directa a Hospital.' }
            ],
            mermas: [
                { id: 1, fecha: '2026-10-08', descripcion_error: 'Tono de estampado fuera de escala por falta de muestra física aprobada en taller externo', tipo: 'Merma', area: 'Impresión externa', material_desperdiciado: '18 gorros cortados', costo_impacto: 810.00, lote_afectado: 'LOT-GQ-01', registrado_por: 'Corte y Calidad [2]' },
                { id: 2, fecha: '2026-10-10', descripcion_error: 'Descuadre en costura de cierre por tensión desajustada de máquina overlock', tipo: 'Retrabajo', area: 'Confección', material_desperdiciado: 'Tiempo de descosido + 3 cierres', costo_impacto: 240.00, lote_afectado: 'LOT-CH-04', registrado_por: 'Dueña/Encargada General' },
                { id: 3, fecha: '2026-10-11', descripcion_error: 'Error en etiquetado de tallas durante empaque apresurado', tipo: 'Retrabajo', area: 'Empaque', material_desperdiciado: '40 bolsas selladas reabiertas', costo_impacto: 120.00, lote_afectado: 'LOT-GQ-03', registrado_por: 'Área Administrativa' },
                { id: 4, fecha: '2026-10-12', descripcion_error: 'Melladura en tela al cortar sin fijación adecuada de plantilla', tipo: 'Merma', area: 'Corte', material_desperdiciado: '2.5 metros tela CRO', costo_impacto: 450.00, lote_afectado: 'LOT-CP-02', registrado_por: 'Corte y Calidad [2]' }
            ],
            rendimientos: [
                { id: 1, fecha: '2026-10-12 10:30', tipo_tela: 'SRA', metros: 20, piezas_estimadas: 120, sobrante_cm: 0, lote_referencia: 'LOT-GQ-01', color: 'Multicolor', coleccion: 'Odonto Smile' },
                { id: 2, fecha: '2026-10-12 11:45', tipo_tela: 'CRO', metros: 20, piezas_estimadas: 80, sobrante_cm: 1000, lote_referencia: 'LOT-CP-02', color: 'Negro', coleccion: 'Protección Total' }
            ]
        };

        // INICIALIZACIÓN AL CARGAR LA PÁGINA
        document.addEventListener('DOMContentLoaded', () => {
            verDetalleFase(1);
            calcularRendimientoEnVivo();
            sincronizarDatos();
        });

        // -------------------------------------------------------------
        // CONECTIVIDAD Y SINCRONIZACIÓN (PHP / MYSQL vs LOCAL STORAGE)
        // -------------------------------------------------------------
        async function sincronizarDatos() {
            const syncIcon = document.getElementById('sync-icon');
            if (syncIcon) syncIcon.classList.add('fa-spin');

            const badgeText = document.getElementById('db-status-text');
            const badgeDiv = document.getElementById('db-status-badge');

            // Intentar consultar backend PHP
            try {
                const res = await fetch('backend/obtener_datos.php', {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });

                if (res.ok) {
                    const json = await res.json();
                    if (json.success && json.data) {
                        appState.modoBackend = 'mysql';
                        appState.productos = json.data.productos || [];
                        appState.ordenes = json.data.ordenes || [];
                        appState.mermas = json.data.mermas || [];
                        appState.rendimientos = json.data.rendimientos || [];
                        appState.kpis = json.data.kpis || {};
                        appState.alertasCriticas = json.data.alertas_criticas || [];

                        badgeDiv.className = 'flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-950 text-emerald-400 border border-emerald-800';
                        badgeDiv.innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-400"></span><span>MySQL Conectado (IIS/PDO)</span>';
                        
                        renderizarTodo();
                        if (syncIcon) syncIcon.classList.remove('fa-spin');
                        return;
                    }
                }
                throw new Error('Backend no respondió exitosamente');
            } catch (err) {
                // Modo Fallback / Demostración Local
                console.info('Modo LocalStorage activado (servidor PHP/MySQL no detectado o offline):', err.message);
                appState.modoBackend = 'local_storage';

                cargarDesdeLocalStorage();

                badgeDiv.className = 'flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-amber-950/80 text-amber-300 border border-amber-800/80 cursor-pointer';
                badgeDiv.title = 'Haz clic para intentar inicializar o probar conexión MySQL';
                badgeDiv.onclick = instalarBaseDatos;
                badgeDiv.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-400"></span><span>Modo Standalone / LocalStorage</span>';

                renderizarTodo();
            }

            if (syncIcon) syncIcon.classList.remove('fa-spin');
        }

        function cargarDesdeLocalStorage() {
            const guardado = localStorage.getItem('pattern_care_data');
            if (guardado) {
                try {
                    const parsed = JSON.parse(guardado);
                    appState.productos = parsed.productos || DATOS_SEMILLA_OFFLINE.productos;
                    appState.ordenes = parsed.ordenes || DATOS_SEMILLA_OFFLINE.ordenes;
                    appState.mermas = parsed.mermas || DATOS_SEMILLA_OFFLINE.mermas;
                    appState.rendimientos = parsed.rendimientos || DATOS_SEMILLA_OFFLINE.rendimientos;
                } catch(e) {
                    appState.productos = [...DATOS_SEMILLA_OFFLINE.productos];
                    appState.ordenes = [...DATOS_SEMILLA_OFFLINE.ordenes];
                    appState.mermas = [...DATOS_SEMILLA_OFFLINE.mermas];
                    appState.rendimientos = [...DATOS_SEMILLA_OFFLINE.rendimientos];
                }
            } else {
                appState.productos = [...DATOS_SEMILLA_OFFLINE.productos];
                appState.ordenes = [...DATOS_SEMILLA_OFFLINE.ordenes];
                appState.mermas = [...DATOS_SEMILLA_OFFLINE.mermas];
                appState.rendimientos = [...DATOS_SEMILLA_OFFLINE.rendimientos];
                guardarEnLocalStorage();
            }

            calcularKPIsLocales();
        }

        function guardarEnLocalStorage() {
            localStorage.setItem('pattern_care_data', JSON.stringify({
                productos: appState.productos,
                ordenes: appState.ordenes,
                mermas: appState.mermas,
                rendimientos: appState.rendimientos
            }));
            calcularKPIsLocales();
        }

        function calcularKPIsLocales() {
            let skusCriticos = 0;
            let stockTotal = 0;
            let costoMermas = 0;
            let conteoMerma = 0;
            let conteoRetrabajo = 0;

            appState.productos.forEach(p => {
                stockTotal += parseInt(p.stock_actual);
                if (parseInt(p.stock_actual) < parseInt(p.stock_minimo)) {
                    skusCriticos++;
                }
            });

            appState.mermas.forEach(m => {
                costoMermas += parseFloat(m.costo_impacto || 0);
                if (m.tipo === 'Merma') conteoMerma++;
                else conteoRetrabajo++;
            });

            appState.kpis = {
                personal_total: 6,
                productos_activos: appState.productos.length,
                stock_total_piezas: stockTotal,
                skus_bajo_minimo: skusCriticos,
                costo_total_mermas: costoMermas.toFixed(2),
                total_mermas_reg: conteoMerma,
                total_retrabajos_reg: conteoRetrabajo
            };

            appState.alertasCriticas = [
                { area: 'Logística Externa', titulo: 'Cuello de Botella: Taller de Impresión/Sublimado', descripcion: 'Dependencia externa sin SLA para gorros estampados. Causa retrasos directos en la entrega.', severidad: 'alta' },
                { area: 'Inventario de Materias Primas', titulo: 'Riesgo de Desabasto en Chamarras y Cubrepolvos', descripcion: 'Falta de tela impermeable y cierres para mantener stock regulatorio de 6 piezas mínimas.', severidad: 'critica' },
                { area: 'Infraestructura Tecnológica', titulo: 'Ausencia de Respaldos en la Nube', descripcion: 'Vulnerabilidad ante fallas en hardware local. Riesgo latente de pérdida de registros de clientes e inventario.', severidad: 'critica' },
                { area: 'Control de Calidad', titulo: 'Merma Oculta y Retrabajos sin Estandarizar', descripcion: 'Pérdidas acumuladas por costuras desajustadas y errores en empaquetado no registradas en línea formal.', severidad: 'media' }
            ];
        }

        // -------------------------------------------------------------
        // RENDERIZADO GLOBAL DE LA INTERFAZ
        // -------------------------------------------------------------
        function renderizarTodo() {
            renderizarKPIs();
            renderizarGraficaInventario();
            renderizarAlertasDashboard();
            renderizarTablaInventario();
            renderizarGridOrdenes();
            renderizarTablaMermas();
            renderizarTablaRendimientos();
            actualizarResumenReporte();
        }

        function renderizarKPIs() {
            const kpis = appState.kpis;
            document.getElementById('kpi-productos-activos').innerText = kpis.productos_activos || appState.productos.length;
            document.getElementById('kpi-alerta-count').innerText = kpis.skus_bajo_minimo || 0;
            document.getElementById('kpi-skus-criticos-txt').innerText = `${kpis.skus_bajo_minimo || 0} SKUs por debajo del mínimo (6 pzas)`;
            document.getElementById('kpi-costo-mermas').innerText = `$${Number(kpis.costo_total_mermas || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 })}`;
            document.getElementById('kpi-mermas-conteo').innerText = `${(kpis.total_mermas_reg || 0) + (kpis.total_retrabajos_reg || 0)} registros en bitácora`;

            const pill = document.getElementById('stock-alert-pill');
            if (pill) {
                if ((kpis.skus_bajo_minimo || 0) > 0) {
                    pill.classList.remove('hidden');
                } else {
                    pill.classList.add('hidden');
                }
            }
        }

        function renderizarAlertasDashboard() {
            const cont = document.getElementById('dashboard-alertas-lista');
            if (!cont) return;

            let html = '';
            (appState.alertasCriticas || []).slice(0, 3).forEach(a => {
                const colorBorder = a.severidad === 'critica' ? 'border-rose-800 bg-rose-950/20' : 'border-amber-800 bg-amber-950/20';
                const colorBadge = a.severidad === 'critica' ? 'text-rose-400 bg-rose-500/20' : 'text-amber-400 bg-amber-500/20';
                html += `
                    <div class="p-3 rounded-xl border ${colorBorder}">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] uppercase font-bold tracking-wider ${colorBadge} px-2 py-0.5 rounded">${a.area}</span>
                            <span class="text-[10px] text-slate-400 uppercase">${a.severidad}</span>
                        </div>
                        <h4 class="text-xs font-semibold text-white mt-1.5">${a.titulo}</h4>
                        <p class="text-[11px] text-slate-300 mt-0.5">${a.descripcion}</p>
                    </div>
                `;
            });
            cont.innerHTML = html;
        }

        function renderizarGraficaInventario() {
            const ctx = document.getElementById('chartInventario');
            if (!ctx) return;

            const labels = appState.productos.map(p => p.sku);
            const dataStock = appState.productos.map(p => p.stock_actual);
            const dataMinimo = appState.productos.map(p => p.stock_minimo);
            const backgroundColors = dataStock.map((val, idx) => {
                return val < dataMinimo[idx] ? 'rgba(239, 68, 68, 0.85)' : 'rgba(14, 165, 233, 0.85)';
            });

            if (appState.chartInstance) {
                appState.chartInstance.destroy();
            }

            appState.chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Stock Actual',
                            data: dataStock,
                            backgroundColor: backgroundColors,
                            borderRadius: 6
                        },
                        {
                            label: 'Umbral Mínimo (6 pzas)',
                            data: dataMinimo,
                            type: 'line',
                            borderColor: 'rgba(245, 158, 11, 0.9)',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 3,
                            pointBackgroundColor: 'rgba(245, 158, 11, 1)'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: { color: 'rgba(51, 65, 85, 0.3)' },
                            ticks: { color: '#94a3b8', font: { size: 10 } }
                        },
                        y: {
                            grid: { color: 'rgba(51, 65, 85, 0.3)' },
                            ticks: { color: '#94a3b8', font: { size: 10 }, stepSize: 2 }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#cbd5e1', font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // -------------------------------------------------------------
        // MÓDULO 4: TABLA DE INVENTARIO Y SKUs
        // -------------------------------------------------------------
        function renderizarTablaInventario() {
            const tbody = document.getElementById('tabla-inventario-body');
            if (!tbody) return;

            const busqueda = (document.getElementById('filtro-sku-busqueda')?.value || '').toLowerCase();
            const catFiltro = document.getElementById('filtro-sku-categoria')?.value || '';
            const estFiltro = document.getElementById('filtro-sku-estado')?.value || '';

            const filtrados = appState.productos.filter(p => {
                const matchTexto = p.sku.toLowerCase().includes(busqueda) || 
                                   p.nombre.toLowerCase().includes(busqueda) || 
                                   p.color.toLowerCase().includes(busqueda);
                const matchCat = catFiltro === '' || p.categoria === catFiltro;
                let matchEst = true;
                if (estFiltro === 'critico') matchEst = parseInt(p.stock_actual) < parseInt(p.stock_minimo);
                if (estFiltro === 'optimo') matchEst = parseInt(p.stock_actual) >= parseInt(p.stock_minimo);
                return matchTexto && matchCat && matchEst;
            });

            if (filtrados.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-6 text-center text-slate-400">No se encontraron productos que coincidan con los filtros.</td></tr>`;
                return;
            }

            let html = '';
            filtrados.forEach(p => {
                const esBajo = parseInt(p.stock_actual) < parseInt(p.stock_minimo);
                const badge = esBajo 
                    ? `<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/40 flex items-center gap-1 w-max mx-auto"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>Crítico (&lt;6)</span>`
                    : `<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 w-max mx-auto">Óptimo</span>`;

                html += `
                    <tr class="hover:bg-slate-850 transition ${esBajo ? 'bg-rose-950/10' : ''}">
                        <td class="p-3.5 font-mono font-bold text-white">${p.sku}</td>
                        <td class="p-3.5">
                            <div class="font-medium text-white">${p.nombre}</div>
                            <div class="text-[11px] text-slate-400">${p.notas || 'Sin observaciones'}</div>
                        </td>
                        <td class="p-3.5 text-slate-300">${p.categoria}</td>
                        <td class="p-3.5">
                            <span class="text-white">${p.color}</span> / <span class="text-slate-400">${p.talla}</span>
                        </td>
                        <td class="p-3.5 text-slate-400">${p.coleccion || 'General'}</td>
                        <td class="p-3.5 text-center">
                            <div class="inline-flex items-center gap-2">
                                <button onclick="ajustarStock(${p.id}, -1)" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">-</button>
                                <span class="font-bold text-base ${esBajo ? 'text-rose-400' : 'text-white'}">${p.stock_actual}</span>
                                <button onclick="ajustarStock(${p.id}, 1)" class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">+</button>
                            </div>
                        </td>
                        <td class="p-3.5 text-center text-slate-400 font-semibold">${p.stock_minimo}</td>
                        <td class="p-3.5 text-center">${badge}</td>
                        <td class="p-3.5 text-right space-x-1 whitespace-nowrap">
                            <button onclick="editarSKU(${p.id})" title="Editar SKU" class="p-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button onclick="eliminarSKU(${p.id})" title="Eliminar SKU" class="p-1.5 rounded bg-slate-800 hover:bg-rose-900/60 text-rose-400 transition">
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

        async function ajustarStock(id, delta) {
            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'ajustar_stock', id: id, delta: delta })
                    });
                    const json = await res.json();
                    if (json.success) {
                        const idx = appState.productos.findIndex(p => p.id == id);
                        if (idx !== -1) {
                            appState.productos[idx].stock_actual = json.data.stock_actual;
                        }
                        renderizarTodo();
                        return;
                    }
                } catch(e) {
                    console.error('Error al sincronizar stock en backend:', e);
                }
            }

            // Fallback Local
            const item = appState.productos.find(p => p.id == id);
            if (item) {
                item.stock_actual = Math.max(0, parseInt(item.stock_actual) + delta);
                guardarEnLocalStorage();
                renderizarTodo();
            }
        }

        function abrirModalSKU(id = null) {
            document.getElementById('form-sku').reset();
            document.getElementById('sku-id').value = '';
            document.getElementById('modal-sku-titulo').innerText = 'Registrar Nuevo SKU de Inventario';

            if (id) {
                const p = appState.productos.find(x => x.id == id);
                if (p) {
                    document.getElementById('modal-sku-titulo').innerText = 'Editar SKU: ' + p.sku;
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

        function editarSKU(id) {
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
                notas: document.getElementById('sku-notas').value.trim()
            };

            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (!json.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: json.message });
                        return;
                    }
                    cerrarModalSKU();
                    sincronizarDatos();
                    Swal.fire({ icon: 'success', title: 'Guardado', text: 'Producto actualizado en la base de datos MySQL.', timer: 1500, showConfirmButton: false });
                    return;
                } catch(err) {
                    console.error('Error enviando al backend:', err);
                }
            }

            // Modo Local
            if (id) {
                const idx = appState.productos.findIndex(p => p.id == id);
                if (idx !== -1) {
                    appState.productos[idx] = { ...appState.productos[idx], ...payload };
                }
            } else {
                payload.id = Date.now();
                appState.productos.push(payload);
            }
            guardarEnLocalStorage();
            cerrarModalSKU();
            renderizarTodo();
            Swal.fire({ icon: 'success', title: 'Guardado Local', text: 'Producto guardado en memoria local.', timer: 1500, showConfirmButton: false });
        }

        async function eliminarSKU(id) {
            const conf = await Swal.fire({
                title: '¿Eliminar producto?',
                text: 'Esta acción removerá el SKU del catálogo de inventario.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!conf.isConfirmed) return;

            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/guardar_inventario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'eliminar', id: id })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        Swal.fire('Eliminado', 'Producto eliminado de la base de datos.', 'success');
                        return;
                    }
                } catch(e) {
                    console.error('Error al eliminar en backend:', e);
                }
            }

            // Local
            appState.productos = appState.productos.filter(p => p.id != id);
            guardarEnLocalStorage();
            renderizarTodo();
            Swal.fire('Eliminado', 'Producto eliminado localmente.', 'success');
        }

        // -------------------------------------------------------------
        // MÓDULO 3: CALCULADORA Y RENDIMIENTO DE TELAS
        // -------------------------------------------------------------
        function calcularRendimientoEnVivo() {
            const tipo = document.getElementById('sim-tipo-tela').value;
            const metros = parseFloat(document.getElementById('sim-metros').value) || 0;

            let piezas = 0;
            let sobranteCm = 0;
            let formulaDesc = '';
            let aprovechamientoTxt = '';

            if (tipo === 'SRA') {
                // Tela SRA: 6 piezas por metro
                piezas = Math.floor(metros * 6);
                sobranteCm = 0;
                formulaDesc = 'Rendimiento: 6 piezas exactas por metro de tela SRA.';
                aprovechamientoTxt = '100% de la tela aprovechada (0 cm merma)';
                document.getElementById('res-tipo-titulo').innerText = 'Tela SRA (Gorros Quirúrgicos)';
            } else {
                // Tela CRO: 4 piezas y 50 cm sobrantes por metro
                piezas = Math.floor(metros * 4);
                sobranteCm = metros * 50;
                formulaDesc = 'Rendimiento: 4 piezas y 50 cm de merma/sobrante por metro.';
                const sobranteMetros = (sobranteCm / 100).toFixed(2);
                aprovechamientoTxt = `Aprovechamiento con ~${sobranteMetros} m de sobrante acumulado`;
                document.getElementById('res-tipo-titulo').innerText = 'Tela CRO (Chamarras & Cubrepolvos)';
            }

            document.getElementById('res-formula-desc').innerText = formulaDesc;
            document.getElementById('res-piezas').innerText = piezas;
            document.getElementById('res-sobrante').innerText = sobranteCm;
            document.getElementById('res-sobrante-unidad').innerText = `cm (${(sobranteCm / 100).toFixed(2)} m)`;
            document.getElementById('res-aprovechamiento').innerText = aprovechamientoTxt;
        }

        async function guardarRendimiento() {
            const tipo = document.getElementById('sim-tipo-tela').value;
            const metros = parseFloat(document.getElementById('sim-metros').value) || 0;
            if (metros <= 0) {
                Swal.fire('Atención', 'Ingresa una cantidad de metros mayor a 0', 'info');
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

            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/calcular_rendimiento.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        Swal.fire({ icon: 'success', title: 'Cálculo Registrado', text: 'Guardado en la base de datos MySQL.', timer: 1500, showConfirmButton: false });
                        return;
                    }
                } catch(e) {
                    console.error('Error calculando en backend:', e);
                }
            }

            // Local
            let piezas = tipo === 'SRA' ? Math.floor(metros * 6) : Math.floor(metros * 4);
            let sobrante = tipo === 'SRA' ? 0 : metros * 50;

            appState.rendimientos.unshift({
                id: Date.now(),
                fecha: new Date().toISOString().replace('T', ' ').substring(0, 16),
                tipo_tela: tipo,
                metros: metros,
                piezas_estimadas: piezas,
                sobrante_cm: sobrante,
                lote_referencia: payload.lote_referencia,
                color: payload.color,
                coleccion: payload.coleccion
            });
            guardarEnLocalStorage();
            renderizarTablaRendimientos();
            Swal.fire({ icon: 'success', title: 'Registrado Localmente', text: 'Cálculo guardado en el historial.', timer: 1500, showConfirmButton: false });
        }

        function renderizarTablaRendimientos() {
            const tbody = document.getElementById('tabla-rendimientos-body');
            if (!tbody) return;

            if (appState.rendimientos.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-slate-400">Sin consumos de tela calculados.</td></tr>`;
                return;
            }

            let html = '';
            appState.rendimientos.slice(0, 8).forEach(r => {
                html += `
                    <tr class="hover:bg-slate-850">
                        <td class="p-3 text-slate-400 font-mono text-[11px]">${r.fecha ? r.fecha.substring(0, 16) : 'Reciente'}</td>
                        <td class="p-3 font-semibold ${r.tipo_tela === 'SRA' ? 'text-teal-400' : 'text-amber-400'}">${r.tipo_tela}</td>
                        <td class="p-3 font-bold text-white">${r.metros} m</td>
                        <td class="p-3 font-bold text-emerald-400">${r.piezas_estimadas} pzas</td>
                        <td class="p-3 text-slate-300">${r.sobrante_cm} cm</td>
                        <td class="p-3 font-mono text-slate-300">${r.lote_referencia || 'N/A'}</td>
                        <td class="p-3 text-slate-400">${r.color || ''} ${r.coleccion ? '('+r.coleccion+')' : ''}</td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        // -------------------------------------------------------------
        // MÓDULO 2: FLUJOGRAMA INTERACTIVO Y DETALLE DE FASES
        // -------------------------------------------------------------
        const DETALLES_FASES = {
            1: {
                nombre: 'Fase 1: Diseño & Patronaje',
                responsable: 'Dueña / Encargada General + Corte y Calidad [2]',
                tiempo: '24 a 48 horas',
                insumos: 'Patrones en papel kraft, muestras textiles y especificaciones de clientes/hospitales.',
                riesgo: 'Falta de graduación estandarizada de tallas S, M, L en chamarras.',
                mitigacion: 'Patronaje digital base con tolerancias impresas.'
            },
            2: {
                nombre: 'Fase 2: Abastecimiento de Materias Primas',
                responsable: 'Área Administrativa & Dueña',
                tiempo: '1 a 3 días según proveedor',
                insumos: 'Rollos de Tela SRA, Tela CRO, cierres reforzados, elásticos y bies.',
                riesgo: 'Punto crítico en Chamarras y Cubrepolvos: Desabasto imprevisto de cierres y tela impermeable.',
                mitigacion: 'Mantener buffer de seguridad de avíos en almacén para 20 prendas mínimas.'
            },
            3: {
                nombre: 'Fase 3: Impresión / Sublimado (Taller Externo)',
                responsable: 'Taller Externo tercerizado (Coordinado por Área Administrativa)',
                tiempo: '48 a 72 horas (CUELLO DE BOTELLA CRÍTICO)',
                insumos: 'Lienzos de tela SRA para gorros quirúrgicos estampados.',
                riesgo: 'Retrasos de entrega, variaciones de tono cromático y merma oculta por sublimado deficiente.',
                mitigacion: 'Exigir muestra física autorizada previa y acuerdos de nivel de servicio (SLA) con penalización.'
            },
            4: {
                nombre: 'Fase 4: Trazo & Corte de Piezas',
                responsable: 'Corte y Control de Calidad [2 personas]',
                tiempo: '2 a 4 horas por lote de 100 prendas',
                insumos: 'Tijeras de corte vertical, mesas de tendido, gis de sastre.',
                riesgo: 'Desperdicio no controlado en Tela CRO (50 cm sobrante por metro no recuperados).',
                mitigacion: 'Uso obligatorio del simulador de corte para reutilizar retazos en cintas y bolsas.'
            },
            5: {
                nombre: 'Fase 5: Confección & Ensamble Textil',
                responsable: 'Confección [2 personas]',
                tiempo: '15 a 30 minutos por prenda según complejidad',
                insumos: 'Máquinas overlock de 4 hilos, máquinas rectas industriales, conos de hilo poliéster.',
                riesgo: 'Desajuste de tensión que causa descosidos y retrabajos no contabilizados.',
                mitigacion: 'Calibración matutina de máquinas y registro de incidencias en bitácora.'
            },
            6: {
                nombre: 'Fase 6: Control de Calidad & Empaque',
                responsable: 'Corte y Control de Calidad [2 personas]',
                tiempo: '1 hora por lote de 50 prendas',
                insumos: 'Bolsas sellables de polietileno, etiquetas de talla y código de barras, tijeras de pulir.',
                riesgo: 'Prendas empacadas con etiquetas de talla trocadas durante prisas de entrega.',
                mitigacion: 'Doble chequeo visual con sello de inspector antes del embalaje.'
            },
            7: {
                nombre: 'Fase 7: Distribución & Entrega',
                responsable: 'Área Administrativa & Logística',
                tiempo: 'Mismo día a 48 horas',
                insumos: 'Cajas corrugadas, guías de paquetería, remisiones para hospitales.',
                riesgo: 'Falta de número de rastreo vinculado al pedido de WhatsApp.',
                mitigacion: 'Notificación de guía automatizada con código de orden del sistema.'
            }
        };

        function verDetalleFase(num) {
            const f = DETALLES_FASES[num];
            if (!f) return;

            const cont = document.getElementById('panel-detalle-fase');
            cont.innerHTML = `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-2 mb-2">
                    <span class="font-heading font-bold text-sm text-brand-400">${f.nombre}</span>
                    <span class="text-slate-400">Responsable: <strong class="text-white">${f.responsable}</strong></span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <span class="text-slate-400 font-semibold">Tiempo Estimado:</span>
                        <div class="text-slate-200 mt-0.5">${f.tiempo}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold">Riesgo / Punto Crítico:</span>
                        <div class="text-amber-400 mt-0.5">${f.riesgo}</div>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold">Plan de Mitigación:</span>
                        <div class="text-teal-400 mt-0.5">${f.mitigacion}</div>
                    </div>
                </div>
            `;
        }

        function seleccionarFlujoTipo(tipo) {
            const btnG = document.getElementById('btn-flujo-gorros');
            const btnC = document.getElementById('btn-flujo-chamarras');
            const nodoFase3 = document.getElementById('nodo-fase-3');

            if (tipo === 'gorros') {
                btnG.className = 'px-3 py-1 rounded-md bg-brand-600 text-white transition';
                btnC.className = 'px-3 py-1 rounded-md text-slate-300 hover:text-white transition';
                nodoFase3.classList.remove('opacity-30', 'grayscale');
                verDetalleFase(3);
            } else {
                btnC.className = 'px-3 py-1 rounded-md bg-teal-600 text-white transition';
                btnG.className = 'px-3 py-1 rounded-md text-slate-300 hover:text-white transition';
                nodoFase3.classList.add('opacity-30', 'grayscale');
                verDetalleFase(2);
            }
        }

        // -------------------------------------------------------------
        // MÓDULO 5: ÓRDENES DE PRODUCCIÓN
        // -------------------------------------------------------------
        function renderizarGridOrdenes() {
            const grid = document.getElementById('ordenes-grid');
            if (!grid) return;

            if (appState.ordenes.length === 0) {
                grid.innerHTML = `<div class="p-8 text-center text-slate-400 col-span-3">No hay órdenes de producción activas. Crea una nueva orden para formalizar la línea.</div>`;
                return;
            }

            let html = '';
            appState.ordenes.forEach(o => {
                const esAlerta = o.estado === 'Detenido / Alerta';
                const badgeEstado = esAlerta
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-ping"></span>Detenido</span>'
                    : '<span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">En Proceso</span>';

                const faseIndex = FASES_PRODUCCION.indexOf(o.fase_actual);
                const siguienteFase = faseIndex < FASES_PRODUCCION.length - 1 ? FASES_PRODUCCION[faseIndex + 1] : null;

                html += `
                    <div class="bg-slate-900 border ${esAlerta ? 'border-rose-800' : 'border-slate-800'} rounded-2xl p-5 flex flex-col justify-between hover:border-slate-700 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs text-brand-400">${o.codigo_orden}</span>
                                ${badgeEstado}
                            </div>
                            <h4 class="text-sm font-heading font-bold text-white mt-2">${o.producto}</h4>
                            <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                                <span>Lote: <strong class="text-slate-300">${o.lote}</strong></span>
                                &bull;
                                <span>Cantidad: <strong class="text-white">${o.cantidad} pzas</strong></span>
                            </div>

                            <!-- Barra de progreso de fase -->
                            <div class="mt-4 p-2.5 bg-slate-950 rounded-xl border border-slate-800/80">
                                <div class="flex items-center justify-between text-[11px] mb-1">
                                    <span class="text-slate-400">Fase Actual:</span>
                                    <span class="font-bold text-teal-400">${o.fase_actual}</span>
                                </div>
                                <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-teal-500 h-full rounded-full" style="width: ${((faseIndex + 1) / FASES_PRODUCCION.length) * 100}%"></div>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1.5 flex items-center justify-between">
                                    <span>Resp: ${o.responsable}</span>
                                    <span>Paso ${faseIndex + 1}/7</span>
                                </div>
                            </div>

                            ${o.notas ? `<p class="text-[11px] text-slate-300 mt-3 bg-slate-800/40 p-2 rounded-lg border border-slate-700/40">${o.notas}</p>` : ''}
                        </div>

                        <!-- Acciones de la Orden -->
                        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                            ${siguienteFase ? `
                                <button onclick="avanzarFaseOrden(${o.id}, '${siguienteFase}')" class="px-3 py-1.5 rounded-lg bg-teal-600/80 hover:bg-teal-500 text-white text-xs font-semibold flex items-center gap-1.5 transition">
                                    <span>Avanzar a ${siguienteFase}</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </button>
                            ` : `
                                <span class="text-xs text-emerald-400 font-semibold flex items-center gap-1"><i class="fa-solid fa-check"></i> Completada</span>
                            `}
                            <button onclick="toggleAlertaOrden(${o.id})" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs transition" title="Alternar Alerta">
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

            if (appState.modoBackend === 'mysql') {
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
                        Swal.fire({ icon: 'success', title: 'Orden Creada', text: 'Registrada en la base de datos MySQL.', timer: 1500, showConfirmButton: false });
                        return;
                    }
                } catch(e) {
                    console.error('Error creando orden en backend:', e);
                }
            }

            // Local
            payload.id = Date.now();
            appState.ordenes.unshift(payload);
            guardarEnLocalStorage();
            cerrarModalOrden();
            renderizarGridOrdenes();
            Swal.fire({ icon: 'success', title: 'Orden Creada', text: 'Registrada localmente.', timer: 1500, showConfirmButton: false });
        }

        async function avanzarFaseOrden(id, nuevaFase) {
            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/registrar_produccion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'cambiar_fase', id: id, fase_actual: nuevaFase })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        return;
                    }
                } catch(e) {
                    console.error('Error avanzando fase:', e);
                }
            }

            const o = appState.ordenes.find(x => x.id == id);
            if (o) {
                o.fase_actual = nuevaFase;
                if (nuevaFase === 'Distribución') o.estado = 'Completado';
                guardarEnLocalStorage();
                renderizarGridOrdenes();
            }
        }

        async function toggleAlertaOrden(id) {
            const o = appState.ordenes.find(x => x.id == id);
            if (!o) return;
            const nuevoEstado = o.estado === 'Detenido / Alerta' ? 'En Proceso' : 'Detenido / Alerta';

            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/registrar_produccion.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'cambiar_estado', id: id, estado: nuevoEstado })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        return;
                    }
                } catch(e) {
                    console.error('Error alternando estado:', e);
                }
            }

            o.estado = nuevoEstado;
            guardarEnLocalStorage();
            renderizarGridOrdenes();
        }

        // -------------------------------------------------------------
        // MÓDULO 6: BITÁCORA DE MERMAS Y RETRABAJOS
        // -------------------------------------------------------------
        function renderizarTablaMermas() {
            const tbody = document.getElementById('tabla-mermas-body');
            if (!tbody) return;

            let totalImpacto = 0;
            let mermasCount = 0;
            let retrabajosCount = 0;

            appState.mermas.forEach(m => {
                totalImpacto += parseFloat(m.costo_impacto || 0);
                if (m.tipo === 'Merma') mermasCount++;
                else retrabajosCount++;
            });

            document.getElementById('mermas-total-impacto').innerText = `$${totalImpacto.toLocaleString('es-MX', { minimumFractionDigits: 2 })} MXN`;
            document.getElementById('mermas-conteo-merma').innerText = `${mermasCount} eventos`;
            document.getElementById('mermas-conteo-retrabajo').innerText = `${retrabajosCount} eventos`;

            if (appState.mermas.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="p-6 text-center text-slate-400">Sin incidencias registradas en la bitácora.</td></tr>`;
                return;
            }

            let html = '';
            appState.mermas.forEach(m => {
                const badge = m.tipo === 'Merma'
                    ? '<span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 font-semibold text-[10px]">Merma</span>'
                    : '<span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-semibold text-[10px]">Retrabajo</span>';

                html += `
                    <tr class="hover:bg-slate-850">
                        <td class="p-3.5 font-mono text-slate-400">${m.fecha}</td>
                        <td class="p-3.5">${badge}</td>
                        <td class="p-3.5 font-semibold text-slate-200">${m.area}</td>
                        <td class="p-3.5 text-slate-300 max-w-xs">${m.descripcion_error}</td>
                        <td class="p-3.5 text-amber-300 font-medium">${m.material_desperdiciado}</td>
                        <td class="p-3.5 text-right font-bold text-rose-400 font-mono">$${parseFloat(m.costo_impacto).toFixed(2)}</td>
                        <td class="p-3.5 font-mono text-slate-400">${m.lote_afectado || 'N/A'}</td>
                        <td class="p-3.5 text-slate-400">${m.registrado_por}</td>
                        <td class="p-3.5 text-right">
                            <button onclick="eliminarMerma(${m.id})" class="p-1 rounded text-rose-400 hover:bg-rose-950/40">
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

            if (appState.modoBackend === 'mysql') {
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
                        Swal.fire({ icon: 'success', title: 'Incidencia Registrada', text: 'Guardado en la bitácora MySQL.', timer: 1500, showConfirmButton: false });
                        return;
                    }
                } catch(e) {
                    console.error('Error registrando merma en backend:', e);
                }
            }

            // Local
            payload.id = Date.now();
            appState.mermas.unshift(payload);
            guardarEnLocalStorage();
            cerrarModalMerma();
            renderizarTodo();
            Swal.fire({ icon: 'success', title: 'Incidencia Registrada', text: 'Guardada localmente.', timer: 1500, showConfirmButton: false });
        }

        async function eliminarMerma(id) {
            const conf = await Swal.fire({
                title: '¿Eliminar registro de merma?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                confirmButtonText: 'Sí, eliminar'
            });
            if (!conf.isConfirmed) return;

            if (appState.modoBackend === 'mysql') {
                try {
                    const res = await fetch('backend/control_mermas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'eliminar', id: id })
                    });
                    const json = await res.json();
                    if (json.success) {
                        sincronizarDatos();
                        return;
                    }
                } catch(e) {
                    console.error('Error eliminando merma en backend:', e);
                }
            }

            appState.mermas = appState.mermas.filter(m => m.id != id);
            guardarEnLocalStorage();
            renderizarTodo();
        }

        // -------------------------------------------------------------
        // MÓDULO 8: PLAN DE ACCIÓN Y REPORTE EJECUTIVO
        // -------------------------------------------------------------
        function actualizarResumenReporte() {
            const criticos = appState.productos.filter(p => parseInt(p.stock_actual) < parseInt(p.stock_minimo)).length;
            let totalMermas = 0;
            appState.mermas.forEach(m => totalMermas += parseFloat(m.costo_impacto || 0));

            const repSkus = document.getElementById('rep-skus-criticos');
            if (repSkus) repSkus.innerText = `${criticos} productos`;

            const repMermas = document.getElementById('rep-costo-mermas');
            if (repMermas) repMermas.innerText = `$${totalMermas.toLocaleString('es-MX', { minimumFractionDigits: 2 })} MXN`;

            const repFecha = document.getElementById('reporte-fecha');
            if (repFecha) {
                const hoy = new Date();
                repFecha.innerText = hoy.toLocaleDateString('es-MX', { day: 'numeric', month: 'long', year: 'numeric' });
            }
        }

        function generarPlanAccion() {
            actualizarResumenReporte();
            Swal.fire({
                icon: 'success',
                title: 'Plan de Acción Generado',
                text: 'Se han consolidado las métricas de mermas, órdenes y stock crítico en el reporte ejecutivo para impresión o descarga PDF.',
                confirmButtonColor: '#0284c7'
            });
        }

        // -------------------------------------------------------------
        // REINSTALACIÓN O SEMBRADO AUTOMÁTICO DE BASE DE DATOS
        // -------------------------------------------------------------
        async function instalarBaseDatos() {
            const conf = await Swal.fire({
                title: '¿Conectar / Inicializar Base de Datos MySQL?',
                text: 'Este proceso ejecutará el script backend/instalar_bd.php para crear la base de datos "pattern_care", tablas y datos semilla.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, inicializar MySQL',
                cancelButtonText: 'Cancelar'
            });
            if (!conf.isConfirmed) return;

            Swal.fire({
                title: 'Inicializando base de datos...',
                text: 'Conectando con el servidor MySQL local.',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {
                const res = await fetch('backend/instalar_bd.php', { method: 'POST' });
                const json = await res.json();
                if (json.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Base de Datos Lista!',
                        text: 'Tablas creadas y datos semilla cargados en MySQL.',
                        confirmButtonColor: '#0284c7'
                    });
                    sincronizarDatos();
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No se pudo conectar a MySQL',
                        html: `<p class="text-xs text-left">${json.message || 'Verifica que MySQL esté activo en el puerto 3306'}</p><p class="text-xs mt-2 text-slate-400">La aplicación seguirá operando perfectamente en modo Standalone LocalStorage.</p>`
                    });
                }
            } catch(e) {
                Swal.fire({
                    icon: 'info',
                    title: 'Modo Standalone / Local',
                    html: '<p class="text-xs">Estás ejecutando el archivo directamente o sin intérprete PHP. No te preocupes: todos los módulos y simuladores continúan funcionando con persistencia local.</p>'
                });
            }
        }

        // -------------------------------------------------------------
        // NAVEGACIÓN ENTRE TABS / MÓDULOS
        // -------------------------------------------------------------
        function cambiarModulo(moduloKey) {
            // Ocultar todos los módulos
            document.querySelectorAll('.modulo-seccion').forEach(sec => sec.classList.add('hidden'));

            // Desactivar botones de tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'text-slate-200', 'bg-slate-800', 'border', 'border-slate-700');
                btn.classList.add('text-slate-400');
            });

            // Mostrar el módulo destino
            const targetId = 'modulo-' + moduloKey;
            const targetSec = document.getElementById(targetId);
            if (targetSec) targetSec.classList.remove('hidden');

            // Activar botón correspondiente
            const activeBtn = document.querySelector(`.tab-btn[data-target="${targetId}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'text-slate-200', 'bg-slate-800', 'border', 'border-slate-700');
                activeBtn.classList.remove('text-slate-400');
            }

            // Si es dashboard o inventario, refrescar gráfica
            if (moduloKey === 'dashboard') {
                setTimeout(renderizarGraficaInventario, 50);
            }
        }
    </script>
</body>
</html>
