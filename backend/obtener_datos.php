<?php
/**
 * PATTERN CARE - ENDPOINT: OBTENER DATOS CONSOLIDADOS
 * Devuelve KPIs ejecutivos, lista de inventario, órdenes activas, mermas y alertas.
 */

require_once __DIR__ . '/../conexion.php';

$pdo = getDBConnection();

if (!$pdo) {
    // Retornamos un flag claro para que el frontend pueda activar modo demostración o guiar al usuario
    jsonResponse(false, [
        'db_connected' => false,
        'instructions' => 'No se pudo conectar a la base de datos "pattern_care". Verifica MySQL o ejecuta el instalador.'
    ], 'Error de conexión a MySQL.', 503);
}

try {
    // 1. Obtener SKUs de productos
    $stmtProductos = $pdo->query("SELECT * FROM `productos_sku` ORDER BY `stock_actual` ASC, `categoria` ASC");
    $productos = $stmtProductos->fetchAll();

    // 2. Obtener Órdenes de producción
    $stmtOrdenes = $pdo->query("SELECT * FROM `produccion_ordenes` ORDER BY `fecha_inicio` DESC");
    $ordenes = $stmtOrdenes->fetchAll();

    // 3. Obtener Bitácora de mermas y retrabajos
    $stmtMermas = $pdo->query("SELECT * FROM `mermas_retrabajos` ORDER BY `fecha` DESC, `id` DESC");
    $mermas = $stmtMermas->fetchAll();

    // 4. Obtener Rendimientos de tela
    $stmtRendimientos = $pdo->query("SELECT * FROM `rendimiento_telas` ORDER BY `fecha` DESC LIMIT 20");
    $rendimientos = $stmtRendimientos->fetchAll();

    // 5. Cálculos de KPIs
    $totalSkus = count($productos);
    $skusCriticos = 0;
    $totalPiezasStock = 0;
    $categoriasConteo = [];

    foreach ($productos as $p) {
        $totalPiezasStock += (int)$p['stock_actual'];
        if ((int)$p['stock_actual'] < (int)$p['stock_minimo']) {
            $skusCriticos++;
        }
        $cat = $p['categoria'];
        $categoriasConteo[$cat] = ($categoriasConteo[$cat] ?? 0) + (int)$p['stock_actual'];
    }

    $ordenesEnProceso = 0;
    $ordenesDetenidas = 0;
    foreach ($ordenes as $o) {
        if ($o['estado'] === 'Detenido / Alerta') {
            $ordenesDetenidas++;
        } elseif ($o['estado'] === 'En Proceso') {
            $ordenesEnProceso++;
        }
    }

    $costoTotalMermas = 0;
    $conteoMermas = 0;
    $conteoRetrabajos = 0;
    foreach ($mermas as $m) {
        $costoTotalMermas += (float)$m['costo_impacto'];
        if ($m['tipo'] === 'Merma') {
            $conteoMermas++;
        } else {
            $conteoRetrabajos++;
        }
    }

    $kpis = [
        'personal_total'      => 6, // 1 Dueña/Encargada, 1 Admin, 2 Corte y Calidad, 2 Confección
        'productos_activos'   => $totalSkus,
        'stock_total_piezas'  => $totalPiezasStock,
        'stock_minimo_norma'  => 6,
        'skus_bajo_minimo'    => $skusCriticos,
        'ordenes_activas'     => $ordenesEnProceso,
        'ordenes_alerta'      => $ordenesDetenidas,
        'costo_total_mermas'  => round($costoTotalMermas, 2),
        'total_mermas_reg'    => $conteoMermas,
        'total_retrabajos_reg'=> $conteoRetrabajos,
        'distribucion_stock'  => $categoriasConteo
    ];

    // Diagnósticos Operativos Críticos activos
    $alertasCriticas = [
        [
            'area' => 'Logística Externa',
            'titulo' => 'Cuello de Botella: Taller de Impresión/Sublimado',
            'descripcion' => 'Dependencia externa sin SLA para gorros estampados. Causa retrasos directos en la entrega.',
            'severidad' => 'alta'
        ],
        [
            'area' => 'Inventario de Materias Primas',
            'titulo' => 'Riesgo de Desabasto en Chamarras y Cubrepolvos',
            'descripcion' => 'Falta de tela impermeable y cierres para mantener stock regulatorio de 6 piezas mínimas.',
            'severidad' => 'critica'
        ],
        [
            'area' => 'Infraestructura Tecnológica',
            'titulo' => 'Ausencia de Respaldos en la Nube',
            'descripcion' => 'Vulnerabilidad ante fallas en hardware local. Riesgo latente de pérdida de registros de clientes e inventario.',
            'severidad' => 'critica'
        ],
        [
            'area' => 'Control de Calidad',
            'titulo' => 'Merma Oculta y Retrabajos sin Estandarizar',
            'descripcion' => 'Pérdidas acumuladas por costuras desajustadas y errores en empaquetado no registradas en línea formal.',
            'severidad' => 'media'
        ]
    ];

    jsonResponse(true, [
        'db_connected'     => true,
        'kpis'             => $kpis,
        'productos'        => $productos,
        'ordenes'          => $ordenes,
        'mermas'           => $mermas,
        'rendimientos'     => $rendimientos,
        'alertas_criticas' => $alertasCriticas
    ], 'Datos operativos cargados exitosamente.');

} catch (PDOException $e) {
    jsonResponse(false, [
        'error' => $e->getMessage()
    ], 'Error al consultar la base de datos: ' . $e->getMessage(), 500);
}
