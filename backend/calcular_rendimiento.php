<?php
/**
 * PATTERN CARE - ENDPOINT: GESTIÓN DE RENDIMIENTO DE TELA
 * Calculadora y registro de lotes de tela SRA (6 pzas/m) y CRO (4 pzas/m + 50cm sobrante/m)
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

$action = $data['action'] ?? 'calcular_y_guardar';

try {
    switch ($action) {
        case 'calcular_y_guardar':
            $tipoTela = in_array(strtoupper($data['tipo_tela'] ?? ''), ['SRA', 'CRO']) ? strtoupper($data['tipo_tela']) : 'SRA';
            $metros = (float)($data['metros'] ?? 0);
            $color = trim($data['color'] ?? '');
            $coleccion = trim($data['coleccion'] ?? '');
            $talla = trim($data['talla'] ?? 'Unitalla');
            $loteReferencia = trim($data['lote_referencia'] ?? '');
            $notas = trim($data['notas'] ?? '');

            if ($metros <= 0) {
                jsonResponse(false, null, 'Ingresa una cantidad de metros mayor a 0.', 400);
            }

            if ($tipoTela === 'SRA') {
                // SRA: 6 piezas por metro
                $piezasEstimadas = (int)floor($metros * 6);
                $sobranteCm = 0.0;
                $rendimientoFormula = '6 piezas exactas por metro lineal';
            } else {
                // CRO: 4 piezas y 50 cm sobrantes por metro
                $piezasEstimadas = (int)floor($metros * 4);
                // 50 cm sobrante por cada metro = metros * 50 cm
                $sobranteCm = round($metros * 50.0, 2);
                $rendimientoFormula = '4 piezas por metro + 50 cm de merma/sobrante por metro';
            }

            $sql = "INSERT INTO `rendimiento_telas` (`tipo_tela`, `metros`, `piezas_estimadas`, `sobrante_cm`, `lote_referencia`, `color`, `coleccion`, `talla`, `notas`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$tipoTela, $metros, $piezasEstimadas, $sobranteCm, $loteReferencia, $color, $coleccion, $talla, $notas]);
            $newId = $pdo->lastInsertId();

            jsonResponse(true, [
                'id' => $newId,
                'tipo_tela' => $tipoTela,
                'metros' => $metros,
                'piezas_estimadas' => $piezasEstimadas,
                'sobrante_cm' => $sobranteCm,
                'sobrante_metros' => round($sobranteCm / 100, 2),
                'formula' => $rendimientoFormula,
                'lote_referencia' => $loteReferencia,
                'color' => $color,
                'coleccion' => $coleccion,
                'talla' => $talla
            ], 'Cálculo de rendimiento procesado y guardado en historial.');
            break;

        case 'listar':
            $stmt = $pdo->query("SELECT * FROM `rendimiento_telas` ORDER BY `fecha` DESC LIMIT 50");
            $items = $stmt->fetchAll();
            jsonResponse(true, $items, 'Historial de rendimientos obtenido.');
            break;

        default:
            jsonResponse(false, null, 'Acción no soportada.', 400);
            break;
    }

} catch (PDOException $e) {
    jsonResponse(false, ['error' => $e->getMessage()], 'Error en la base de datos: ' . $e->getMessage(), 500);
}
