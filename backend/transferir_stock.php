<?php
/**
 * DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
 * Endpoint: Órdenes de Transferencia / Traspasos Inter-Sucursal
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
        case 'transferir':
            $origenId = (int)($data['sucursal_origen_id'] ?? 0);
            $destinoId = (int)($data['sucursal_destino_id'] ?? 0);
            $productoId = (int)($data['producto_id'] ?? 0);
            $cantidad = (int)($data['cantidad'] ?? 0);
            $responsable = trim($data['responsable'] ?? 'Área Administrativa');
            $notas = trim($data['notas'] ?? '');
            $estado = in_array($data['estado'] ?? '', ['Pendiente', 'En Tránsito', 'Recibido']) ? $data['estado'] : 'Recibido';

            if ($origenId <= 0 || $destinoId <= 0 || $productoId <= 0 || $cantidad <= 0) {
                jsonResponse(false, null, 'Todos los campos de la transferencia son obligatorios y la cantidad debe ser mayor a 0.', 400);
            }

            if ($origenId === $destinoId) {
                jsonResponse(false, null, 'La sucursal de origen no puede ser igual a la sucursal de destino.', 400);
            }

            // Iniciar transacción atómica en MySQL
            $pdo->beginTransaction();

            // 1. Verificar stock suficiente en sucursal de origen
            $stmtStockOrigen = $pdo->prepare("SELECT stock_actual, stock_minimo FROM `inventario_sucursales` WHERE `producto_id` = ? AND `sucursal_id` = ? FOR UPDATE");
            $stmtStockOrigen->execute([$productoId, $origenId]);
            $stockOrigen = $stmtStockOrigen->fetch();

            $disponibleOrigen = $stockOrigen ? (int)$stockOrigen['stock_actual'] : 0;
            if ($disponibleOrigen < $cantidad) {
                $pdo->rollBack();
                jsonResponse(false, null, "Stock insuficiente en la sucursal de origen. Disponible: {$disponibleOrigen} piezas, solicitadas: {$cantidad}.", 400);
            }

            // 2. Descontar stock de origen
            $stmtDeduct = $pdo->prepare("UPDATE `inventario_sucursales` SET `stock_actual` = `stock_actual` - ? WHERE `producto_id` = ? AND `sucursal_id` = ?");
            $stmtDeduct->execute([$cantidad, $productoId, $origenId]);

            // 3. Aumentar stock en destino (si ya se marcó como 'Recibido' o dejar en tránsito)
            if ($estado === 'Recibido') {
                $stmtCheckDest = $pdo->prepare("SELECT id FROM `inventario_sucursales` WHERE `producto_id` = ? AND `sucursal_id` = ?");
                $stmtCheckDest->execute([$productoId, $destinoId]);
                if ($stmtCheckDest->fetch()) {
                    $stmtAdd = $pdo->prepare("UPDATE `inventario_sucursales` SET `stock_actual` = `stock_actual` + ? WHERE `producto_id` = ? AND `sucursal_id` = ?");
                    $stmtAdd->execute([$cantidad, $productoId, $destinoId]);
                } else {
                    $stmtInsertDest = $pdo->prepare("INSERT INTO `inventario_sucursales` (`producto_id`, `sucursal_id`, `stock_actual`, `stock_minimo`) VALUES (?, ?, ?, 6)");
                    $stmtInsertDest->execute([$productoId, $destinoId, $cantidad]);
                }
            }

            // 4. Registrar la orden de transferencia
            $codigoTrf = 'TRF-' . date('Y') . '-' . str_pad(rand(10, 999), 3, '0', STR_PAD_LEFT);
            $stmtTrf = $pdo->prepare("
                INSERT INTO `transferencias_stock` (`codigo_transferencia`, `sucursal_origen_id`, `sucursal_destino_id`, `producto_id`, `cantidad`, `estado`, `responsable`, `notas`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtTrf->execute([$codigoTrf, $origenId, $destinoId, $productoId, $cantidad, $estado, $responsable, $notas]);
            $newTrfId = $pdo->lastInsertId();

            $pdo->commit();

            jsonResponse(true, [
                'id' => $newTrfId,
                'codigo_transferencia' => $codigoTrf,
                'cantidad' => $cantidad,
                'estado' => $estado
            ], 'Transferencia entre sucursales procesada correctamente.');
            break;

        case 'cambiar_estado':
            $id = (int)($data['id'] ?? 0);
            $nuevoEstado = trim($data['estado'] ?? '');

            if ($id <= 0 || !in_array($nuevoEstado, ['Pendiente', 'En Tránsito', 'Recibido', 'Cancelado'])) {
                jsonResponse(false, null, 'Estado inválido.', 400);
            }

            $stmt = $pdo->prepare("UPDATE `transferencias_stock` SET `estado` = ? WHERE `id` = ?");
            $stmt->execute([$nuevoEstado, $id]);

            jsonResponse(true, ['id' => $id, 'estado' => $nuevoEstado], 'Estado de transferencia actualizado.');
            break;

        default:
            jsonResponse(false, null, 'Acción no permitida.', 400);
            break;
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    jsonResponse(false, ['error' => $e->getMessage()], 'Error al procesar transferencia: ' . $e->getMessage(), 500);
}
