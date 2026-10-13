<?php
/**
 * PATTERN CARE - ENDPOINT: CONTROL DE MERMAS Y RETRABAJOS
 * Registro de costos ocultos, errores de estampado, confección y empaquetado.
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
            $fecha = !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d');
            $descripcionError = trim($data['descripcion_error'] ?? '');
            $tipo = in_array($data['tipo'] ?? '', ['Merma', 'Retrabajo']) ? $data['tipo'] : 'Merma';
            $area = trim($data['area'] ?? 'Corte');
            $materialDesperdiciado = trim($data['material_desperdiciado'] ?? '');
            $costoImpacto = (float)($data['costo_impacto'] ?? 0.0);
            $loteAfectado = trim($data['lote_afectado'] ?? 'N/A');
            $registradoPor = trim($data['registrado_por'] ?? 'Corte y Calidad [2]');

            if (empty($descripcionError) || empty($materialDesperdiciado)) {
                jsonResponse(false, null, 'La descripción y el material desperdiciado son obligatorios.', 400);
            }

            $sql = "INSERT INTO `mermas_retrabajos` (`fecha`, `descripcion_error`, `tipo`, `area`, `material_desperdiciado`, `costo_impacto`, `lote_afectado`, `registrado_por`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha, $descripcionError, $tipo, $area, $materialDesperdiciado, $costoImpacto, $loteAfectado, $registradoPor]);
            $newId = $pdo->lastInsertId();

            jsonResponse(true, [
                'id' => $newId,
                'costo_impacto' => $costoImpacto,
                'tipo' => $tipo
            ], 'Incidencia registrada en la bitácora de mermas.');
            break;

        case 'eliminar':
            $id = (int)($data['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'ID inválido.', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM `mermas_retrabajos` WHERE `id` = ?");
            $stmt->execute([$id]);

            jsonResponse(true, ['id' => $id], 'Registro de merma eliminado.');
            break;

        default:
            jsonResponse(false, null, 'Acción no permitida.', 400);
            break;
    }

} catch (PDOException $e) {
    jsonResponse(false, ['error' => $e->getMessage()], 'Error en la base de datos: ' . $e->getMessage(), 500);
}
