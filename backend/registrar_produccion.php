<?php
/**
 * PATTERN CARE - ENDPOINT: GESTIÓN DE PRODUCCIÓN
 * Control de fases, órdenes de trabajo, responsables y cuellos de botella.
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

$fasesPermitidas = ['Diseño', 'Abastecimiento', 'Impresión', 'Corte', 'Confección', 'Empaque', 'Distribución'];
$estadosPermitidos = ['En Proceso', 'Detenido / Alerta', 'Completado'];

try {
    switch ($action) {
        case 'crear':
            $codigoOrden = trim($data['codigo_orden'] ?? ('ORD-' . date('Y') . '-' . rand(100, 999)));
            $producto = trim($data['producto'] ?? '');
            $lote = trim($data['lote'] ?? '');
            $cantidad = (int)($data['cantidad'] ?? 0);
            $faseActual = in_array($data['fase_actual'] ?? '', $fasesPermitidas) ? $data['fase_actual'] : 'Diseño';
            $estado = in_array($data['estado'] ?? '', $estadosPermitidos) ? $data['estado'] : 'En Proceso';
            $responsable = trim($data['responsable'] ?? 'Corte y Calidad [2]');
            $notas = trim($data['notas'] ?? '');

            if (empty($producto) || empty($lote) || $cantidad <= 0) {
                jsonResponse(false, null, 'Producto, Lote y Cantidad son campos obligatorios.', 400);
            }

            // Verificar código de orden único
            $stmtCheck = $pdo->prepare("SELECT id FROM `produccion_ordenes` WHERE `codigo_orden` = ?");
            $stmtCheck->execute([$codigoOrden]);
            if ($stmtCheck->fetch()) {
                $codigoOrden .= '-' . rand(10, 99);
            }

            $sql = "INSERT INTO `produccion_ordenes` (`codigo_orden`, `producto`, `lote`, `cantidad`, `fase_actual`, `estado`, `responsable`, `notas`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigoOrden, $producto, $lote, $cantidad, $faseActual, $estado, $responsable, $notas]);
            $newId = $pdo->lastInsertId();

            jsonResponse(true, [
                'id' => $newId,
                'codigo_orden' => $codigoOrden,
                'fase_actual' => $faseActual,
                'estado' => $estado
            ], 'Orden de producción creada exitosamente.');
            break;

        case 'cambiar_fase':
            $id = (int)($data['id'] ?? 0);
            $nuevaFase = trim($data['fase_actual'] ?? '');
            $nuevoEstado = trim($data['estado'] ?? '');

            if ($id <= 0 || !in_array($nuevaFase, $fasesPermitidas)) {
                jsonResponse(false, null, 'Fase de producción inválida.', 400);
            }

            if (!empty($nuevoEstado) && in_array($nuevoEstado, $estadosPermitidos)) {
                $stmt = $pdo->prepare("UPDATE `produccion_ordenes` SET `fase_actual` = ?, `estado` = ? WHERE `id` = ?");
                $stmt->execute([$nuevaFase, $nuevoEstado, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE `produccion_ordenes` SET `fase_actual` = ? WHERE `id` = ?");
                $stmt->execute([$nuevaFase, $id]);
            }

            jsonResponse(true, ['id' => $id, 'fase_actual' => $nuevaFase], 'Fase actualizada correctamente.');
            break;

        case 'cambiar_estado':
            $id = (int)($data['id'] ?? 0);
            $nuevoEstado = trim($data['estado'] ?? '');
            $notas = trim($data['notas'] ?? '');

            if ($id <= 0 || !in_array($nuevoEstado, $estadosPermitidos)) {
                jsonResponse(false, null, 'Estado inválido.', 400);
            }

            if (!empty($notas)) {
                $stmt = $pdo->prepare("UPDATE `produccion_ordenes` SET `estado` = ?, `notas` = ? WHERE `id` = ?");
                $stmt->execute([$nuevoEstado, $notas, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE `produccion_ordenes` SET `estado` = ? WHERE `id` = ?");
                $stmt->execute([$nuevoEstado, $id]);
            }

            jsonResponse(true, ['id' => $id, 'estado' => $nuevoEstado], 'Estado de la orden actualizado.');
            break;

        case 'eliminar':
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'ID inválido.', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM `produccion_ordenes` WHERE `id` = ?");
            $stmt->execute([$id]);

            jsonResponse(true, ['id' => $id], 'Orden de producción eliminada.');
            break;

        default:
            jsonResponse(false, null, 'Acción no permitida.', 400);
            break;
    }

} catch (PDOException $e) {
    jsonResponse(false, ['error' => $e->getMessage()], 'Error en la base de datos: ' . $e->getMessage(), 500);
}
