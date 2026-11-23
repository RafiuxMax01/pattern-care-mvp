<?php
/**
 * PATTERN CARE ERP - MIDDLEWARE DE AUTORIZACIÓN (SCRUM-20)
 *
 * Se incluye al principio de cada endpoint y corta la petición con 401 / 403
 * cuando el usuario no tiene sesión, no tiene el permiso requerido o intenta
 * operar sobre una sucursal que no le fue asignada.
 *
 * Uso típico:
 *
 *   require_once __DIR__ . '/middleware_auth.php';
 *   $perfil = requerirAutenticacion();
 *   requerirPermiso('inventario.ajustar');
 *   requerirSucursal($sucursalId, 'ajustar');
 *
 * MODO COMPATIBILIDAD: si la base de datos todavía no tiene instalado el
 * modelo de accesos (SCRUM-19), el middleware deja pasar la petición con un
 * perfil de alcance total. Así los entornos anteriores a esta historia siguen
 * funcionando sin tocar su base de datos.
 */

require_once __DIR__ . '/modelo_accesos.php';

define('SESION_CLAVE_USUARIO', 'pattern_care_usuario');
define('SESION_CLAVE_SUCURSAL', 'pattern_care_sucursal_activa');

/**
 * Arranca la sesión PHP con cookie restringida al propio sitio.
 */
function iniciarSesionERP() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    if (headers_sent()) {
        return;
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_name('PATTERNCARESESS');
    session_start();
}

/**
 * Perfil de respaldo para bases de datos anteriores a SCRUM-19.
 */
function perfilCompatibilidad() {
    return [
        'id'                  => 0,
        'usuario'             => 'compatibilidad',
        'nombre_completo'     => 'Dueña / Encargada General',
        'correo'              => null,
        'iniciales'           => 'EG',
        'estado'              => 'Activo',
        'rol_clave'           => 'ENCARGADA_GENERAL',
        'rol_nombre'          => 'Dueña / Encargada General',
        'alcance_global'      => true,
        'sucursal_default_id' => null,
        'permisos'            => ['*'],
        'sucursales'          => [],
        'modo_compatibilidad' => true
    ];
}

/**
 * Indica si el control de accesos está activo en esta instalación.
 */
function autorizacionActiva($pdo = null) {
    $pdo = $pdo ?: getDBConnection();
    return $pdo !== null && accesosInstalados($pdo);
}

/**
 * Devuelve el perfil en sesión, o null si no hay sesión iniciada.
 */
function sesionUsuario() {
    iniciarSesionERP();
    return $_SESSION[SESION_CLAVE_USUARIO] ?? null;
}

/**
 * Guarda el perfil en la sesión tras un inicio de sesión correcto.
 */
function guardarSesionUsuario(array $perfil) {
    iniciarSesionERP();
    $_SESSION[SESION_CLAVE_USUARIO] = $perfil;
    $_SESSION[SESION_CLAVE_SUCURSAL] = $perfil['sucursal_default_id'] ?? null;
}

/**
 * Refresca el perfil en sesión desde la base de datos.
 * Se usa cuando la Dueña cambia roles o asignaciones en caliente.
 */
function refrescarSesionUsuario($pdo) {
    $perfil = sesionUsuario();
    if (!$perfil || empty($perfil['id'])) {
        return null;
    }
    $actualizado = perfilDeUsuario($pdo, $perfil['id']);
    if ($actualizado) {
        $_SESSION[SESION_CLAVE_USUARIO] = $actualizado;
    }
    return $actualizado;
}

/**
 * Cierra la sesión del usuario.
 */
function cerrarSesionERP() {
    iniciarSesionERP();
    $_SESSION = [];
    if (ini_get('session.use_cookies') && !headers_sent()) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Exige sesión iniciada. Corta con 401 si no la hay.
 * @return array Perfil del usuario autenticado.
 */
function requerirAutenticacion() {
    if (!autorizacionActiva()) {
        return perfilCompatibilidad();
    }

    $perfil = sesionUsuario();
    if (!$perfil) {
        jsonResponse(false, ['autenticado' => false], 'Sesión no iniciada. Inicia sesión para continuar.', 401);
    }
    if (($perfil['estado'] ?? 'Activo') !== 'Activo') {
        cerrarSesionERP();
        jsonResponse(false, ['autenticado' => false], 'La cuenta fue desactivada. Contacta a la Encargada General.', 401);
    }
    return $perfil;
}

/**
 * Comprueba un permiso sin cortar la ejecución.
 */
function perfilTienePermiso($perfil, $clave) {
    if (!$perfil) {
        return false;
    }
    $permisos = $perfil['permisos'] ?? [];
    return in_array('*', $permisos, true) || in_array($clave, $permisos, true);
}

/**
 * Exige un permiso concreto. Corta con 403 y deja constancia en la bitácora.
 */
function requerirPermiso($clave) {
    $perfil = requerirAutenticacion();
    if (perfilTienePermiso($perfil, $clave)) {
        return $perfil;
    }

    registrarBitacoraAcceso(
        getDBConnection(), 'acceso_denegado',
        $perfil['id'] ?? null, $perfil['usuario'] ?? null, $clave
    );
    jsonResponse(false, [
        'autenticado'      => true,
        'permiso_faltante' => $clave
    ], "Tu rol ({$perfil['rol_nombre']}) no tiene el permiso '{$clave}'.", 403);
}

/**
 * Ids de las sucursales sobre las que puede operar el perfil.
 * Un perfil de alcance global devuelve null (sin restricción).
 */
function sucursalesPermitidasIds($perfil) {
    if (!$perfil || !empty($perfil['alcance_global'])) {
        return null;
    }
    return array_map(function ($s) {
        return (int)$s['sucursal_id'];
    }, $perfil['sucursales'] ?? []);
}

/**
 * Comprueba la asignación a una sucursal sin cortar la ejecución.
 *
 * @param string $accion 'ver' | 'vender' | 'ajustar'
 */
function perfilPuedeEnSucursal($perfil, $sucursalId, $accion = 'ver') {
    if (!$perfil) {
        return false;
    }
    if (!empty($perfil['alcance_global'])) {
        return true;
    }

    foreach ($perfil['sucursales'] ?? [] as $s) {
        if ((int)$s['sucursal_id'] !== (int)$sucursalId) {
            continue;
        }
        if ($accion === 'vender') {
            return !empty($s['puede_vender']);
        }
        if ($accion === 'ajustar') {
            return !empty($s['puede_ajustar_inventario']);
        }
        return true;
    }
    return false;
}

/**
 * Exige que la sucursal recibida esté asignada al usuario.
 * Es la regla que evita descontar inventario del hospital cuando la venta
 * ocurrió en tienda directa.
 */
function requerirSucursal($sucursalId, $accion = 'ver') {
    $perfil = requerirAutenticacion();
    $sucursalId = (int)$sucursalId;

    if ($sucursalId > 0 && perfilPuedeEnSucursal($perfil, $sucursalId, $accion)) {
        return $perfil;
    }

    registrarBitacoraAcceso(
        getDBConnection(), 'acceso_denegado',
        $perfil['id'] ?? null, $perfil['usuario'] ?? null, 'sucursal.' . $accion, $sucursalId
    );
    jsonResponse(false, [
        'autenticado'         => true,
        'sucursal_rechazada'  => $sucursalId,
        'sucursales_permitidas' => sucursalesPermitidasIds($perfil)
    ], 'No tienes asignado ese punto de venta. Selecciona una sucursal de tu lista antes de operar.', 403);
}

/**
 * Resuelve qué sucursal debe consultarse en un endpoint de lectura.
 *
 * - Perfil global: respeta lo pedido (null = consolidado de todas).
 * - Perfil acotado: valida lo pedido y, si no se pidió nada, devuelve su
 *   primera sucursal asignada para que nunca vea datos de otros puntos de venta.
 *
 * @return int|null Id de sucursal a consultar, o null para consolidado.
 */
function resolverSucursalConsulta($perfil, $sucursalSolicitada) {
    $permitidas = sucursalesPermitidasIds($perfil);
    if ($permitidas === null) {
        return $sucursalSolicitada !== null ? (int)$sucursalSolicitada : null;
    }

    if (count($permitidas) === 0) {
        jsonResponse(false, [
            'autenticado' => true,
            'sucursales_permitidas' => []
        ], 'Tu cuenta no tiene puntos de venta asignados. Pide a la Encargada General que te asigne al menos uno.', 403);
    }

    if ($sucursalSolicitada !== null) {
        if (!in_array((int)$sucursalSolicitada, $permitidas, true)) {
            registrarBitacoraAcceso(
                getDBConnection(), 'acceso_denegado',
                $perfil['id'] ?? null, $perfil['usuario'] ?? null, 'sucursal.ver', (int)$sucursalSolicitada
            );
            jsonResponse(false, [
                'autenticado' => true,
                'sucursal_rechazada' => (int)$sucursalSolicitada,
                'sucursales_permitidas' => $permitidas
            ], 'No tienes asignado ese punto de venta.', 403);
        }
        return (int)$sucursalSolicitada;
    }

    return $permitidas[0];
}
