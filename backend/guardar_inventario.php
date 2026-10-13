<?php
/**
 * DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
 * Endpoint: Catálogo de Productos e Inventario por Sucursal
 */

require_once __DIR__ . '/../conexion.php';

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, null, 'Error de conexión con la base de datos.', 500);
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);
if (!$data) {
    $data = $_POST;
}

$action = $data['action'] ?? 'crear';

try {
    switch ($action) {
        case 'crear':
            $sku = trim($data['sku'] ?? '');
            $nombre = trim($data['nombre'] ?? '');
            $categoria = trim($data['categoria'] ?? 'Gorros Quirúrgicos');
            $color = trim($data['color'] ?? '');
            $talla = trim($data['talla'] ?? 'Unitalla');
            $coleccion = trim($data['coleccion'] ?? '');
            $stockMinimo = isset($data['stock_minimo']) ? (int)$data['stock_minimo'] : 6;
            $stockInicial = isset($data['stock_actual']) ? (int)$data['stock_actual'] : 0;
            $sucursalId = isset($data['sucursal_id']) && (int)$data['sucursal_id'] > 0 ? (int)$data['sucursal_id'] : 1; // Default Matriz
            $precioUnitario = isset($data['precio_unitario']) ? (float)$data['precio_unitario'] : 0.0;
            $notas = trim($data['notas'] ?? '');

            if (empty($sku) || empty($nombre) || empty($color)) {
                jsonResponse(false, null, 'SKU, Nombre y Color son obligatorios.', 400);
            }

            // Validar SKU duplicado
            $stmtCheck = $pdo->prepare("SELECT id FROM `productos_sku` WHERE `sku` = ?");
            $stmtCheck->execute([$sku]);
            if ($stmtCheck->fetch()) {
                jsonResponse(false, null, 'El código SKU ' . $sku . ' ya existe en el catálogo.', 400);
            }

            $pdo->beginTransaction();

            $sql = "INSERT INTO `productos_sku` (`sku`, `nombre`, `categoria`, `color`, `talla`, `coleccion`, `stock_minimo_general`, `precio_unitario`, `notas`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $categoria, $color, $talla, $coleccion, $stockMinimo, $precioUnitario, $notas]);
            $newId = $pdo->lastInsertId();

            // Asignar stock en inventario_sucursales para todas las sucursales existentes (con stockInicial en la sucursal elegida)
            $stmtSucs = $pdo->query("SELECT id FROM `sucursales`");
            $sucursales = $stmtSucs->fetchAll();

            $stmtInv = $pdo->prepare("INSERT INTO `inventario_sucursales` (`producto_id`, `sucursal_id`, `stock_actual`, `stock_minimo`) VALUES (?, ?, ?, ?)");
            foreach ($sucursales as $s) {
                $qty = ($s['id'] == $sucursalId) ? $stockInicial : 0;
                $stmtInv->execute([$newId, $s['id'], $qty, $stockMinimo]);
            }

            $pdo->commit();

            jsonResponse(true, [
                'id' => $newId,
                'sku' => $sku,
                'stock_actual' => $stockInicial
            ], 'Producto registrado e inicializado en todas las sucursales de Business Central.');
            break;

        case 'actualizar':
            $id = (int)($data['id'] ?? 0);
            $sku = trim($data['sku'] ?? '');
            $nombre = trim($data['nombre'] ?? '');
            $categoria = trim($data['categoria'] ?? 'Gorros Quirúrgicos');
            $color = trim($data['color'] ?? '');
            $talla = trim($data['talla'] ?? 'Unitalla');
            $coleccion = trim($data['coleccion'] ?? '');
            $stockMinimo = isset($data['stock_minimo']) ? (int)$data['stock_minimo'] : 6;
            $precioUnitario = isset($data['precio_unitario']) ? (float)$data['precio_unitario'] : 0.0;
            $notas = trim($data['notas'] ?? '');

            if ($id <= 0 || empty($sku) || empty($nombre)) {
                jsonResponse(false, null, 'Identificador o campos requeridos faltantes.', 400);
            }

            $sql = "UPDATE `productos_sku` SET `sku`=?, `nombre`=?, `categoria`=?, `color`=?, `talla`=?, `coleccion`=?, `stock_minimo_general`=?, `precio_unitario`=?, `notas`=? WHERE `id`=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $categoria, $color, $talla, $coleccion, $stockMinimo, $precioUnitario, $notas, $id]);

            jsonResponse(true, ['id' => $id, 'sku' => $sku], 'Ficha de producto actualizada en Business Central.');
            break;

        case 'ajustar_stock':
            $productoId = (int)($data['id'] ?? 0);
            $sucursalId = (int)($data['sucursal_id'] ?? 1); // Por defecto Matriz
            $delta = isset($data['delta']) ? (int)$data['delta'] : 0;

            if ($productoId <= 0 || $sucursalId <= 0) {
                jsonResponse(false, null, 'Parámetros de producto o sucursal inválidos.', 400);
            }

            // Verificar si existe registro en inventario_sucursales
            $stmtCheck = $pdo->prepare("SELECT id, stock_actual FROM `inventario_sucursales` WHERE `producto_id` = ? AND `sucursal_id` = ?");
            $stmtCheck->execute([$productoId, $sucursalId]);
            $invRow = $stmtCheck->fetch();

            if ($invRow) {
                $stmtUpdate = $pdo->prepare("UPDATE `inventario_sucursales` SET `stock_actual` = GREATEST(0, stock_actual + ?) WHERE `producto_id` = ? AND `sucursal_id` = ?");
                $stmtUpdate->execute([$delta, $productoId, $sucursalId]);
            } else {
                $nuevoStock = max(0, $delta);
                $stmtInsert = $pdo->prepare("INSERT INTO `inventario_sucursales` (`producto_id`, `sucursal_id`, `stock_actual`, `stock_minimo`) VALUES (?, ?, ?, 6)");
                $stmtInsert->execute([$productoId, $sucursalId, $nuevoStock]);
            }

            // Consultar nuevo stock de la sucursal
            $stmtGet = $pdo->prepare("SELECT stock_actual, stock_minimo FROM `inventario_sucursales` WHERE `producto_id` = ? AND `sucursal_id` = ?");
            $stmtGet->execute([$productoId, $sucursalId]);
            $res = $stmtGet->fetch();

            jsonResponse(true, [
                'producto_id' => $productoId,
                'sucursal_id' => $sucursalId,
                'stock_actual' => (int)$res['stock_actual'],
                'stock_minimo' => (int)$res['stock_minimo']
            ], 'Stock de la sucursal ajustado.');
            break;

        case 'eliminar':
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'ID inválido.', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM `productos_sku` WHERE `id` = ?");
            $stmt->execute([$id]);

            jsonResponse(true, ['id' => $id], 'Producto eliminado del catálogo.');
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
