<?php
/**
 * DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
 * Endpoint: Catálogo de Productos e Inventario por Sucursal
 */

require_once __DIR__ . '/bootstrap_endpoint.php';

// Autorización por acción (SCRUM-20)
[$pdo, $data, $action, $perfil] = arrancarEndpointJson([
    'crear'        => 'inventario.editar',
    'actualizar'   => 'inventario.editar',
    'eliminar'     => 'inventario.editar',
    'ajustar_stock'=> 'inventario.ajustar'
], 'inventario.ver');

try {
    switch ($action) {
        // El esquema actual (v2) guarda cada SKU como variante de un modelo
        // (products + product_variants). Los modelos y sus variantes se crean y
        // editan en el catálogo (gestionar_productos.php), que asigna el número de
        // pieza de la secuencia. Estas dos acciones escribían en `productos_sku`,
        // una tabla del esquema v1 que ya no existe, y nunca funcionaron con MySQL.
        case 'crear':
        case 'actualizar':
            jsonResponse(false, null, 'Los modelos y sus variantes se dan de alta y se editan en Catálogo de Productos.', 410);
            break;

        case 'ajustar_stock':
            // `id` es el id de la variante (los SKUs de obtener_datos.php)
            $variantId  = (int)($data['id'] ?? 0);
            $sucursalId = (int)($data['sucursal_id'] ?? 1); // Por defecto Matriz
            $delta = isset($data['delta']) ? (int)$data['delta'] : 0;

            if ($variantId <= 0 || $sucursalId <= 0) {
                jsonResponse(false, null, 'Parámetros de producto o sucursal inválidos.', 400);
            }

            // Evita descontar existencias de un punto de venta que no es el suyo
            requerirSucursal($sucursalId, 'ajustar');

            $stmtVar = $pdo->prepare("SELECT id FROM `product_variants` WHERE `id` = ? AND `activo` = 1");
            $stmtVar->execute([$variantId]);
            if (!$stmtVar->fetch()) {
                jsonResponse(false, null, 'La variante no existe o está desactivada.', 404);
            }

            // Verificar si existe registro en inventario_sucursales
            $stmtCheck = $pdo->prepare("SELECT id FROM `inventario_sucursales` WHERE `variant_id` = ? AND `sucursal_id` = ?");
            $stmtCheck->execute([$variantId, $sucursalId]);

            if ($stmtCheck->fetch()) {
                $stmtUpdate = $pdo->prepare("UPDATE `inventario_sucursales` SET `stock_actual` = GREATEST(0, stock_actual + ?) WHERE `variant_id` = ? AND `sucursal_id` = ?");
                $stmtUpdate->execute([$delta, $variantId, $sucursalId]);
            } else {
                $stmtInsert = $pdo->prepare("INSERT INTO `inventario_sucursales` (`variant_id`, `sucursal_id`, `stock_actual`, `stock_minimo`) VALUES (?, ?, ?, 6)");
                $stmtInsert->execute([$variantId, $sucursalId, max(0, $delta)]);
            }

            // Consultar nuevo stock de la sucursal
            $stmtGet = $pdo->prepare("SELECT stock_actual, stock_minimo FROM `inventario_sucursales` WHERE `variant_id` = ? AND `sucursal_id` = ?");
            $stmtGet->execute([$variantId, $sucursalId]);
            $res = $stmtGet->fetch();

            jsonResponse(true, [
                'variant_id'   => $variantId,
                'sucursal_id'  => $sucursalId,
                'stock_actual' => (int)$res['stock_actual'],
                'stock_minimo' => (int)$res['stock_minimo']
            ], 'Stock de la sucursal ajustado.');
            break;

        case 'eliminar':
            // Baja lógica de la variante, como hace el catálogo: el inventario y
            // las transferencias que la referencian se conservan.
            $variantId = (int)($data['id'] ?? 0);
            if ($variantId <= 0) {
                jsonResponse(false, null, 'ID inválido.', 400);
            }

            $stmt = $pdo->prepare("UPDATE `product_variants` SET `activo` = 0 WHERE `id` = ? AND `activo` = 1");
            $stmt->execute([$variantId]);
            if ($stmt->rowCount() === 0) {
                jsonResponse(false, null, 'La variante no existe o ya estaba desactivada.', 404);
            }

            jsonResponse(true, ['id' => $variantId], 'Variante desactivada del inventario.');
            break;

        default:
            jsonResponse(false, null, 'Acción no reconocida.', 400);
            break;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, ['error' => $e->getMessage()], 'Error en la base de datos: ' . $e->getMessage(), 500);
}
