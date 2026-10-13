<?php
/**
 * DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
 * Endpoint: Obtener Datos Consolidados y Multi-Sucursal
 */

require_once __DIR__ . '/../conexion.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(false, [
        'db_connected' => false,
        'instructions' => 'No se pudo conectar a la base de datos "pattern_care". Verifica MySQL o ejecuta el instalador.'
    ], 'Error de conexión a MySQL.', 503);
}

try {
    $sucursalFiltro = isset($_GET['sucursal_id']) && is_numeric($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;

    // 1. Obtener lista de Sucursales
    $stmtSucursales = $pdo->query("SELECT * FROM `sucursales` WHERE `estado` = 'Activo' ORDER BY `id` ASC");
    $sucursales = $stmtSucursales->fetchAll();

    // 2. Obtener Productos y su desglose por sucursales
    $stmtProductos = $pdo->query("SELECT * FROM `productos_sku` ORDER BY `categoria` ASC, `nombre` ASC");
    $productos = $stmtProductos->fetchAll();

    // 3. Obtener existencias detalladas por sucursal
    $stmtInv = $pdo->query("
        SELECT inv.*, s.nombre AS sucursal_nombre, s.codigo AS sucursal_codigo, p.sku, p.nombre AS producto_nombre
        FROM `inventario_sucursales` inv
        INNER JOIN `sucursales` s ON inv.sucursal_id = s.id
        INNER JOIN `productos_sku` p ON inv.producto_id = p.id
        ORDER BY inv.producto_id ASC, inv.sucursal_id ASC
    ");
    $inventarioDetalle = $stmtInv->fetchAll();

    // Indexar existencias por producto_id
    $stockPorProductoYSucursal = [];
    foreach ($inventarioDetalle as $item) {
        $prodId = $item['producto_id'];
        $sucId = $item['sucursal_id'];
        if (!isset($stockPorProductoYSucursal[$prodId])) {
            $stockPorProductoYSucursal[$prodId] = [
                'total' => 0,
                'sucursales' => []
            ];
        }
        $stockPorProductoYSucursal[$prodId]['total'] += (int)$item['stock_actual'];
        $stockPorProductoYSucursal[$prodId]['sucursales'][$sucId] = [
            'sucursal_id' => $sucId,
            'sucursal_codigo' => $item['sucursal_codigo'],
            'sucursal_nombre' => $item['sucursal_nombre'],
            'stock_actual' => (int)$item['stock_actual'],
            'stock_minimo' => (int)$item['stock_minimo'],
            'es_critico' => (int)$item['stock_actual'] < (int)$item['stock_minimo'],
            'ubicacion_pasillo' => $item['ubicacion_pasillo']
        ];
    }

    // Enriquecer cada producto con su stock total y distribución por sucursal
    $skusCriticosTotales = 0;
    $totalPiezasGlobal = 0;

    foreach ($productos as &$prod) {
        $pId = $prod['id'];
        $dist = $stockPorProductoYSucursal[$pId] ?? ['total' => 0, 'sucursales' => []];
        $prod['stock_total'] = $dist['total'];
        $prod['desglose_sucursales'] = $dist['sucursales'];
        
        // Stock en la sucursal seleccionada (o total si no hay filtro)
        if ($sucursalFiltro !== null && isset($dist['sucursales'][$sucursalFiltro])) {
            $prod['stock_actual'] = $dist['sucursales'][$sucursalFiltro]['stock_actual'];
            $prod['stock_minimo'] = $dist['sucursales'][$sucursalFiltro]['stock_minimo'];
            $prod['es_critico'] = $prod['stock_actual'] < $prod['stock_minimo'];
        } else {
            $prod['stock_actual'] = $dist['total'];
            $prod['stock_minimo'] = (int)$prod['stock_minimo_general'];
            $prod['es_critico'] = $dist['total'] < (int)$prod['stock_minimo_general'];
        }

        if ($prod['es_critico']) {
            $skusCriticosTotales++;
        }
        $totalPiezasGlobal += (int)$prod['stock_actual'];
    }
    unset($prod);

    // 4. Obtener Transferencias de Stock Inter-Sucursal
    $stmtTrf = $pdo->query("
        SELECT trf.*, 
               so.nombre AS sucursal_origen, so.codigo AS origen_codigo,
               sd.nombre AS sucursal_destino, sd.codigo AS destino_codigo,
               p.sku, p.nombre AS producto_nombre
        FROM `transferencias_stock` trf
        INNER JOIN `sucursales` so ON trf.sucursal_origen_id = so.id
        INNER JOIN `sucursales` sd ON trf.sucursal_destino_id = sd.id
        INNER JOIN `productos_sku` p ON trf.producto_id = p.id
        ORDER BY trf.fecha_transferencia DESC
    ");
    $transferencias = $stmtTrf->fetchAll();

    // 5. Órdenes de Producción
    $sqlOrdenes = "SELECT o.*, s.nombre AS sucursal_nombre FROM `produccion_ordenes` o LEFT JOIN `sucursales` s ON o.sucursal_id = s.id ORDER BY o.fecha_inicio DESC";
    $stmtOrdenes = $pdo->query($sqlOrdenes);
    $ordenes = $stmtOrdenes->fetchAll();

    // 6. Mermas y Retrabajos
    $stmtMermas = $pdo->query("SELECT m.*, s.nombre AS sucursal_nombre FROM `mermas_retrabajos` m LEFT JOIN `sucursales` s ON m.sucursal_id = s.id ORDER BY m.fecha DESC, m.id DESC");
    $mermas = $stmtMermas->fetchAll();

    // 7. Rendimientos de Tela
    $stmtRendimientos = $pdo->query("SELECT * FROM `rendimiento_telas` ORDER BY `fecha` DESC LIMIT 20");
    $rendimientos = $stmtRendimientos->fetchAll();

    // 8. Resumen de Transferencias Activas
    $transferenciasEnTransito = 0;
    foreach ($transferencias as $t) {
        if ($t['estado'] === 'En Tránsito') {
            $transferenciasEnTransito++;
        }
    }

    // 9. Cálculo de Costo de Mermas
    $costoTotalMermas = 0;
    foreach ($mermas as $m) {
        $costoTotalMermas += (float)$m['costo_impacto'];
    }

    // Resumen de KPIs Business Central
    $kpis = [
        'sucursal_activa_id'       => $sucursalFiltro,
        'sucursales_conteo'        => count($sucursales),
        'personal_total'           => 6, // 1 Dueña, 1 Admin, 2 Corte, 2 Confección
        'productos_activos'        => count($productos),
        'stock_total_piezas'       => $totalPiezasGlobal,
        'skus_bajo_minimo'         => $skusCriticosTotales,
        'stock_minimo_norma'       => 6,
        'transferencias_transito'  => $transferenciasEnTransito,
        'total_transferencias'     => count($transferencias),
        'ordenes_activas'          => count(array_filter($ordenes, fn($o) => $o['estado'] === 'En Proceso')),
        'ordenes_alerta'           => count(array_filter($ordenes, fn($o) => $o['estado'] === 'Detenido / Alerta')),
        'costo_total_mermas'       => round($costoTotalMermas, 2)
    ];

    // Diagnósticos Operativos Críticos de Business Central
    $alertasCriticas = [
        [
            'area' => 'Logística Externa',
            'titulo' => 'Cuello de Botella: Taller de Impresión/Sublimado',
            'descripcion' => 'Dependencia de terceros sin SLA para gorros estampados. Retrasos de 48-72h impactan las entregas hospitalarias.',
            'severidad' => 'alta'
        ],
        [
            'area' => 'Abastecimiento Multi-Sucursal',
            'titulo' => 'Riesgo de Desabasto en Sucursal Hospital General',
            'descripcion' => 'Stock de Gorros Azules (2 pzas) y Chamarras (2 pzas) en punto de venta crítico por debajo de la norma de 6 piezas.',
            'severidad' => 'critica'
        ],
        [
            'area' => 'Infraestructura Tecnológica',
            'titulo' => 'Ausencia de Respaldos en la Nube',
            'descripcion' => 'Vulnerabilidad ante pérdida de datos en equipos locales sin respaldo automatizado centralizado.',
            'severidad' => 'critica'
        ],
        [
            'area' => 'Control de Calidad',
            'titulo' => 'Merma Oculta en Confección y Empaque',
            'descripcion' => 'Pérdidas acumuladas por desajustes mecánicos y retrazado de tela CRO no costeadas formalmente.',
            'severidad' => 'media'
        ]
    ];

    jsonResponse(true, [
        'db_connected'     => true,
        'sucursal_filtro'  => $sucursalFiltro,
        'sucursales'       => $sucursales,
        'kpis'             => $kpis,
        'productos'        => $productos,
        'inventario_detalle'=> $inventarioDetalle,
        'transferencias'   => $transferencias,
        'ordenes'          => $ordenes,
        'mermas'           => $mermas,
        'rendimientos'     => $rendimientos,
        'alertas_criticas' => $alertasCriticas
    ], 'Datos de Business Central ERP cargados exitosamente.');

} catch (PDOException $e) {
    jsonResponse(false, ['error' => $e->getMessage()], 'Error al consultar la base de datos: ' . $e->getMessage(), 500);
}
