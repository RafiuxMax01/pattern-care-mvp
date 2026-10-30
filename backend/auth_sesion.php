<?php
/**
 * PATTERN CARE ERP - SESIÓN DE USUARIO (SCRUM-20)
 *
 * Endpoints:
 *   GET  ?action=sesion                → Perfil en sesión (data = null si no hay)
 *   POST action=login                  → Inicia sesión con usuario y contraseña
 *   POST action=logout                 → Cierra la sesión
 *   POST action=seleccionar_sucursal   → Fija la sucursal activa del usuario
 *
 * El perfil devuelto incluye rol, permisos y puntos de venta asignados: es la
 * carga que consume el contexto de autenticación del frontend (SCRUM-21).
 */

require_once __DIR__ . '/middleware_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;
} else {
    $input = $_GET;
}

$action = $input['action'] ?? 'sesion';
$pdo    = getDBConnection();

/** Intentos fallidos recientes de una cuenta (defensa básica contra fuerza bruta) */
define('LOGIN_MAX_INTENTOS', 5);
define('LOGIN_VENTANA_MINUTOS', 10);

function intentosFallidosRecientes($pdo, $usuario) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS `total`
        FROM `bitacora_accesos`
        WHERE `evento` = 'login_fallido'
          AND `usuario` = ?
          AND `fecha` > DATE_SUB(NOW(), INTERVAL " . LOGIN_VENTANA_MINUTOS . " MINUTE)
    ");
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();
    return (int)($fila['total'] ?? 0);
}

try {
    switch ($action) {

        // ── PERFIL EN SESIÓN ──────────────────────────────────────
        case 'sesion':
            if (!autorizacionActiva($pdo)) {
                jsonResponse(true, perfilCompatibilidad(), 'Control de accesos no instalado: modo compatibilidad.');
            }

            $perfil = sesionUsuario();
            if (!$perfil) {
                jsonResponse(true, null, 'Sin sesión activa.');
            }

            // Se relee de la base para reflejar cambios de rol o de asignaciones
            $actualizado = refrescarSesionUsuario($pdo);
            if (!$actualizado || $actualizado['estado'] !== 'Activo') {
                cerrarSesionERP();
                jsonResponse(true, null, 'La sesión caducó o la cuenta fue desactivada.');
            }

            $actualizado['sucursal_activa_id'] = $_SESSION[SESION_CLAVE_SUCURSAL] ?? null;
            jsonResponse(true, $actualizado, 'Sesión activa.');
            break;

        // ── INICIO DE SESIÓN ──────────────────────────────────────
        case 'login':
            if (!$pdo) {
                jsonResponse(false, null, 'Base de datos no disponible.', 503);
            }
            if (!accesosInstalados($pdo)) {
                jsonResponse(true, perfilCompatibilidad(), 'Control de accesos no instalado: modo compatibilidad.');
            }

            $usuario  = trim($input['usuario'] ?? '');
            $password = (string)($input['password'] ?? '');

            if ($usuario === '' || $password === '') {
                jsonResponse(false, null, 'Captura tu usuario y tu contraseña.', 400);
            }

            if (intentosFallidosRecientes($pdo, $usuario) >= LOGIN_MAX_INTENTOS) {
                jsonResponse(false, null, 'Demasiados intentos fallidos. Espera ' . LOGIN_VENTANA_MINUTOS . ' minutos o pide un restablecimiento.', 429);
            }

            $fila = buscarUsuarioPorNombre($pdo, $usuario);
            $necesitaRehash = false;

            if (!$fila || !verificarPassword($password, $fila['password_hash'], $necesitaRehash)) {
                registrarBitacoraAcceso($pdo, 'login_fallido', $fila['id'] ?? null, $usuario);
                jsonResponse(false, null, 'Usuario o contraseña incorrectos.', 401);
            }
            if ($fila['estado'] !== 'Activo') {
                registrarBitacoraAcceso($pdo, 'login_fallido', $fila['id'], $usuario);
                jsonResponse(false, null, 'La cuenta está desactivada. Contacta a la Encargada General.', 403);
            }

            // Migración transparente del hash semilla (SHA-256) a bcrypt
            if ($necesitaRehash) {
                actualizarPasswordHash($pdo, $fila['id'], hashPassword($password));
            }

            $perfil = perfilDeUsuario($pdo, $fila['id']);
            if (empty($perfil['alcance_global']) && count($perfil['sucursales']) === 0) {
                registrarBitacoraAcceso($pdo, 'login_fallido', $fila['id'], $usuario);
                jsonResponse(false, null, 'Tu cuenta no tiene puntos de venta asignados. Pide a la Encargada General que te asigne al menos uno.', 403);
            }

            registrarUltimoAcceso($pdo, $fila['id']);
            registrarBitacoraAcceso($pdo, 'login_ok', $fila['id'], $usuario);
            guardarSesionUsuario($perfil);

            $perfil['sucursal_activa_id'] = $_SESSION[SESION_CLAVE_SUCURSAL] ?? null;
            jsonResponse(true, $perfil, 'Bienvenida, ' . $perfil['nombre_completo'] . '.');
            break;

        // ── CIERRE DE SESIÓN ──────────────────────────────────────
        case 'logout':
            $perfil = sesionUsuario();
            if ($perfil && $pdo) {
                registrarBitacoraAcceso($pdo, 'logout', $perfil['id'] ?? null, $perfil['usuario'] ?? null);
            }
            cerrarSesionERP();
            jsonResponse(true, null, 'Sesión cerrada.');
            break;

        // ── SUCURSAL ACTIVA ───────────────────────────────────────
        case 'seleccionar_sucursal':
            $perfil = requerirAutenticacion();
            $sucursalId = $input['sucursal_id'] ?? null;

            // null = consolidado de todas las sucursales (solo alcance global)
            if ($sucursalId === null || $sucursalId === '') {
                if (empty($perfil['alcance_global'])) {
                    jsonResponse(false, null, 'Tu rol solo puede trabajar sobre sus puntos de venta asignados.', 403);
                }
                iniciarSesionERP();
                $_SESSION[SESION_CLAVE_SUCURSAL] = null;
                jsonResponse(true, ['sucursal_activa_id' => null], 'Vista consolidada de todas las sucursales.');
            }

            $sucursalId = (int)$sucursalId;
            requerirSucursal($sucursalId, 'ver');

            iniciarSesionERP();
            $_SESSION[SESION_CLAVE_SUCURSAL] = $sucursalId;
            jsonResponse(true, ['sucursal_activa_id' => $sucursalId], 'Sucursal activa actualizada.');
            break;

        default:
            jsonResponse(false, null, "Acción no reconocida: {$action}", 400);
    }
} catch (PDOException $e) {
    jsonResponse(false, null, 'Error de base de datos: ' . $e->getMessage(), 500);
}
