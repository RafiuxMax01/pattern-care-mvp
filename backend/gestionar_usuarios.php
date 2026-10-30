<?php
/**
 * PATTERN CARE ERP - BACKEND: USUARIOS, ROLES Y PUNTOS DE VENTA (SCRUM-19)
 *
 * Endpoints:
 *   GET  ?action=listar               → Usuarios con su rol y sucursales asignadas
 *   GET  ?action=obtener&id=N         → Ficha completa de un usuario
 *   GET  ?action=catalogos            → Roles, permisos y sucursales disponibles
 *   GET  ?action=bitacora             → Últimos movimientos de la bitácora de accesos
 *   POST action=crear                 → Alta de usuario
 *   POST action=actualizar            → Edición de datos y rol
 *   POST action=cambiar_estado        → Activa / desactiva una cuenta
 *   POST action=asignar_sucursales    → Reemplaza los puntos de venta del usuario
 *   POST action=cambiar_password      → Fija una contraseña nueva (bcrypt)
 *   POST action=permisos_rol          → Reemplaza los permisos de un rol
 */

require_once __DIR__ . '/middleware_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST') {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? $_POST;
} else {
    $input = $_GET;
}

$action = $input['action'] ?? 'listar';

// Autorización por acción (SCRUM-20): solo la Encargada General administra accesos
$permisosPorAccion = [
    'crear'              => 'usuarios.gestionar',
    'actualizar'         => 'usuarios.gestionar',
    'cambiar_estado'     => 'usuarios.gestionar',
    'asignar_sucursales' => 'usuarios.gestionar',
    'cambiar_password'   => 'usuarios.gestionar',
    'permisos_rol'       => 'usuarios.gestionar'
];
$perfil = requerirPermiso($permisosPorAccion[$action] ?? 'usuarios.ver');

$pdo = getDBConnection();
if (!$pdo) {
    jsonResponse(false, null, 'Base de datos no disponible.', 503);
}
if (!accesosInstalados($pdo)) {
    jsonResponse(false, null, 'El módulo de accesos no está instalado. Ejecuta database_v3_usuarios.sql.', 503);
}

/**
 * Agrupa las asignaciones de punto de venta por usuario en una sola consulta.
 */
function asignacionesPorUsuario($pdo) {
    $stmt = $pdo->query("
        SELECT us.`usuario_id`, us.`sucursal_id`, us.`puede_vender`, us.`puede_ajustar_inventario`,
               us.`asignado_por`, us.`fecha_asignacion`, s.`codigo`, s.`nombre`, s.`tipo`
        FROM `usuario_sucursales` us
        INNER JOIN `sucursales` s ON s.`id` = us.`sucursal_id`
        ORDER BY us.`usuario_id`, s.`id`
    ");

    $mapa = [];
    foreach ($stmt->fetchAll() as $fila) {
        $mapa[(int)$fila['usuario_id']][] = [
            'sucursal_id'              => (int)$fila['sucursal_id'],
            'codigo'                   => $fila['codigo'],
            'nombre'                   => $fila['nombre'],
            'tipo'                     => $fila['tipo'],
            'puede_vender'             => (int)$fila['puede_vender'] === 1,
            'puede_ajustar_inventario' => (int)$fila['puede_ajustar_inventario'] === 1,
            'asignado_por'             => $fila['asignado_por'],
            'fecha_asignacion'         => $fila['fecha_asignacion']
        ];
    }
    return $mapa;
}

try {
    switch ($action) {

        // ── LISTAR USUARIOS ───────────────────────────────────────
        case 'listar':
            $stmt = $pdo->query("
                SELECT u.`id`, u.`usuario`, u.`nombre_completo`, u.`correo`, u.`estado`,
                       u.`ultimo_acceso`, u.`fecha_registro`, u.`sucursal_default_id`,
                       r.`id` AS `rol_id`, r.`clave` AS `rol_clave`, r.`nombre` AS `rol_nombre`,
                       r.`alcance_global`, s.`nombre` AS `sucursal_default`
                FROM `usuarios` u
                INNER JOIN `roles` r     ON r.`id` = u.`rol_id`
                LEFT JOIN `sucursales` s ON s.`id` = u.`sucursal_default_id`
                ORDER BY r.`alcance_global` DESC, u.`nombre_completo`
            ");
            $usuarios   = $stmt->fetchAll();
            $asignadas  = asignacionesPorUsuario($pdo);

            $salida = [];
            foreach ($usuarios as $u) {
                $id = (int)$u['id'];
                $salida[] = [
                    'id'                  => $id,
                    'usuario'             => $u['usuario'],
                    'nombre_completo'     => $u['nombre_completo'],
                    'correo'              => $u['correo'],
                    'estado'              => $u['estado'],
                    'ultimo_acceso'       => $u['ultimo_acceso'],
                    'fecha_registro'      => $u['fecha_registro'],
                    'rol_id'              => (int)$u['rol_id'],
                    'rol_clave'           => $u['rol_clave'],
                    'rol_nombre'          => $u['rol_nombre'],
                    'alcance_global'      => (int)$u['alcance_global'] === 1,
                    'sucursal_default_id' => $u['sucursal_default_id'] !== null ? (int)$u['sucursal_default_id'] : null,
                    'sucursal_default'    => $u['sucursal_default'],
                    'sucursales'          => $asignadas[$id] ?? []
                ];
            }

            jsonResponse(true, $salida, 'Usuarios obtenidos correctamente.');
            break;

        // ── FICHA DE UN USUARIO ───────────────────────────────────
        case 'obtener':
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(false, null, 'Identificador de usuario inválido.', 400);
            }
            $perfil = perfilDeUsuario($pdo, $id);
            if (!$perfil) {
                jsonResponse(false, null, 'El usuario no existe.', 404);
            }
            jsonResponse(true, $perfil, 'Usuario obtenido correctamente.');
            break;

        // ── CATÁLOGOS (roles, permisos y sucursales) ──────────────
        case 'catalogos':
            $roles = $pdo->query("
                SELECT r.`id`, r.`clave`, r.`nombre`, r.`descripcion`, r.`alcance_global`, r.`estado`,
                       GROUP_CONCAT(p.`clave` ORDER BY p.`clave` SEPARATOR ',') AS `permisos`
                FROM `roles` r
                LEFT JOIN `rol_permisos` rp ON rp.`rol_id` = r.`id`
                LEFT JOIN `permisos` p      ON p.`id` = rp.`permiso_id`
                GROUP BY r.`id`, r.`clave`, r.`nombre`, r.`descripcion`, r.`alcance_global`, r.`estado`
                ORDER BY r.`alcance_global` DESC, r.`nombre`
            ")->fetchAll();

            $rolesSalida = array_map(function ($r) {
                return [
                    'id'             => (int)$r['id'],
                    'clave'          => $r['clave'],
                    'nombre'         => $r['nombre'],
                    'descripcion'    => $r['descripcion'],
                    'alcance_global' => (int)$r['alcance_global'] === 1,
                    'estado'         => $r['estado'],
                    'permisos'       => $r['permisos'] ? explode(',', $r['permisos']) : []
                ];
            }, $roles);

            $permisos = $pdo->query("
                SELECT `id`, `clave`, `modulo`, `nombre`, `descripcion`
                FROM `permisos` ORDER BY `modulo`, `clave`
            ")->fetchAll();

            $sucursales = $pdo->query("
                SELECT `id`, `codigo`, `nombre`, `tipo`, `estado`
                FROM `sucursales` WHERE `estado` = 'Activo' ORDER BY `id`
            ")->fetchAll();

            jsonResponse(true, [
                'roles'      => $rolesSalida,
                'permisos'   => $permisos,
                'sucursales' => $sucursales
            ], 'Catálogos de accesos obtenidos.');
            break;

        // ── BITÁCORA DE ACCESOS ───────────────────────────────────
        case 'bitacora':
            $limite = (int)($input['limite'] ?? 50);
            if ($limite <= 0 || $limite > 200) {
                $limite = 50;
            }
            $stmt = $pdo->prepare("
                SELECT b.`id`, b.`usuario`, b.`evento`, b.`permiso`, b.`recurso`, b.`ip`, b.`fecha`,
                       s.`nombre` AS `sucursal`
                FROM `bitacora_accesos` b
                LEFT JOIN `sucursales` s ON s.`id` = b.`sucursal_id`
                ORDER BY b.`fecha` DESC, b.`id` DESC
                LIMIT {$limite}
            ");
            $stmt->execute();
            jsonResponse(true, $stmt->fetchAll(), 'Bitácora obtenida.');
            break;

        // ── ALTA DE USUARIO ───────────────────────────────────────
        case 'crear':
            $usuario  = trim($input['usuario'] ?? '');
            $nombre   = trim($input['nombre_completo'] ?? '');
            $correo   = trim($input['correo'] ?? '');
            $rolId    = (int)($input['rol_id'] ?? 0);
            $password = (string)($input['password'] ?? '');
            $sucursalDefault = isset($input['sucursal_default_id']) && $input['sucursal_default_id'] !== ''
                ? (int)$input['sucursal_default_id'] : null;

            if ($usuario === '' || $nombre === '' || $rolId <= 0) {
                jsonResponse(false, null, 'Usuario, nombre completo y rol son obligatorios.', 400);
            }
            if (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/', $usuario)) {
                jsonResponse(false, null, 'El nombre de cuenta admite 3 a 40 caracteres: letras, números, punto, guion y guion bajo.', 400);
            }
            if (strlen($password) < 8) {
                jsonResponse(false, null, 'La contraseña debe tener al menos 8 caracteres.', 400);
            }

            $existe = $pdo->prepare("SELECT `id` FROM `usuarios` WHERE `usuario` = ? LIMIT 1");
            $existe->execute([$usuario]);
            if ($existe->fetch()) {
                jsonResponse(false, null, "La cuenta '{$usuario}' ya está registrada.", 400);
            }

            $stmt = $pdo->prepare("
                INSERT INTO `usuarios`
                    (`usuario`, `nombre_completo`, `correo`, `password_hash`, `rol_id`, `sucursal_default_id`, `estado`)
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')
            ");
            $stmt->execute([$usuario, $nombre, $correo ?: null, hashPassword($password), $rolId, $sucursalDefault]);
            $nuevoId = (int)$pdo->lastInsertId();

            // Asignación inicial de puntos de venta (opcional en el alta)
            $asignaciones = $input['sucursales'] ?? [];
            if (is_array($asignaciones) && count($asignaciones) > 0) {
                reemplazarSucursalesDeUsuario($pdo, $nuevoId, $asignaciones, $perfil['nombre_completo'] ?? null);
            }

            jsonResponse(true, perfilDeUsuario($pdo, $nuevoId), 'Usuario creado correctamente.', 201);
            break;

        // ── EDICIÓN DE DATOS Y ROL ────────────────────────────────
        case 'actualizar':
            $id     = (int)($input['id'] ?? 0);
            $nombre = trim($input['nombre_completo'] ?? '');
            $correo = trim($input['correo'] ?? '');
            $rolId  = (int)($input['rol_id'] ?? 0);
            $sucursalDefault = isset($input['sucursal_default_id']) && $input['sucursal_default_id'] !== ''
                ? (int)$input['sucursal_default_id'] : null;

            if ($id <= 0 || $nombre === '' || $rolId <= 0) {
                jsonResponse(false, null, 'Identificador, nombre completo y rol son obligatorios.', 400);
            }

            $stmt = $pdo->prepare("
                UPDATE `usuarios`
                SET `nombre_completo` = ?, `correo` = ?, `rol_id` = ?, `sucursal_default_id` = ?
                WHERE `id` = ?
            ");
            $stmt->execute([$nombre, $correo ?: null, $rolId, $sucursalDefault, $id]);

            if (isset($input['sucursales']) && is_array($input['sucursales'])) {
                reemplazarSucursalesDeUsuario($pdo, $id, $input['sucursales'], $perfil['nombre_completo'] ?? null);
            }

            jsonResponse(true, perfilDeUsuario($pdo, $id), 'Usuario actualizado correctamente.');
            break;

        // ── ACTIVAR / DESACTIVAR CUENTA ───────────────────────────
        case 'cambiar_estado':
            $id     = (int)($input['id'] ?? 0);
            $estado = ($input['estado'] ?? '') === 'Inactivo' ? 'Inactivo' : 'Activo';
            if ($id <= 0) {
                jsonResponse(false, null, 'Identificador de usuario inválido.', 400);
            }

            $stmt = $pdo->prepare("UPDATE `usuarios` SET `estado` = ? WHERE `id` = ?");
            $stmt->execute([$estado, $id]);

            jsonResponse(true, ['id' => $id, 'estado' => $estado], "La cuenta quedó en estado {$estado}.");
            break;

        // ── ASIGNACIÓN DE PUNTOS DE VENTA ─────────────────────────
        case 'asignar_sucursales':
            $id = (int)($input['id'] ?? 0);
            $asignaciones = $input['sucursales'] ?? null;

            if ($id <= 0 || !is_array($asignaciones)) {
                jsonResponse(false, null, 'Se requiere el usuario y la lista de sucursales a asignar.', 400);
            }
            if (!buscarUsuarioPorId($pdo, $id)) {
                jsonResponse(false, null, 'El usuario no existe.', 404);
            }

            $total = reemplazarSucursalesDeUsuario($pdo, $id, $asignaciones, $perfil['nombre_completo'] ?? null);

            jsonResponse(true, perfilDeUsuario($pdo, $id), "Se asignaron {$total} punto(s) de venta al usuario.");
            break;

        // ── CAMBIO DE CONTRASEÑA ──────────────────────────────────
        case 'cambiar_password':
            $id       = (int)($input['id'] ?? 0);
            $password = (string)($input['password'] ?? '');

            if ($id <= 0 || strlen($password) < 8) {
                jsonResponse(false, null, 'La contraseña nueva debe tener al menos 8 caracteres.', 400);
            }
            if (!buscarUsuarioPorId($pdo, $id)) {
                jsonResponse(false, null, 'El usuario no existe.', 404);
            }

            actualizarPasswordHash($pdo, $id, hashPassword($password));
            jsonResponse(true, ['id' => $id], 'Contraseña actualizada correctamente.');
            break;

        // ── PERMISOS DE UN ROL ────────────────────────────────────
        case 'permisos_rol':
            $rolId    = (int)($input['rol_id'] ?? 0);
            $permisos = $input['permisos'] ?? null;

            if ($rolId <= 0 || !is_array($permisos)) {
                jsonResponse(false, null, 'Se requiere el rol y la lista de permisos.', 400);
            }

            $total = reemplazarPermisosDeRol($pdo, $rolId, $permisos);
            jsonResponse(true, ['rol_id' => $rolId, 'permisos_asignados' => $total], "El rol quedó con {$total} permiso(s).");
            break;

        default:
            jsonResponse(false, null, "Acción no reconocida: {$action}", 400);
    }
} catch (PDOException $e) {
    jsonResponse(false, null, 'Error de base de datos: ' . $e->getMessage(), 500);
}
