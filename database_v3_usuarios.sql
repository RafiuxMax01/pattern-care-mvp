-- =====================================================================
-- PATTERN CARE ERP - EXTENSION v3: ROLES, PERMISOS Y PUNTOS DE VENTA
-- Agrega el modelo de accesos (roles, permisos, usuarios y su asignacion
-- a sucursales) sobre una base `pattern_care` ya instalada.
-- Reejecutable: usa CREATE TABLE IF NOT EXISTS e INSERT IGNORE.
-- Compatible con MySQL 5.7+ / 8.0+ / MariaDB
-- =====================================================================

USE `pattern_care`;

-- ---------------------------------------------------------------------
-- TABLA: roles (Perfiles de acceso del equipo Pattern Care)
-- `alcance_global` = 1 significa que el rol ve y opera TODAS las
-- sucursales sin necesidad de asignación individual (Dueña / Dirección).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(40) NOT NULL UNIQUE,
    `nombre` VARCHAR(80) NOT NULL,
    `descripcion` VARCHAR(200) NULL,
    `alcance_global` TINYINT(1) NOT NULL DEFAULT 0,
    `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rol_clave` (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Roles operativos: Dirección, Administrativa, Corte, Confección y Punto de Venta';

-- ---------------------------------------------------------------------
-- TABLA: permisos (Acciones atómicas del ERP)
-- La clave sigue el formato `modulo.accion` (ej. inventario.ajustar) y es
-- la misma cadena que consumen el middleware (backend) y las rutas (frontend).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permisos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(60) NOT NULL UNIQUE,
    `modulo` VARCHAR(40) NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` VARCHAR(200) NULL,
    INDEX `idx_permiso_modulo` (`modulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de acciones protegidas del sistema';

-- ---------------------------------------------------------------------
-- TABLA: rol_permisos (Relación N:M entre roles y permisos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rol_permisos` (
    `rol_id` INT NOT NULL,
    `permiso_id` INT NOT NULL,
    PRIMARY KEY (`rol_id`, `permiso_id`),
    FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permiso_id`) REFERENCES `permisos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos concedidos a cada rol';

-- ---------------------------------------------------------------------
-- TABLA: usuarios (Equipo de 6 personas + accesos de punto de venta)
-- `password_hash` admite dos formatos:
--   - bcrypt  ($2y$...)  -> formato definitivo generado por PHP password_hash()
--   - SHA-256 (64 hex)   -> formato semilla, se re-hashea a bcrypt en el
--                           primer inicio de sesión correcto.
-- Esto permite sembrar la base sin depender de PHP durante la instalación.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(40) NOT NULL UNIQUE,
    `nombre_completo` VARCHAR(100) NOT NULL,
    `correo` VARCHAR(120) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol_id` INT NOT NULL,
    `sucursal_default_id` INT NULL,
    `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    `ultimo_acceso` DATETIME NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`),
    FOREIGN KEY (`sucursal_default_id`) REFERENCES `sucursales`(`id`) ON DELETE SET NULL,
    INDEX `idx_usuario_login` (`usuario`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cuentas del personal con su rol y punto de venta por defecto';

-- ---------------------------------------------------------------------
-- TABLA: usuario_sucursales (ASIGNACIÓN A PUNTOS DE VENTA)
-- Tabla central de la historia: define sobre qué sucursales puede operar
-- cada persona. Sin fila aquí (y sin rol de alcance global) el usuario no
-- puede consultar ni mover inventario de esa sucursal.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuario_sucursales` (
    `usuario_id` INT NOT NULL,
    `sucursal_id` INT NOT NULL,
    `puede_vender` TINYINT(1) NOT NULL DEFAULT 1,
    `puede_ajustar_inventario` TINYINT(1) NOT NULL DEFAULT 0,
    `asignado_por` VARCHAR(100) NULL,
    `fecha_asignacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`usuario_id`, `sucursal_id`),
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales`(`id`) ON DELETE CASCADE,
    INDEX `idx_asignacion_sucursal` (`sucursal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Qué usuario puede operar en qué punto de venta / almacén';

-- ---------------------------------------------------------------------
-- TABLA: bitacora_accesos (Registro de inicios de sesión y bloqueos)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bitacora_accesos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `usuario` VARCHAR(40) NULL,
    `evento` ENUM('login_ok', 'login_fallido', 'logout', 'acceso_denegado') NOT NULL,
    `permiso` VARCHAR(60) NULL,
    `sucursal_id` INT NULL,
    `recurso` VARCHAR(120) NULL,
    `ip` VARCHAR(45) NULL,
    `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bitacora_fecha` (`fecha`),
    INDEX `idx_bitacora_evento` (`evento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Auditoría de accesos y de intentos bloqueados por el middleware';

-- ---------------------------------------------------------------------
-- VISTA: v_usuarios_accesos (Usuario + rol + puntos de venta asignados)
-- Alimenta directamente la pantalla de administración de accesos.
-- ---------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_usuarios_accesos` AS
SELECT
    u.`id`,
    u.`usuario`,
    u.`nombre_completo`,
    u.`correo`,
    u.`estado`,
    u.`ultimo_acceso`,
    r.`clave`           AS `rol_clave`,
    r.`nombre`          AS `rol_nombre`,
    r.`alcance_global`,
    s.`nombre`          AS `sucursal_default`,
    COUNT(us.`sucursal_id`) AS `total_sucursales`,
    GROUP_CONCAT(suc.`nombre` ORDER BY suc.`id` SEPARATOR ' | ') AS `sucursales_asignadas`
FROM `usuarios` u
INNER JOIN `roles` r              ON r.`id` = u.`rol_id`
LEFT JOIN `sucursales` s          ON s.`id` = u.`sucursal_default_id`
LEFT JOIN `usuario_sucursales` us ON us.`usuario_id` = u.`id`
LEFT JOIN `sucursales` suc        ON suc.`id` = us.`sucursal_id`
GROUP BY u.`id`, u.`usuario`, u.`nombre_completo`, u.`correo`, u.`estado`,
         u.`ultimo_acceso`, r.`clave`, r.`nombre`, r.`alcance_global`, s.`nombre`;


-- ---------------------------------------------------------------------
-- DATOS SEMILLA DEL MODULO DE ACCESOS
-- ---------------------------------------------------------------------

-- Roles del organigrama de Pattern Care
INSERT IGNORE INTO `roles` (`clave`, `nombre`, `descripcion`, `alcance_global`) VALUES
('ENCARGADA_GENERAL', 'Dueña / Encargada General', 'Dirección y operaciones. Acceso total a todas las sucursales y a la administración de accesos.', 1),
('ADMINISTRATIVA',    'Área Administrativa',       'Compras, inventario y atención a hospitales en las sucursales que le asignen.', 0),
('CORTE_CALIDAD',     'Corte y Control de Calidad','Órdenes de producción, mermas y rendimiento de tela en su planta.', 0),
('CONFECCION',        'Confección Textil',         'Avance de fases de confección y registro de retrabajos.', 0),
('PUNTO_VENTA',       'Mostrador / Punto de Venta','Venta y ajuste de existencias únicamente en su punto de venta asignado.', 0);

-- Catálogo de permisos (formato modulo.accion)
INSERT IGNORE INTO `permisos` (`clave`, `modulo`, `nombre`, `descripcion`) VALUES
('rolecenter.ver',        'rolecenter',     'Ver Role Center',              'Consultar el resumen ejecutivo y los KPIs'),
('inventario.ver',        'inventario',     'Ver inventario',               'Consultar existencias por sucursal'),
('inventario.ajustar',    'inventario',     'Ajustar existencias',          'Sumar o restar piezas del inventario de una sucursal'),
('inventario.editar',     'inventario',     'Editar SKU',                   'Alta, edición y baja de SKUs en inventario'),
('transferencias.ver',    'transferencias', 'Ver transferencias',           'Consultar traspasos entre sucursales'),
('transferencias.crear',  'transferencias', 'Crear transferencias',         'Generar órdenes de traspaso inter-sucursal'),
('produccion.ver',        'produccion',     'Ver órdenes de producción',    'Consultar el tablero de las 7 fases'),
('produccion.avanzar',    'produccion',     'Avanzar producción',           'Mover órdenes de fase y cambiar su estado'),
('rendimiento.ver',       'rendimiento',    'Ver rendimiento de tela',      'Consultar el historial del simulador SRA / CRO'),
('rendimiento.registrar', 'rendimiento',    'Registrar rendimiento',        'Guardar cálculos de consumo de tela'),
('mermas.ver',            'mermas',         'Ver bitácora de mermas',       'Consultar mermas y retrabajos con su costeo'),
('mermas.registrar',      'mermas',         'Registrar mermas',             'Capturar mermas y retrabajos'),
('sucursales.ver',        'sucursales',     'Ver sucursales',               'Consultar la ficha de los puntos de venta'),
('sucursales.editar',     'sucursales',     'Editar sucursales',            'Alta y edición de puntos de venta'),
('sucursales.todas',      'sucursales',     'Ver consolidado global',       'Operar sobre todas las sucursales sin asignación individual'),
('diagnostico.ver',       'diagnostico',    'Ver diagnóstico',              'Consultar la matriz de problemas críticos'),
('reportes.ver',          'reportes',       'Ver reportes',                 'Generar el plan de acción y reportes ejecutivos'),
('catalogo.ver',          'catalogo',       'Ver catálogo',                 'Consultar modelos y variantes de producto'),
('catalogo.editar',       'catalogo',       'Editar catálogo',              'Alta y edición de modelos, variantes y fotos'),
('usuarios.ver',          'usuarios',       'Ver usuarios y accesos',       'Consultar el padrón de usuarios y sus asignaciones'),
('usuarios.gestionar',    'usuarios',       'Gestionar usuarios y accesos', 'Crear usuarios, cambiar roles y asignar puntos de venta');

-- Permisos por rol
-- Dirección: acceso total (incluye la administración de accesos)
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r CROSS JOIN `permisos` p
WHERE r.`clave` = 'ENCARGADA_GENERAL';

-- Área Administrativa: operación completa salvo administrar accesos y editar sucursales
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'ADMINISTRATIVA'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar', 'inventario.editar',
                    'transferencias.ver', 'transferencias.crear', 'produccion.ver',
                    'rendimiento.ver', 'rendimiento.registrar', 'mermas.ver', 'mermas.registrar',
                    'sucursales.ver', 'diagnostico.ver', 'reportes.ver',
                    'catalogo.ver', 'catalogo.editar', 'usuarios.ver');

-- Corte y Control de Calidad: producción, mermas y rendimiento de su planta
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'CORTE_CALIDAD'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar',
                    'produccion.ver', 'produccion.avanzar',
                    'rendimiento.ver', 'rendimiento.registrar',
                    'mermas.ver', 'mermas.registrar', 'sucursales.ver', 'catalogo.ver');

-- Confección Textil: avance de fases y registro de retrabajos
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'CONFECCION'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'produccion.ver', 'produccion.avanzar',
                    'mermas.ver', 'mermas.registrar', 'catalogo.ver');

-- Mostrador / Punto de Venta: solo mueve existencias de su punto de venta
INSERT IGNORE INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'PUNTO_VENTA'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar',
                    'transferencias.ver', 'sucursales.ver', 'catalogo.ver');

-- Usuarios del equipo (6 personas del organigrama + 1 mostrador)
-- Contraseña inicial de todas las cuentas: PatternCare2026
-- Se siembra como SHA-256 y el login la convierte a bcrypt en el primer acceso.
INSERT IGNORE INTO `usuarios` (`id`, `usuario`, `nombre_completo`, `correo`, `password_hash`, `rol_id`, `sucursal_default_id`, `estado`) VALUES
(1, 'encargada',     'Dueña / Encargada General', 'direccion@patterncare.mx',   SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'ENCARGADA_GENERAL'), 1, 'Activo'),
(2, 'administracion','Área Administrativa',       'admin@patterncare.mx',       SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'ADMINISTRATIVA'),    2, 'Activo'),
(3, 'corte1',        'Corte y Calidad [1]',       'corte1@patterncare.mx',      SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CORTE_CALIDAD'),     1, 'Activo'),
(4, 'corte2',        'Corte y Calidad [2]',       'corte2@patterncare.mx',      SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CORTE_CALIDAD'),     1, 'Activo'),
(5, 'confeccion1',   'Confección Textil [1]',     'confeccion1@patterncare.mx', SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CONFECCION'),        1, 'Activo'),
(6, 'confeccion2',   'Confección Textil [2]',     'confeccion2@patterncare.mx', SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CONFECCION'),        1, 'Activo'),
(7, 'pv.hospital',   'Mostrador Hospital General','mostrador@patterncare.mx',   SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'PUNTO_VENTA'),       2, 'Activo');

-- Asignación de usuarios a puntos de venta
-- La Dueña NO lleva filas aquí: su rol tiene alcance global.
-- El resto solo puede operar sobre las sucursales listadas a continuación.
INSERT IGNORE INTO `usuario_sucursales` (`usuario_id`, `sucursal_id`, `puede_vender`, `puede_ajustar_inventario`, `asignado_por`) VALUES
-- Área Administrativa: matriz, hospital y almacén digital
(2, 1, 0, 1, 'Dueña / Encargada General'),
(2, 2, 1, 1, 'Dueña / Encargada General'),
(2, 4, 1, 1, 'Dueña / Encargada General'),
-- Corte y Calidad [1]: solo planta matriz
(3, 1, 0, 1, 'Dueña / Encargada General'),
-- Corte y Calidad [2]: planta matriz y apoyo en Zona Norte
(4, 1, 0, 1, 'Dueña / Encargada General'),
(4, 3, 0, 1, 'Dueña / Encargada General'),
-- Confección Textil: solo planta matriz, sin ajuste de existencias
(5, 1, 0, 0, 'Dueña / Encargada General'),
(6, 1, 0, 0, 'Dueña / Encargada General'),
-- Mostrador Hospital General: únicamente su punto de venta
(7, 2, 1, 1, 'Dueña / Encargada General');

-- =====================================================================
-- FIN DE LA EXTENSION v3
-- Usuarios sembrados (contrasena inicial: PatternCare2026):
--   encargada / administracion / corte1 / corte2 / confeccion1 /
--   confeccion2 / pv.hospital
-- =====================================================================
