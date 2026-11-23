<?php
/**
 * DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
 * Endpoint: Obtener Datos Consolidados y Multi-Sucursal
 * Incluye catálogo de modelos (products), variantes (product_variants) e inventario por sucursal.
 */

require_once __DIR__ . '/middleware_auth.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(false, [
        'db_connected' => false,
        'instructions' => 'No se pudo conectar a la base de datos "pattern_care". Verifica MySQL o ejecuta el instalador.'
    ], 'Error de conexión a MySQL.', 503);
}

// Control de accesos (SCRUM-20): consultar el ERP exige sesión iniciada
$perfil = requerirAutenticacion();

try {
    $sucursalSolicitada = isset($_GET['sucursal_id']) && is_numeric($_GET['sucursal_id']) ? (int)$_GET['sucursal_id'] : null;

    // El usuario solo consulta los puntos de venta que tiene asignados.
    // Si no pide ninguno, se le devuelve el primero de su lista (nunca el consolidado ajeno).
    $sucursalFiltro = resolverSucursalConsulta($perfil, $sucursalSolicitada);
    $sucursalesPermitidas = sucursalesPermitidasIds($perfil);

    // 1. Obtener lista de Sucursales
    $stmtSucursales = $pdo->query("SELECT * FROM `sucursales` WHERE `estado` = 'Activo' ORDER BY `id` ASC");
    $sucursales = $stmtSucursales->fetchAll();

    // Se recorta el catálogo de sucursales a las asignadas al usuario
    if ($sucursalesPermitidas !== null) {
        $sucursales = array_values(array_filter($sucursales, function ($s) use ($sucursalesPermitidas) {
            return in_array((int)$s['id'], $sucursalesPermitidas, true);
        }));
    }

    // 2. Obtener Productos (modelos base) con conteo de variantes
    $stmtProductos = $pdo->query("
        SELECT
            p.*,
            COUNT(DISTINCT pv.id)                                AS total_variantes,
            SUM(CASE WHEN pv.activo = 1 THEN 1 ELSE 0 END)      AS variantes_activas
        FROM products p
        LEFT JOIN product_variants pv ON pv.product_id = p.id
        WHERE p.activo = 1
        GROUP BY p.id
        ORDER BY p.categoria, p.nombre
    ");
    $productos = $stmtProductos->fetchAll();

    // Para cada producto, obtener sus variantes activas
    foreach ($productos as &$prod) {
        $stmtV = $pdo->prepare("
            SELECT pv.*, 
                   COALESCE(SUM(inv.stock_actual), 0) AS stock_total_global
            FROM   product_variants pv
            LEFT JOIN inventario_sucursales inv ON inv.variant_id = pv.id
            WHERE  pv.product_id = ? AND pv.activo = 1
            GROUP BY pv.id
            ORDER BY pv.color, pv.talla
        ");
        $stmtV->execute([$prod['id']]);
        $prod['variantes'] = $stmtV->fetchAll();

        // Para cada variante, obtener desglose por sucursal
        foreach ($prod['variantes'] as &$variante) {
            $stmtS = $pdo->prepare("
                SELECT inv.*, s.nombre AS sucursal_nombre, s.codigo AS sucursal_codigo
                FROM inventario_sucursales inv
                INNER JOIN sucursales s ON inv.sucursal_id = s.id
                WHERE inv.variant_id = ?
                ORDER BY s.id
            ");
            $stmtS->execute([$variante['id']]);
            $variante['sucursales'] = $stmtS->fetchAll();

            // Stock total y crítico de la variante
            $total = 0;
            $criticoEnAlgunaSucursal = false;
            foreach ($variante['sucursales'] as $sucData) {
                $total += (int)$sucData['stock_actual'];
                if ((int)$sucData['stock_actual'] < (int)$sucData['stock_minimo']) {
                    $criticoEnAlgunaSucursal = true;
                }
            }
            $variante['stock_total'] = $total;
            $variante['es_critico'] = $criticoEnAlgunaSucursal;
            $variante['stock_minimo_general'] = (int)$prod['stock_minimo'];
        }
        unset($variante);
    }
    unset($prod);

    // 3. Construir lista plana de SKUs para el módulo de inventario (compatibilidad)
    $skusPlanos = [];
    foreach ($productos as $prod) {
        foreach ($prod['variantes'] as $v) {
            $skusPlanos[] = [
                'id'                 => $v['id'],
                'producto_id'        => $prod['id'],
                'sku'                => $v['sku'],
                'numero_pieza'       => $prod['numero_pieza'],
                'nombre'             => $prod['nombre'],
                'categoria'          => $prod['categoria'],
                'color'              => $v['color'],
                'talla'              => $v['talla'],
                'coleccion'          => $prod['coleccion'],
                'stock_minimo_general' => $prod['stock_minimo'],
                'precio_unitario'    => $v['precio_variante'] ?? $prod['precio_base'],
                'stock_total'        => $v['stock_total'],
                'es_critico'         => $v['es_critico'],
                'desglose_sucursales'=> $v['sucursales'],
                'foto_url'           => $prod['foto_url'],
                'tipo_tela'          => $prod['tipo_tela'],
                'notas'              => $prod['notas']
            ];
        }
    }
    // 3.1 Construir el detalle de inventario para el frontend
 $inventarioDetalle = [];

foreach ($skusPlanos as $sku) {
    foreach ($sku['desglose_sucursales'] as $sucursal) {
        $inventarioDetalle[] = [
            'producto_id'  => (int) $sku['id'], // ID de la variante/SKU
            'variant_id'   => (int) $sku['id'],
            'sucursal_id'  => (int) $sucursal['sucursal_id'],
            'stock_actual' => (int) $sucursal['stock_actual'],
            'stock_minimo' => (int) $sucursal['stock_minimo']
        ];
    }
}

    // 4. Calcular stock crítico total (SKUs con stock < mínimo en al menos una sucursal o global)
    $skusCriticosTotales = 0;
    $totalPiezasGlobal = 0;
    foreach ($skusPlanos as $sku) {
        $totalPiezasGlobal += (int)$sku['stock_total'];
        if ($sku['es_critico']) {
            $skusCriticosTotales++;
        }
    }

    // 5. Obtener Transferencias de Stock Inter-Sucursal
    $stmtTrf = $pdo->query("
        SELECT trf.*, 
               so.nombre AS sucursal_origen, so.codigo AS origen_codigo,
               sd.nombre AS sucursal_destino, sd.codigo AS destino_codigo,
               v.sku, p.nombre AS producto_nombre, v.color, v.talla
        FROM transferencias_stock trf
        INNER JOIN sucursales so ON trf.sucursal_origen_id = so.id
        INNER JOIN sucursales sd ON trf.sucursal_destino_id = sd.id
        INNER JOIN product_variants v ON trf.variant_id = v.id
        INNER JOIN products p ON v.product_id = p.id
        ORDER BY trf.fecha_transferencia DESC
    ");
    $transferencias = $stmtTrf->fetchAll();

    $transferenciasEnTransito = 0;
    foreach ($transferencias as $t) {
        if ($t['estado'] === 'En Tránsito') {
            $transferenciasEnTransito++;
        }
    }

    // 6. Órdenes de Producción
    $sqlOrdenes = "SELECT o.*, s.nombre AS sucursal_nombre FROM `produccion_ordenes` o LEFT JOIN `sucursales` s ON o.sucursal_id = s.id ORDER BY o.fecha_inicio DESC";
    $stmtOrdenes = $pdo->query($sqlOrdenes);
    $ordenes = $stmtOrdenes->fetchAll();

    // 7. Mermas y Retrabajos
    $stmtMermas = $pdo->query("SELECT m.*, s.nombre AS sucursal_nombre FROM `mermas_retrabajos` m LEFT JOIN `sucursales` s ON m.sucursal_id = s.id ORDER BY m.fecha DESC, m.id DESC");
    $mermas = $stmtMermas->fetchAll();

    $costoTotalMermas = 0;
    foreach ($mermas as $m) {
        $costoTotalMermas += (float)$m['costo_impacto'];
    }

    // 8. Rendimientos de Tela
    $stmtRendimientos = $pdo->query("SELECT * FROM `rendimiento_telas` ORDER BY `fecha` DESC LIMIT 20");
    $rendimientos = $stmtRendimientos->fetchAll();

    // 9. KPIs Business Central
    $kpis = [
        'sucursal_activa_id'       => $sucursalFiltro,
        'sucursales_conteo'        => count($sucursales),
        'personal_total'           => 6, // 1 Dueña, 1 Admin, 2 Corte, 2 Confección
        'productos_activos'        => count($productos),
        'skus_activos'             => count($skusPlanos),
        'stock_total_piezas'       => $totalPiezasGlobal,
        'skus_bajo_minimo'         => $skusCriticosTotales,
        'stock_minimo_norma'       => 6,
        'transferencias_transito'  => $transferenciasEnTransito,
        'total_transferencias'     => count($transferencias),
        'ordenes_activas'          => count(array_filter($ordenes, fn($o) => $o['estado'] === 'En Proceso')),
        'ordenes_alerta'           => count(array_filter($ordenes, fn($o) => $o['estado'] === 'Detenido / Alerta')),
        'costo_total_mermas'       => round($costoTotalMermas, 2)
    ];

    // 10. Diagnósticos Operativos Críticos
    $alertasCriticas = [
        [
            'area'     => 'Logística Externa',
            'titulo'   => 'Cuello de Botella: Taller de Impresión/Sublimado',
            'descripcion' => 'Dependencia de terceros sin SLA para gorros estampados. Retrasos de 48-72h impactan las entregas hospitalarias.',
            'severidad'=> 'alta'
        ],
        [
            'area'     => 'Abastecimiento Multi-Sucursal',
            'titulo'   => 'Riesgo de Desabasto en Sucursal Hospital General',
            'descripcion' => 'Stock de variantes críticas en punto de venta por debajo de la norma de 6 piezas.',
            'severidad'=> 'critica'
        ],
        [
            'area'     => 'Infraestructura Tecnológica',
            'titulo'   => 'Ausencia de Respaldos en la Nube',
            'descripcion' => 'Vulnerabilidad ante pérdida de datos en equipos locales sin respaldo automatizado centralizado.',
            'severidad'=> 'critica'
        ],
        [
            'area'     => 'Control de Calidad',
            'titulo'   => 'Merma Oculta en Confección y Empaque',
            'descripcion' => 'Pérdidas acumuladas por desajustes mecánicos y retrazado de tela CRO no costeadas formalmente.',
            'severidad'=> 'media'
        ]
    ];

    jsonResponse(true, [
        'db_connected'     => true,
        'sesion'           => $perfil,
        'sucursal_filtro'  => $sucursalFiltro,
        'sucursales'       => $sucursales,
        'kpis'             => $kpis,
        'productos'        => $productos,
        'skus_planos'      => $skusPlanos,
        'inventario_detalle' => $inventarioDetalle,
        'transferencias'   => $transferencias,
        'ordenes'          => $ordenes,
        'mermas'           => $mermas,
        'rendimientos'     => $rendimientos,
        'alertas_criticas' => $alertasCriticas
    ], 'Datos de Business Central ERP cargados exitosamente.');

} catch (PDOException $e) {
    jsonResponse(false, ['error' => $e->getMessage()], 'Error al consultar la base de datos: ' . $e->getMessage(), 500);
}
