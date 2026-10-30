
<?php
/**
 * PATTERN CARE - Registro y Administración de Sucursales
 * Operaciones: listar, obtener, crear, actualizar, cambiar_estado
 */

require_once __DIR__ . '/../conexion.php';

$pdo = getDBConnection();

if (!$pdo) {
    jsonResponse(
        false,
        null,
        'No se pudo conectar con la base de datos.',
        503
    );
}

// Permitir solicitudes de preflight CORS.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Aceptar datos JSON o formularios tradicionales.
$entrada = [];
$contenido = file_get_contents('php://input');

if ($contenido !== false && trim($contenido) !== '') {
    $json = json_decode($contenido, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        $entrada = $json;
    } elseif (
        str_contains(
            $_SERVER['CONTENT_TYPE'] ?? '',
            'application/json'
        )
    ) {
        jsonResponse(false, null, 'El JSON recibido no es válido.', 400);
    }
}

$entrada = array_merge($_POST, $entrada);

$accion = $_GET['accion'] ?? $entrada['accion'] ?? 'listar';

$tiposPermitidos = [
    'Planta / Taller Central',
    'Punto de Venta / Showroom',
    'Almacén Digital'
];

function texto($datos, $campo) {
    return trim((string)($datos[$campo] ?? ''));
}

function validarSucursal($datos, $tiposPermitidos) {
    $campos = ['codigo', 'nombre', 'tipo', 'encargado', 'direccion'];

    foreach ($campos as $campo) {
        if (texto($datos, $campo) === '') {
            jsonResponse(
                false,
                ['campo' => $campo],
                "El campo {$campo} es obligatorio.",
                422
            );
        }
    }

    if (!in_array($datos['tipo'], $tiposPermitidos, true)) {
        jsonResponse(false, null, 'El tipo de sucursal no es válido.', 422);
    }

    $limites = [
        'codigo' => 20,
        'nombre' => 100,
        'encargado' => 100,
        'telefono' => 50,
        'direccion' => 200
    ];

    foreach ($limites as $campo => $limite) {
        if (mb_strlen(texto($datos, $campo)) > $limite) {
            jsonResponse(
                false,
                ['campo' => $campo, 'longitud_maxima' => $limite],
                "El campo {$campo} supera la longitud permitida.",
                422
            );
        }
    }
}

try {
    switch ($accion) {
        case 'listar':
            // Incluye sucursales activas e inactivas.
            $stmt = $pdo->query(
                "SELECT * FROM sucursales ORDER BY id DESC"
            );

            jsonResponse(
                true,
                ['sucursales' => $stmt->fetchAll()],
                'Sucursales consultadas correctamente.'
            );
            break;

        case 'obtener':
            $id = filter_var(
                $_GET['id'] ?? $entrada['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                jsonResponse(false, null, 'El ID no es válido.', 422);
            }

            $stmt = $pdo->prepare(
                "SELECT * FROM sucursales WHERE id = ?"
            );
            $stmt->execute([$id]);
            $sucursal = $stmt->fetch();

            if (!$sucursal) {
                jsonResponse(false, null, 'Sucursal no encontrada.', 404);
            }

            jsonResponse(true, $sucursal, 'Sucursal consultada correctamente.');
            break;

        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(false, null, 'Se requiere POST.', 405);
            }

            validarSucursal($entrada, $tiposPermitidos);

            $estado = $entrada['estado'] ?? 'Activo';

            if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
                jsonResponse(false, null, 'El estado no es válido.', 422);
            }

            $stmt = $pdo->prepare(
                "INSERT INTO sucursales
                (codigo, nombre, tipo, encargado, telefono, direccion, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                texto($entrada, 'codigo'),
                texto($entrada, 'nombre'),
                $entrada['tipo'],
                texto($entrada, 'encargado'),
                texto($entrada, 'telefono') ?: null,
                texto($entrada, 'direccion'),
                $estado
            ]);

            $idNuevo = (int)$pdo->lastInsertId();

            $stmt = $pdo->prepare(
                "SELECT * FROM sucursales WHERE id = ?"
            );
            $stmt->execute([$idNuevo]);

            jsonResponse(
                true,
                $stmt->fetch(),
                'Sucursal registrada correctamente.',
                201
            );
            break;

        case 'actualizar':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(false, null, 'Se requiere POST.', 405);
            }

            $id = filter_var(
                $entrada['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                jsonResponse(false, null, 'El ID no es válido.', 422);
            }

            validarSucursal($entrada, $tiposPermitidos);

            $stmt = $pdo->prepare(
                "UPDATE sucursales
                SET codigo = ?, nombre = ?, tipo = ?, encargado = ?,
                    telefono = ?, direccion = ?
                WHERE id = ?"
            );

            $stmt->execute([
                texto($entrada, 'codigo'),
                texto($entrada, 'nombre'),
                $entrada['tipo'],
                texto($entrada, 'encargado'),
                texto($entrada, 'telefono') ?: null,
                texto($entrada, 'direccion'),
                $id
            ]);

            $stmt = $pdo->prepare(
                "SELECT * FROM sucursales WHERE id = ?"
            );
            $stmt->execute([$id]);
            $sucursal = $stmt->fetch();

            if (!$sucursal) {
                jsonResponse(false, null, 'Sucursal no encontrada.', 404);
            }

            jsonResponse(
                true,
                $sucursal,
                'Sucursal actualizada correctamente.'
            );
            break;

        case 'cambiar_estado':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(false, null, 'Se requiere POST.', 405);
            }

            $id = filter_var(
                $entrada['id'] ?? null,
                FILTER_VALIDATE_INT
            );
            $estado = $entrada['estado'] ?? '';

            if (!$id || $id < 1) {
                jsonResponse(false, null, 'El ID no es válido.', 422);
            }

            if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
                jsonResponse(false, null, 'El estado no es válido.', 422);
            }

            $stmt = $pdo->prepare(
                "UPDATE sucursales SET estado = ? WHERE id = ?"
            );
            $stmt->execute([$estado, $id]);

            if ($stmt->rowCount() === 0) {
                $stmt = $pdo->prepare(
                    "SELECT id FROM sucursales WHERE id = ?"
                );
                $stmt->execute([$id]);

                if (!$stmt->fetch()) {
                    jsonResponse(false, null, 'Sucursal no encontrada.', 404);
                }
            }

            $stmt = $pdo->prepare(
                "SELECT * FROM sucursales WHERE id = ?"
            );
            $stmt->execute([$id]);

            jsonResponse(
                true,
                $stmt->fetch(),
                'Estado de la sucursal actualizado correctamente.'
            );
            break;

        default:
            jsonResponse(
                false,
                ['acciones_disponibles' => [
                    'listar',
                    'obtener',
                    'crear',
                    'actualizar',
                    'cambiar_estado'
                ]],
                'La acción solicitada no existe.',
                400
            );
    }
} catch (PDOException $e) {
    // SQLSTATE 23000 incluye errores de integridad, como códigos duplicados.
    if ($e->getCode() === '23000') {
        jsonResponse(
            false,
            null,
            'No se pudo guardar. Verifica que el código de sucursal no esté repetido.',
            409
        );
    }

    error_log('Error en gestionar_sucursales.php: ' . $e->getMessage());

    jsonResponse(
        false,
        null,
        'Ocurrió un error al procesar la solicitud.',
        500
    );
}
