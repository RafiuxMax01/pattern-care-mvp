<?php
/**
 * PATTERN CARE - ENDPOINT: GESTIÓN DE INVENTARIO Y SKUS
 * Soporta operaciones CRUD para productos_sku con control de stock mínimo.
 */

require_once __DIR__ . '/../conexion.php';

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, null, 'Error de conexión con la base de datos.', 500);
}

// Obtener datos recibidos en JSON o POST convencional
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
            $stockActual = isset($data['stock_actual']) ? (int)$data['stock_actual'] : 0;
            $stockMinimo = isset($data['stock_minimo']) ? (int)$data['stock_minimo'] : 6;
            $notas = trim($data['notas'] ?? '');

            if (empty($sku) || empty($nombre) || empty($color)) {
                jsonResponse(false, null, 'SKU, Nombre y Color son campos obligatorios.', 400);
            }

            // Validar SKU duplicado
            $stmtCheck = $pdo->prepare("SELECT id FROM `productos_sku` WHERE `sku` = ?");
            $stmtCheck->execute([$sku]);
            if ($stmtCheck->fetch()) {
                jsonResponse(false, null, 'Ya existe un producto con el SKU: ' . $sku, 400);
            }

            $sql = "INSERT INTO `productos_sku` (`sku`, `nombre`, `categoria`, `color`, `talla`, `coleccion`, `stock_actual`, `stock_minimo`, `notas`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $categoria, $color, $talla, $coleccion, $stockActual, $stockMinimo, $notas]);
            $newId = $pdo->lastInsertId();

            jsonResponse(true, [
                'id' => $newId,
                'sku' => $sku,
                'stock_actual' => $stockActual,
                'alerta_minimo' => $stockActual < $stockMinimo
            ], 'Producto registrado exitosamente.');
            break;

        case 'actualizar':
            $id = (int)($data['id'] ?? 0);
            $sku = trim($data['sku'] ?? '');
            $nombre = trim($data['nombre'] ?? '');
            $categoria = trim($data['categoria'] ?? 'Gorros Quirúrgicos');
            $color = trim($data['color'] ?? '');
            $talla = trim($data['talla'] ?? 'Unitalla');
            $coleccion = trim($data['coleccion'] ?? '');
            $stockActual = isset($data['stock_actual']) ? (int)$data['stock_actual'] : 0;
            $stockMinimo = isset($data['stock_minimo']) ? (int)$data['stock_minimo'] : 6;
            $notas = trim($data['notas'] ?? '');

            if ($id <= 0 || empty($sku) || empty($nombre)) {
                jsonResponse(false, null, 'Identificador o campos clave faltantes.', 400);
            }

            // Validar SKU único en otro id
            $stmtCheck = $pdo->prepare("SELECT id FROM `productos_sku` WHERE `sku` = ? AND `id` != ?");
            $stmtCheck->execute([$sku, $id]);
            if ($stmtCheck->fetch()) {
                jsonResponse(false, null, 'El SKU ' . $sku . ' ya está asignado a otro producto.', 400);
            }

            $sql = "UPDATE `productos_sku` SET `sku`=?, `nombre`=?, `categoria`=?, `color`=?, `talla`=?, `coleccion`=?, `stock_actual`=?, `stock_minimo`=?, `notas`=? WHERE `id`=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $categoria, $color, $talla, $coleccion, $stockActual, $stockMinimo, $notas, $id]);

            jsonResponse(true, [
                'id' => $id,
                'sku' => $sku,
                'stock_actual' => $stockActual
            ], 'Producto actualizado exitosamente.');
            break;

        case 'ajustar_stock':
            $id = (int)($data['id'] ?? 0);
            $delta = isset($data['delta']) ? (int)$data['delta'] : 0;
            $nuevoStock = isset($data['nuevo_stock']) ? (int)$data['nuevo_stock'] : null;

            if ($id <= 0) {
                jsonResponse(false, null, 'ID de producto no válido.', 400);
            }

            if ($nuevoStock !== null) {
                if ($nuevoStock < 0) $nuevoStock = 0;
                $stmt = $pdo->prepare("UPDATE `productos_sku` SET `stock_actual` = ? WHERE `id` = ?");
                $stmt->execute([$nuevoStock, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE `productos_sku` SET `stock_actual` = GREATEST(0, stock_actual + ?) WHERE `id` = ?");
                $stmt->execute([$delta, $id]);
            }

            $stmtUpdated = $pdo->prepare("SELECT `id`, `sku`, `stock_actual`, `stock_minimo` FROM `productos_sku` WHERE `id` = ?");
            $stmtUpdated->execute([$id]);
            $updated = $stmtUpdated->fetch();

            jsonResponse(true, $updated, 'Stock actualizado correctamente.');
            break;

        case 'eliminar':
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'ID inválido para eliminación.', 400);
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
    jsonResponse(false, ['error' => $e->getMessage()], 'Error en la base de datos: ' . $e->getMessage(), 500);
}
