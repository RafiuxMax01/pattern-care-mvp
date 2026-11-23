<?php
/**
 * PATTERN CARE ERP - MODELO DE ROLES Y PERMISOS (SCRUM-19)
 *
 * Capa de datos del módulo de accesos. No imprime nada ni corta la
 * ejecución: solo lee y escribe las tablas `roles`, `permisos`,
 * `rol_permisos`, `usuarios`, `usuario_sucursales` y `bitacora_accesos`.
 *
 * Lo consumen:
 *   - backend/gestionar_usuarios.php  → administración de usuarios y accesos
 *   - backend/middleware_auth.php     → verificación de sesión y permisos
 */

require_once __DIR__ . '/../conexion.php';

/** Contraseña inicial sembrada por database_master.sql / database_v3_usuarios.sql */
define('ACCESOS_PASSWORD_SEMILLA', 'PatternCare2026');

/**
 * Indica si el esquema de accesos ya está instalado en la base de datos.
 * Permite que el ERP siga funcionando contra bases creadas antes de SCRUM-19.
 */
function accesosInstalados($pdo) {
    static $instalado = null;
    if ($instalado !== null) {
        return $instalado;
    }
    if (!$pdo) {
        return $instalado = false;
    }
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuario_sucursales'");
        $instalado = $stmt && $stmt->fetch() !== false;
    } catch (PDOException $e) {
        $instalado = false;
    }
    return $instalado;
}

/**
 * Genera el hash definitivo (bcrypt) de una contraseña.
 */
function hashPassword($plano) {
    return password_hash($plano, PASSWORD_BCRYPT);
}

/**
 * Verifica una contraseña contra el hash almacenado.
 *
 * Acepta dos formatos:
 *   - bcrypt  ($2y$...)         → formato definitivo
 *   - SHA-256 (64 caracteres hex) → formato semilla del script SQL
 *
 * @param bool $necesitaRehash Se marca como true cuando el hash guardado
 *                             es de semilla y conviene migrarlo a bcrypt.
 */
function verificarPassword($plano, $hashGuardado, &$necesitaRehash = false) {
    $necesitaRehash = false;
    if ($hashGuardado === null || $hashGuardado === '') {
        return false;
    }

    // Formato semilla: SHA-256 en hexadecimal
    if (preg_match('/^[a-f0-9]{64}$/i', $hashGuardado)) {
        if (hash_equals(strtolower($hashGuardado), hash('sha256', $plano))) {
            $necesitaRehash = true;
            return true;
        }
        return false;
    }

    if (password_verify($plano, $hashGuardado)) {
        $necesitaRehash = password_needs_rehash($hashGuardado, PASSWORD_BCRYPT);
        return true;
    }
    return false;
}

/**
 * Busca un usuario activo por su nombre de cuenta (incluye datos del rol).
 */
function buscarUsuarioPorNombre($pdo, $usuario) {
    $stmt = $pdo->prepare("
        SELECT u.`id`, u.`usuario`, u.`nombre_completo`, u.`correo`, u.`password_hash`,
               u.`rol_id`, u.`sucursal_default_id`, u.`estado`, u.`ultimo_acceso`,
               r.`clave` AS `rol_clave`, r.`nombre` AS `rol_nombre`, r.`alcance_global`
        FROM `usuarios` u
        INNER JOIN `roles` r ON r.`id` = u.`rol_id`
        WHERE u.`usuario` = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/**
 * Obtiene un usuario por su id (incluye datos del rol).
 */
function buscarUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("
        SELECT u.`id`, u.`usuario`, u.`nombre_completo`, u.`correo`, u.`password_hash`,
               u.`rol_id`, u.`sucursal_default_id`, u.`estado`, u.`ultimo_acceso`,
               r.`clave` AS `rol_clave`, r.`nombre` AS `rol_nombre`, r.`alcance_global`
        FROM `usuarios` u
        INNER JOIN `roles` r ON r.`id` = u.`rol_id`
        WHERE u.`id` = ?
        LIMIT 1
    ");
    $stmt->execute([(int)$id]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/**
 * Devuelve las claves de permiso concedidas al rol del usuario.
 * @return string[] ej. ['inventario.ver', 'inventario.ajustar']
 */
function obtenerPermisosDeUsuario($pdo, $usuarioId) {
    $stmt = $pdo->prepare("
        SELECT p.`clave`
        FROM `usuarios` u
        INNER JOIN `rol_permisos` rp ON rp.`rol_id` = u.`rol_id`
        INNER JOIN `permisos` p      ON p.`id` = rp.`permiso_id`
        WHERE u.`id` = ?
        ORDER BY p.`clave`
    ");
    $stmt->execute([(int)$usuarioId]);
    return array_column($stmt->fetchAll(), 'clave');
}

/**
 * Devuelve los puntos de venta sobre los que puede operar el usuario.
 * Un rol con `alcance_global` recibe todas las sucursales activas.
 */
function obtenerSucursalesDeUsuario($pdo, $usuarioId, $alcanceGlobal = false) {
    if ($alcanceGlobal) {
        $stmt = $pdo->prepare("
            SELECT s.`id` AS `sucursal_id`, s.`codigo`, s.`nombre`, s.`tipo`,
                   1 AS `puede_vender`, 1 AS `puede_ajustar_inventario`
            FROM `sucursales` s
            WHERE s.`estado` = 'Activo'
            ORDER BY s.`id`
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    $stmt = $pdo->prepare("
        SELECT us.`sucursal_id`, s.`codigo`, s.`nombre`, s.`tipo`,
               us.`puede_vender`, us.`puede_ajustar_inventario`
        FROM `usuario_sucursales` us
        INNER JOIN `sucursales` s ON s.`id` = us.`sucursal_id`
        WHERE us.`usuario_id` = ? AND s.`estado` = 'Activo'
        ORDER BY s.`id`
    ");
    $stmt->execute([(int)$usuarioId]);
    return $stmt->fetchAll();
}

/**
 * Arma el perfil completo del usuario: identidad + rol + permisos + sucursales.
 * Es la estructura que viaja a la sesión PHP y al contexto de autenticación
 * del frontend. Nunca incluye el hash de la contraseña.
 */
function perfilDeUsuario($pdo, $usuario) {
    if (is_numeric($usuario)) {
        $usuario = buscarUsuarioPorId($pdo, $usuario);
    }
    if (!$usuario) {
        return null;
    }

    $alcanceGlobal = (int)$usuario['alcance_global'] === 1;
    $sucursales    = obtenerSucursalesDeUsuario($pdo, $usuario['id'], $alcanceGlobal);

    return [
        'id'                  => (int)$usuario['id'],
        'usuario'             => $usuario['usuario'],
        'nombre_completo'     => $usuario['nombre_completo'],
        'correo'              => $usuario['correo'],
        'iniciales'           => inicialesDeNombre($usuario['nombre_completo']),
        'estado'              => $usuario['estado'],
        'rol_clave'           => $usuario['rol_clave'],
        'rol_nombre'          => $usuario['rol_nombre'],
        'alcance_global'      => $alcanceGlobal,
        'sucursal_default_id' => $usuario['sucursal_default_id'] !== null ? (int)$usuario['sucursal_default_id'] : null,
        'permisos'            => obtenerPermisosDeUsuario($pdo, $usuario['id']),
        'sucursales'          => array_map(function ($s) {
            return [
                'sucursal_id'              => (int)$s['sucursal_id'],
                'codigo'                   => $s['codigo'],
                'nombre'                   => $s['nombre'],
                'tipo'                     => $s['tipo'],
                'puede_vender'             => (int)$s['puede_vender'] === 1,
                'puede_ajustar_inventario' => (int)$s['puede_ajustar_inventario'] === 1
            ];
        }, $sucursales)
    ];
}

/**
 * Iniciales para el avatar de la barra superior (ej. "Área Administrativa" → "ÁA").
 */
function inicialesDeNombre($nombre) {
    $partes = preg_split('/[\s\/]+/u', trim((string)$nombre), -1, PREG_SPLIT_NO_EMPTY);
    $iniciales = '';
    foreach ($partes as $parte) {
        $letra = mb_substr($parte, 0, 1, 'UTF-8');
        if (preg_match('/\pL/u', $letra)) {
            $iniciales .= mb_strtoupper($letra, 'UTF-8');
        }
        if (mb_strlen($iniciales, 'UTF-8') >= 2) {
            break;
        }
    }
    return $iniciales !== '' ? $iniciales : 'PC';
}

/**
 * Reemplaza por completo la asignación de puntos de venta de un usuario.
 * Es la operación central de la historia "Asignación de Usuarios a Puntos de Venta".
 *
 * @param array $asignaciones [['sucursal_id'=>2,'puede_vender'=>1,'puede_ajustar_inventario'=>1], ...]
 */
function reemplazarSucursalesDeUsuario($pdo, $usuarioId, array $asignaciones, $asignadoPor = null) {
    $usuarioId = (int)$usuarioId;

    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM `usuario_sucursales` WHERE `usuario_id` = ?")->execute([$usuarioId]);

        $stmt = $pdo->prepare("
            INSERT INTO `usuario_sucursales`
                (`usuario_id`, `sucursal_id`, `puede_vender`, `puede_ajustar_inventario`, `asignado_por`)
            VALUES (?, ?, ?, ?, ?)
        ");

        $guardadas = [];
        foreach ($asignaciones as $asignacion) {
            $sucursalId = (int)($asignacion['sucursal_id'] ?? 0);
            if ($sucursalId <= 0 || isset($guardadas[$sucursalId])) {
                continue;
            }
            $guardadas[$sucursalId] = true;
            $stmt->execute([
                $usuarioId,
                $sucursalId,
                !empty($asignacion['puede_vender']) ? 1 : 0,
                !empty($asignacion['puede_ajustar_inventario']) ? 1 : 0,
                $asignadoPor
            ]);
        }

        $pdo->commit();
        return count($guardadas);
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Reemplaza los permisos concedidos a un rol.
 * @param string[] $clavesPermiso
 */
function reemplazarPermisosDeRol($pdo, $rolId, array $clavesPermiso) {
    $rolId = (int)$rolId;

    $pdo->beginTransaction();
    try {
        $pdo->prepare("DELETE FROM `rol_permisos` WHERE `rol_id` = ?")->execute([$rolId]);

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
            SELECT ?, `id` FROM `permisos` WHERE `clave` = ?
        ");
        $total = 0;
        foreach (array_unique($clavesPermiso) as $clave) {
            $stmt->execute([$rolId, $clave]);
            $total += $stmt->rowCount();
        }

        $pdo->commit();
        return $total;
    } catch (PDOException $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Guarda el hash definitivo tras migrar una contraseña semilla a bcrypt.
 */
function actualizarPasswordHash($pdo, $usuarioId, $hash) {
    $stmt = $pdo->prepare("UPDATE `usuarios` SET `password_hash` = ? WHERE `id` = ?");
    return $stmt->execute([$hash, (int)$usuarioId]);
}

/**
 * Marca la fecha del último acceso correcto.
 */
function registrarUltimoAcceso($pdo, $usuarioId) {
    $stmt = $pdo->prepare("UPDATE `usuarios` SET `ultimo_acceso` = NOW() WHERE `id` = ?");
    return $stmt->execute([(int)$usuarioId]);
}

/**
 * Escribe un renglón en la bitácora de accesos.
 * Nunca interrumpe la petición: si la auditoría falla, la operación continúa.
 *
 * @param string $evento login_ok | login_fallido | logout | acceso_denegado
 */
function registrarBitacoraAcceso($pdo, $evento, $usuarioId = null, $usuario = null, $permiso = null, $sucursalId = null, $recurso = null) {
    if (!$pdo || !accesosInstalados($pdo)) {
        return false;
    }
    try {
        $stmt = $pdo->prepare("
            INSERT INTO `bitacora_accesos`
                (`usuario_id`, `usuario`, `evento`, `permiso`, `sucursal_id`, `recurso`, `ip`)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $usuarioId !== null ? (int)$usuarioId : null,
            $usuario,
            $evento,
            $permiso,
            $sucursalId !== null ? (int)$sucursalId : null,
            $recurso ?: ($_SERVER['SCRIPT_NAME'] ?? null),
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (PDOException $e) {
        return false;
    }
}
