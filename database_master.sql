-- =====================================================================
-- PATTERN CARE ERP - SCRIPT MAESTRO DE BASE DE DATOS (UNIFICADO v2)
-- Negocio: Confección Textil y Uniformes Médicos
-- Arquitectura: Multi-Sucursal + Catálogo de Modelos + Variantes (Color/Talla)
-- Accesos: Roles, Permisos y Asignación de Usuarios a Puntos de Venta
-- Compatible con: MySQL 5.7+ / 8.0+ / MariaDB / Servidores IIS + PHP-PDO
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `pattern_care`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `pattern_care`;

-- ---------------------------------------------------------------------
-- Limpieza previa del modulo de accesos (SCRUM-19)
-- Se ejecuta aqui porque estas tablas referencian `sucursales`, que el
-- script vuelve a crear mas abajo: sin estos DROP el script no es re-ejecutable.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `bitacora_accesos`;
DROP TABLE IF EXISTS `usuario_sucursales`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `rol_permisos`;
DROP TABLE IF EXISTS `permisos`;
DROP TABLE IF EXISTS `roles`;
DROP VIEW  IF EXISTS `v_usuarios_accesos`;

-- =====================================================================
-- 1. TABLA: sucursales (Multi-Location / Almacenes de Business Central)
-- =====================================================================
DROP TABLE IF EXISTS `transferencias_stock`;
DROP TABLE IF EXISTS `inventario_sucursales`;
DROP TABLE IF EXISTS `sucursales`;

CREATE TABLE `sucursales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `nombre` VARCHAR(100) NOT NULL,
    `tipo` ENUM('Planta / Taller Central', 'Punto de Venta / Showroom', 'Almacén Digital') NOT NULL,
    `encargado` VARCHAR(100) NOT NULL,
    `telefono` VARCHAR(50) NULL,
    `direccion` VARCHAR(200) NOT NULL,
    `estado` ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sucursal_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ubicaciones de negocio: Planta Matriz, puntos hospitalarios y almacén digital';

-- =====================================================================
-- 2. TABLA: products (Catálogo Maestro de Modelos / Familias de Producto)
-- Representa el "producto base" ANTES de separarlo por talla/color.
-- Cada modelo tiene un Número de Pieza único (ej. GQ-CLA-0001).
-- =====================================================================
DROP TABLE IF EXISTS `product_variants`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `numero_pieza_seq`;
DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
    `id`                INT AUTO_INCREMENT PRIMARY KEY,
    `numero_pieza`      VARCHAR(30) NOT NULL UNIQUE COMMENT 'Auto-generado: CAT-XXX-NNNN (ej. GQ-CLA-0001)',
    `nombre`            VARCHAR(150) NOT NULL,
    `nombre_normalizado` VARCHAR(150) AS (LOWER(TRIM(`nombre`))) STORED COMMENT 'Para deduplicación case-insensitive',
    `categoria`         ENUM('Gorros Quirúrgicos', 'Chamarras Médicas', 'Cubrepolvos', 'Otros') NOT NULL,
    `coleccion`         VARCHAR(100) NOT NULL DEFAULT 'General',
    `descripcion`       TEXT NULL,
    `tipo_tela`         ENUM('SRA', 'CRO', 'Mixta', 'Otra') NOT NULL DEFAULT 'SRA',
    `precio_base`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock_minimo`      INT NOT NULL DEFAULT 6 COMMENT 'Umbral de alerta por sucursal',
    -- Imagen del estampado / modelo (Cloudinary, S3 o ruta local)
    `foto_url`          VARCHAR(500) NULL COMMENT 'URL en Cloudinary / S3 / ruta local',
    `foto_public_id`    VARCHAR(200) NULL COMMENT 'ID público en Cloudinary para gestión',
    -- Metadata
    `activo`            TINYINT(1) NOT NULL DEFAULT 1,
    `notas`             VARCHAR(255) NULL,
    `created_by`        VARCHAR(100) NOT NULL DEFAULT 'Administración',
    `fecha_registro`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Índices
    UNIQUE KEY `uk_nombre_normalizado` (`nombre_normalizado`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_coleccion` (`coleccion`),
    INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo maestro de modelos de prenda médica (antes de variantes)';

-- =====================================================================
-- 3. TABLA: product_variants (Variantes por Color + Talla)
-- Cada fila es un SKU único = Producto + Color + Talla.
-- =====================================================================
CREATE TABLE `product_variants` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `product_id`      INT NOT NULL,
    `sku`             VARCHAR(50) NOT NULL UNIQUE COMMENT 'Generado: NumeroPieza-COLCOD-TALLACOD (ej. GQ-CLA-0001-AZC-U)',
    `color`           VARCHAR(60) NOT NULL,
    `color_codigo`    VARCHAR(10) NULL COMMENT 'Código corto: AZC, VER, NEG...',
    `talla`           VARCHAR(20) NOT NULL,
    `precio_variante` DECIMAL(10,2) NULL COMMENT 'NULL = usa precio_base del producto',
    `activo`          TINYINT(1) NOT NULL DEFAULT 1,
    `notas`           VARCHAR(255) NULL,
    `fecha_registro`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Relación con producto base
    CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Un producto no puede tener dos variantes idénticas (color+talla)
    UNIQUE KEY `uk_product_color_talla` (`product_id`, `color`, `talla`),
    INDEX `idx_variant_sku` (`sku`),
    INDEX `idx_variant_product` (`product_id`),
    INDEX `idx_variant_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Variantes SKU por color y talla de cada producto base';

-- =====================================================================
-- 4. TABLA: product_images (Galería de imágenes adicionales por producto)
-- Almacena fotos de estampados, detalles, empaque, etc.
-- =====================================================================
CREATE TABLE `product_images` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `product_id`   INT NOT NULL,
    `url`          VARCHAR(500) NOT NULL,
    `public_id`    VARCHAR(200) NULL COMMENT 'ID en Cloudinary',
    `tipo`         ENUM('Principal', 'Estampado', 'Detalle', 'Empaque') DEFAULT 'Estampado',
    `orden`        TINYINT NOT NULL DEFAULT 0,
    `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_img_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE CASCADE,
    INDEX `idx_img_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Galería de imágenes de estampados por producto';

-- =====================================================================
-- 5. TABLA: numero_pieza_seq (Secuencias para auto-generar Número de Pieza)
-- Una fila por categoría, permite numeración atómica y única.
-- =====================================================================
CREATE TABLE `numero_pieza_seq` (
    `categoria_codigo` VARCHAR(10) NOT NULL PRIMARY KEY COMMENT 'Ej: GQ, CH, CP, OT',
    `ultimo_numero`    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inicializar secuencias por categoría
INSERT IGNORE INTO `numero_pieza_seq` (`categoria_codigo`, `ultimo_numero`) VALUES
('GQ', 0),  -- Gorros Quirúrgicos
('CH', 0),  -- Chamarras Médicas
('CP', 0),  -- Cubrepolvos
('OT', 0);  -- Otros

-- =====================================================================
-- 6. TABLA: inventario_sucursales (Existencias por Sucursal y Variante)
-- Permite control individual de stock mínimo (< 6) por cada sucursal.
-- =====================================================================
DROP TABLE IF EXISTS `productos_sku`;

CREATE TABLE `inventario_sucursales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `variant_id` INT NOT NULL COMMENT 'FK a product_variants.id',
    `sucursal_id` INT NOT NULL,
    `stock_actual` INT NOT NULL DEFAULT 0,
    `stock_minimo` INT NOT NULL DEFAULT 6,
    `ubicacion_pasillo` VARCHAR(50) NULL,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_variante_sucursal` (`variant_id`, `sucursal_id`),
    INDEX `idx_var_suc` (`variant_id`, `sucursal_id`),
    CONSTRAINT `fk_inv_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Existencias de cada variante en cada sucursal';

-- =====================================================================
-- 7. TABLA: transferencias_stock (Órdenes de Transferencia Inter-Sucursal)
-- =====================================================================
CREATE TABLE `transferencias_stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_transferencia` VARCHAR(50) NOT NULL UNIQUE,
    `sucursal_origen_id` INT NOT NULL,
    `sucursal_destino_id` INT NOT NULL,
    `variant_id` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `estado` ENUM('Pendiente', 'En Tránsito', 'Recibido', 'Cancelado') DEFAULT 'Recibido',
    `responsable` VARCHAR(100) NOT NULL,
    `notas` VARCHAR(255) NULL,
    `fecha_transferencia` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_trf_origen` (`sucursal_origen_id`),
    INDEX `idx_trf_destino` (`sucursal_destino_id`),
    CONSTRAINT `fk_trf_origen` FOREIGN KEY (`sucursal_origen_id`) REFERENCES `sucursales` (`id`),
    CONSTRAINT `fk_trf_destino` FOREIGN KEY (`sucursal_destino_id`) REFERENCES `sucursales` (`id`),
    CONSTRAINT `fk_trf_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Traspasos de stock entre almacenes y puntos de venta';

-- =====================================================================
-- 8. TABLA: produccion_ordenes (Órdenes de Producción en 7 Fases)
-- =====================================================================
CREATE TABLE `produccion_ordenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_orden` VARCHAR(50) NOT NULL UNIQUE,
    `producto` VARCHAR(150) NOT NULL,
    `lote` VARCHAR(50) NOT NULL,
    `cantidad` INT NOT NULL,
    `fase_actual` ENUM('Diseño', 'Abastecimiento', 'Impresión', 'Corte', 'Confección', 'Empaque', 'Distribución') NOT NULL DEFAULT 'Diseño',
    `estado` ENUM('En Proceso', 'Detenido / Alerta', 'Completado') NOT NULL DEFAULT 'En Proceso',
    `responsable` VARCHAR(100) NOT NULL,
    `sucursal_id` INT NULL,
    `notas` TEXT NULL,
    `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fase` (`fase_actual`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Órdenes de confección con seguimiento de las 7 fases operativas';

-- =====================================================================
-- 9. TABLA: mermas_retrabajos (Bitácora de Costos Ocultos y Mermas)
-- =====================================================================
CREATE TABLE `mermas_retrabajos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `descripcion_error` TEXT NOT NULL,
    `tipo` ENUM('Merma', 'Retrabajo') NOT NULL,
    `area` VARCHAR(60) NOT NULL,
    `material_desperdiciado` VARCHAR(120) NOT NULL,
    `costo_impacto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `lote_afectado` VARCHAR(50) NULL,
    `sucursal_id` INT NULL,
    `registrado_por` VARCHAR(100) NOT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_area` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Registro de mermas y retrabajos para visibilizar costos ocultos';

-- =====================================================================
-- 10. TABLA: rendimiento_telas (Simulador de Consumo de Tela SRA y CRO)
-- =====================================================================
CREATE TABLE `rendimiento_telas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fecha` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `tipo_tela` ENUM('SRA', 'CRO') NOT NULL,
    `metros` DECIMAL(8,2) NOT NULL,
    `piezas_estimadas` INT NOT NULL,
    `sobrante_cm` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `lote_referencia` VARCHAR(50) NULL,
    `color` VARCHAR(50) NULL,
    `coleccion` VARCHAR(100) NULL,
    `talla` VARCHAR(20) NULL,
    `notas` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Historial de cálculos de rendimiento de tela';

-- =====================================================================
-- VISTA: v_products_full (Productos con conteo de variantes)
-- Útil para el frontend: lista de modelos con sus SKUs activos.
-- =====================================================================
CREATE OR REPLACE VIEW `v_products_full` AS
SELECT
    p.id,
    p.numero_pieza,
    p.nombre,
    p.categoria,
    p.coleccion,
    p.tipo_tela,
    p.precio_base,
    p.stock_minimo,
    p.foto_url,
    p.foto_public_id,
    p.activo,
    p.notas,
    p.created_by,
    p.fecha_registro,
    COUNT(DISTINCT pv.id) AS total_variantes,
    SUM(CASE WHEN pv.activo = 1 THEN 1 ELSE 0 END) AS variantes_activas
FROM `products` p
LEFT JOIN `product_variants` pv ON pv.product_id = p.id
GROUP BY p.id;

-- =====================================================================
-- PROCEDURE: generar_numero_pieza (Número de Pieza único, atómico)
-- Uso: CALL generar_numero_pieza('Gorros Quirúrgicos', @numero_pieza);
-- Formato de salida: GQ-NUE-0001
-- =====================================================================
DROP PROCEDURE IF EXISTS `generar_numero_pieza`;
DELIMITER $$
CREATE PROCEDURE `generar_numero_pieza`(
    IN  p_categoria VARCHAR(100),
    OUT p_numero    VARCHAR(30)
)
BEGIN
    DECLARE v_codigo  VARCHAR(10);
    DECLARE v_ultimo  INT DEFAULT 0;
    DECLARE v_nuevo   INT;
    DECLARE v_numero  VARCHAR(30);

    -- Mapear categoría a código corto de 2 letras
    SET v_codigo = CASE p_categoria
        WHEN 'Gorros Quirúrgicos' THEN 'GQ'
        WHEN 'Chamarras Médicas'  THEN 'CH'
        WHEN 'Cubrepolvos'        THEN 'CP'
        ELSE                           'OT'
    END;

    -- Actualizar y obtener el siguiente número de forma atómica (lock de fila implícito por UPDATE)
    UPDATE `numero_pieza_seq`
    SET    `ultimo_numero` = `ultimo_numero` + 1
    WHERE  `categoria_codigo` = v_codigo;

    SELECT `ultimo_numero` INTO v_ultimo
    FROM   `numero_pieza_seq`
    WHERE  `categoria_codigo` = v_codigo;

    SET v_nuevo  = v_ultimo;
    SET v_numero = CONCAT(v_codigo, '-NUE-', LPAD(v_nuevo, 4, '0'));

    SET p_numero = v_numero;
END$$
DELIMITER ;

-- =====================================================================
-- 11. TABLA: roles (Perfiles de acceso del equipo Pattern Care)
-- `alcance_global` = 1 significa que el rol ve y opera TODAS las
-- sucursales sin necesidad de asignación individual (Dueña / Dirección).
-- =====================================================================
CREATE TABLE `roles` (
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

-- =====================================================================
-- 12. TABLA: permisos (Acciones atómicas del ERP)
-- La clave sigue el formato `modulo.accion` (ej. inventario.ajustar) y es
-- la misma cadena que consumen el middleware (backend) y las rutas (frontend).
-- =====================================================================
CREATE TABLE `permisos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(60) NOT NULL UNIQUE,
    `modulo` VARCHAR(40) NOT NULL,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` VARCHAR(200) NULL,
    INDEX `idx_permiso_modulo` (`modulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Catálogo de acciones protegidas del sistema';

-- =====================================================================
-- 13. TABLA: rol_permisos (Relación N:M entre roles y permisos)
-- =====================================================================
CREATE TABLE `rol_permisos` (
    `rol_id` INT NOT NULL,
    `permiso_id` INT NOT NULL,
    PRIMARY KEY (`rol_id`, `permiso_id`),
    FOREIGN KEY (`rol_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permiso_id`) REFERENCES `permisos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos concedidos a cada rol';

-- =====================================================================
-- 14. TABLA: usuarios (Equipo de 6 personas + accesos de punto de venta)
-- `password_hash` admite dos formatos:
--   - bcrypt  ($2y$...)  -> formato definitivo generado por PHP password_hash()
--   - SHA-256 (64 hex)   -> formato semilla, se re-hashea a bcrypt en el
--                           primer inicio de sesión correcto.
-- Esto permite sembrar la base sin depender de PHP durante la instalación.
-- =====================================================================
CREATE TABLE `usuarios` (
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

-- =====================================================================
-- 15. TABLA: usuario_sucursales (ASIGNACIÓN A PUNTOS DE VENTA)
-- Tabla central de la historia: define sobre qué sucursales puede operar
-- cada persona. Sin fila aquí (y sin rol de alcance global) el usuario no
-- puede consultar ni mover inventario de esa sucursal.
-- =====================================================================
CREATE TABLE `usuario_sucursales` (
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

-- =====================================================================
-- 16. TABLA: bitacora_accesos (Registro de inicios de sesión y bloqueos)
-- =====================================================================
CREATE TABLE `bitacora_accesos` (
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

-- =====================================================================
-- VISTA: v_usuarios_accesos (Usuario + rol + puntos de venta asignados)
-- Alimenta directamente la pantalla de administración de accesos.
-- =====================================================================
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

-- =====================================================================
-- DATOS SEMILLA (MULTI-SUCURSAL Y OPERATIVO TEXTIL)
-- =====================================================================

-- 1. Sucursales de Pattern Care
INSERT INTO `sucursales` (`id`, `codigo`, `nombre`, `tipo`, `encargado`, `telefono`, `direccion`, `estado`) VALUES
(1, 'MAT-01', 'Matriz & Planta de Confección', 'Planta / Taller Central', 'Dueña / Encargada General', '55-1234-5678', 'Parque Industrial Textil, Nave 4', 'Activo'),
(2, 'SUC-HOSP', 'Sucursal Hospital General', 'Punto de Venta / Showroom', 'Área Administrativa', '55-8765-4321', 'Av. Médicos 405, Frente a Hospital Regional', 'Activo'),
(3, 'SUC-NORTE', 'Sucursal Zona Norte (Clínica Palmas)', 'Punto de Venta / Showroom', 'Corte y Calidad [2]', '55-9988-7766', 'Plaza Médica Las Palmas, Local 12', 'Activo'),
(4, 'ALM-DIG', 'Almacén Digital & Envíos WhatsApp', 'Almacén Digital', 'Área Administrativa', '55-4433-2211', 'Bodega Central de Logística y Paquetería', 'Activo');

-- 2. Productos base (familias de modelo)
INSERT INTO `products`
    (`id`, `numero_pieza`, `nombre`, `categoria`, `coleccion`, `tipo_tela`, `precio_base`, `stock_minimo`, `descripcion`, `foto_url`, `notas`, `created_by`)
VALUES
(1, 'GQ-CLA-0001', 'Gorro Quirúrgico Clásico Antifluido', 'Gorros Quirúrgicos', 'Clásica Hospitalaria', 'SRA', 120.00, 6, 'Gorro quirúrgico clásico, tela antifluido, alta demanda en hospitales', NULL, 'Alta rotación hospitalaria', 'Dueña / Encargada General'),
(2, 'GQ-EST-0002', 'Gorro Quirúrgico Estampado Odonto Smile', 'Gorros Quirúrgicos', 'Odontología Smile', 'SRA', 150.00, 6, 'Gorro quirúrgico con estampado odontológico, impresión en taller externo', NULL, 'Estampado en taller externo', 'Área Administrativa'),
(3, 'GQ-PAS-0003', 'Gorro Quirúrgico Colección Pastel', 'Gorros Quirúrgicos', 'Colección Pastel', 'SRA', 130.00, 6, 'Gorro quirúrgico colores pastel, venta activa por WhatsApp', NULL, 'Venta activa WhatsApp', 'Área Administrativa'),
(4, 'CH-PRE-0001', 'Chamarra Médica Antifluido Stretch Premier', 'Chamarras Médicas', 'Línea Premier', 'CRO', 680.00, 6, 'Chamarra médica antifluido stretch, línea Premier, desabasto de cierres y tela blanca', NULL, 'Desabasto de cierres y tela', 'Dueña / Encargada General'),
(5, 'CH-EST-0002', 'Chamarra Médica Estándar Hospitalaria', 'Chamarras Médicas', 'Línea Estándar', 'CRO', 580.00, 6, 'Chamarra médica estándar de planta hospitalaria', NULL, 'Prenda estándar de planta', 'Dueña / Encargada General'),
(6, 'CP-IMP-0001', 'Cubrepolvo Impermeable con Ajuste', 'Cubrepolvos', 'Protección Total', 'CRO', 320.00, 6, 'Cubrepolvo impermeable con ajuste elástico, alta demanda institucional', NULL, 'Riesgo inminente de agotamiento', 'Área Administrativa'),
(7, 'CP-REP-0002', 'Cubrepolvo Quirúrgico Repelente', 'Cubrepolvos', 'Protección Total', 'CRO', 320.00, 6, 'Cubrepolvo quirúrgico repelente, demanda constante', NULL, 'Demanda constante institucional', 'Área Administrativa');

-- 3. Variantes de cada producto base (SKU = NumeroPieza-COLCOD-TALLACOD)
-- Gorro Clásico
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(1, 'GQ-CLA-0001-AZC-U', 'Azul Cielo',    'AZC', 'Unitalla', 120.00, 1),
(1, 'GQ-CLA-0001-VER-U', 'Verde Quirófano','VER', 'Unitalla', 120.00, 1),
(1, 'GQ-CLA-0001-LIL-U', 'Lila',          'LIL', 'Unitalla', 130.00, 1);
-- Gorro Estampado Odonto
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(2, 'GQ-EST-0002-MUL-U', 'Multicolor',    'MUL', 'Unitalla', 150.00, 1);
-- Gorro Pastel
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(3, 'GQ-PAS-0003-LIL-U', 'Lila',          'LIL', 'Unitalla', 130.00, 1),
(3, 'GQ-PAS-0003-ROS-U', 'Rosa Palo',     'ROS', 'Unitalla', 130.00, 1);
-- Chamarra Premier
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(4, 'CH-PRE-0001-BLA-S', 'Blanco Óptico', 'BLA', 'S', 680.00, 1),
(4, 'CH-PRE-0001-BLA-M', 'Blanco Óptico', 'BLA', 'M', 680.00, 1),
(4, 'CH-PRE-0001-BLA-L', 'Blanco Óptico', 'BLA', 'L', 680.00, 1),
(4, 'CH-PRE-0001-AZM-S', 'Azul Marino',   'AZM', 'S', 680.00, 1),
(4, 'CH-PRE-0001-AZM-M', 'Azul Marino',   'AZM', 'M', 680.00, 1),
(4, 'CH-PRE-0001-AZM-L', 'Azul Marino',   'AZM', 'L', 680.00, 1),
(4, 'CH-PRE-0001-VER-S', 'Verde Quirófano','VER', 'S', 680.00, 1);
-- Chamarra Estándar
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(5, 'CH-EST-0002-AZM-M', 'Azul Marino',   'AZM', 'M', 580.00, 1),
(5, 'CH-EST-0002-AZM-L', 'Azul Marino',   'AZM', 'L', 580.00, 1);
-- Cubrepolvo Impermeable
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(6, 'CP-IMP-0001-NEG-U', 'Negro Oxford',  'NEG', 'Unitalla', 320.00, 1),
(6, 'CP-IMP-0001-AZM-U', 'Azul Marino',   'AZM', 'Unitalla', 320.00, 1);
-- Cubrepolvo Repelente
INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `precio_variante`, `activo`) VALUES
(7, 'CP-REP-0002-AZM-U', 'Azul Marino',   'AZM', 'Unitalla', 320.00, 1),
(7, 'CP-REP-0002-VER-U', 'Verde Quirófano','VER', 'Unitalla', 320.00, 1);

-- Actualizar secuencias basadas en productos existentes
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 3 WHERE `categoria_codigo` = 'GQ';
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 3 WHERE `categoria_codigo` = 'CH';
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 2 WHERE `categoria_codigo` = 'CP';

-- 4. Inventario distribuido por Sucursal y Variante (stock mínimo norma = 6)
-- Nota: Varias sucursales tienen < 6 para detonar alertas críticas.
INSERT INTO `inventario_sucursales` (`variant_id`, `sucursal_id`, `stock_actual`, `stock_minimo`, `ubicacion_pasillo`) VALUES
-- GQ-CLA-0001-AZC-U (variant 1)
(1, 1, 14, 6, 'Estante A-1'),
(1, 2, 2, 6, 'Exhibidor Showroom'),  -- ALERTA CRÍTICA Hospital
(1, 3, 5, 6, 'Mostrador B'),        -- ALERTA Zona Norte
(1, 4, 8, 6, 'Rack Digital 1'),
-- GQ-CLA-0001-VER-U (variant 2)
(2, 1, 12, 6, 'Estante A-1'),
(2, 2, 6, 6, 'Exhibidor Showroom'),
(2, 3, 4, 6, 'Mostrador B'),        -- ALERTA
(2, 4, 10, 6, 'Rack Digital 1'),
-- GQ-CLA-0001-LIL-U (variant 3)
(3, 1, 10, 6, 'Estante A-3'),
(3, 2, 4, 6, 'Exhibidor Showroom'), -- ALERTA
(3, 3, 6, 6, 'Mostrador C'),
(3, 4, 12, 6, 'Rack Digital 1'),
-- GQ-EST-0002-MUL-U (variant 4)
(4, 1, 20, 6, 'Estante A-2'),
(4, 2, 8, 6, 'Exhibidor Showroom'),
(4, 3, 7, 6, 'Mostrador A'),
(4, 4, 12, 6, 'Rack Digital 1'),
-- GQ-PAS-0003-LIL-U (variant 5)
(5, 1, 12, 6, 'Estante A-3'),
(5, 2, 4, 6, 'Exhibidor Showroom'), -- ALERTA
(5, 3, 6, 6, 'Mostrador C'),
(5, 4, 15, 6, 'Rack Digital 1'),
-- GQ-PAS-0003-ROS-U (variant 6)
(6, 1, 8, 6, 'Estante A-3'),
(6, 2, 3, 6, 'Exhibidor Showroom'), -- ALERTA
(6, 3, 5, 6, 'Mostrador C'),        -- ALERTA
(6, 4, 10, 6, 'Rack Digital 1'),
-- CH-PRE-0001-BLA-S (variant 7)
(7, 1, 5, 6, 'Estante B-1'),        -- ALERTA Planta
(7, 2, 2, 6, 'Perchero Premier'),   -- ALERTA
(7, 3, 1, 6, 'Perchero Showroom'),  -- ALERTA CRÍTICA
(7, 4, 3, 6, 'Rack Digital 2'),     -- ALERTA
-- CH-PRE-0001-BLA-M (variant 8)
(8, 1, 15, 6, 'Estante B-2'),
(8, 2, 7, 6, 'Perchero Premier'),
(8, 3, 6, 6, 'Perchero Showroom'),
(8, 4, 9, 6, 'Rack Digital 2'),
-- CH-PRE-0001-BLA-L (variant 9)
(9, 1, 8, 6, 'Estante B-2'),
(9, 2, 4, 6, 'Perchero Premier'),   -- ALERTA
(9, 3, 3, 6, 'Perchero Showroom'),  -- ALERTA
(9, 4, 5, 6, 'Rack Digital 2'),     -- ALERTA
-- CH-PRE-0001-AZM-S (variant 10)
(10, 1, 6, 6, 'Estante B-3'),
(10, 2, 3, 6, 'Perchero Premier'),  -- ALERTA
(10, 3, 4, 6, 'Perchero Showroom'), -- ALERTA
(10, 4, 5, 6, 'Rack Digital 2'),    -- ALERTA
-- CH-PRE-0001-AZM-M (variant 11)
(11, 1, 15, 6, 'Estante B-3'),
(11, 2, 7, 6, 'Perchero Premier'),
(11, 3, 6, 6, 'Perchero Showroom'),
(11, 4, 9, 6, 'Rack Digital 2'),
-- CH-PRE-0001-AZM-L (variant 12)
(12, 1, 10, 6, 'Estante B-3'),
(12, 2, 5, 6, 'Perchero Premier'),  -- ALERTA
(12, 3, 4, 6, 'Perchero Showroom'), -- ALERTA
(12, 4, 7, 6, 'Rack Digital 2'),
-- CH-PRE-0001-VER-S (variant 13)
(13, 1, 7, 6, 'Estante B-4'),
(13, 2, 2, 6, 'Perchero Premier'),  -- ALERTA CRÍTICA
(13, 3, 3, 6, 'Perchero Showroom'), -- ALERTA
(13, 4, 4, 6, 'Rack Digital 2'),    -- ALERTA
-- CH-EST-0002-AZM-M (variant 14)
(14, 1, 12, 6, 'Estante B-5'),
(14, 2, 6, 6, 'Perchero Estándar'),
(14, 3, 4, 6, 'Perchero Showroom'), -- ALERTA
(14, 4, 8, 6, 'Rack Digital 2'),
-- CH-EST-0002-AZM-L (variant 15)
(15, 1, 10, 6, 'Estante B-5'),
(15, 2, 5, 6, 'Perchero Estándar'), -- ALERTA
(15, 3, 3, 6, 'Perchero Showroom'), -- ALERTA
(15, 4, 6, 6, 'Rack Digital 2'),
-- CP-IMP-0001-NEG-U (variant 16)
(16, 1, 6, 6, 'Estante C-1'),
(16, 2, 1, 6, 'Estante Protección'), -- ALERTA CRÍTICA
(16, 3, 2, 6, 'Estante Protección'), -- ALERTA
(16, 4, 3, 6, 'Rack Digital 3'),     -- ALERTA
-- CP-IMP-0001-AZM-U (variant 17)
(17, 1, 18, 6, 'Estante C-2'),
(17, 2, 8, 6, 'Estante Protección'),
(17, 3, 7, 6, 'Estante Protección'),
(17, 4, 10, 6, 'Rack Digital 3'),
-- CP-REP-0002-AZM-U (variant 18)
(18, 1, 14, 6, 'Estante C-3'),
(18, 2, 6, 6, 'Estante Protección'),
(18, 3, 5, 6, 'Estante Protección'), -- ALERTA
(18, 4, 9, 6, 'Rack Digital 3'),
-- CP-REP-0002-VER-U (variant 19)
(19, 1, 10, 6, 'Estante C-4'),
(19, 2, 4, 6, 'Estante Protección'), -- ALERTA
(19, 3, 3, 6, 'Estante Protección'), -- ALERTA
(19, 4, 6, 6, 'Rack Digital 4');

-- 5. Transferencias de Stock Inter-Sucursal
INSERT INTO `transferencias_stock` (`codigo_transferencia`, `sucursal_origen_id`, `sucursal_destino_id`, `variant_id`, `cantidad`, `estado`, `responsable`, `notas`) VALUES
('TRF-2026-001', 1, 2, 1, 10, 'Recibido', 'Área Administrativa', 'Reabastecimiento urgente de Gorro Azul a Sucursal Hospital'),
('TRF-2026-002', 1, 4, 4, 15, 'Recibido', 'Dueña / Encargada General', 'Envío para pedidos de catálogo digital WhatsApp'),
('TRF-2026-003', 1, 3, 11, 6, 'En Tránsito', 'Corte y Calidad [2]', 'Traspaso de Chamarras Azul M por mensajería interna');

-- 6. Órdenes de Producción
INSERT INTO `produccion_ordenes` (`codigo_orden`, `producto`, `lote`, `cantidad`, `fase_actual`, `estado`, `responsable`, `sucursal_id`, `notas`) VALUES
('ORD-2026-001', 'Gorro Quirúrgico Odonto Smile', 'LOT-GQ-01', 120, 'Impresión', 'Detenido / Alerta', 'Área Administrativa', 1, 'Retraso de 48 horas en taller de estampado externo. Cuello de botella detectado.'),
('ORD-2026-002', 'Chamarra Médica Premier Azul Marino', 'LOT-CH-04', 50, 'Confección', 'En Proceso', 'Confección [2]', 1, 'Ensamble de cremalleras y pespuntes dobles en Planta.'),
('ORD-2026-003', 'Cubrepolvo Impermeable Negro', 'LOT-CP-02', 80, 'Corte', 'En Proceso', 'Corte y Calidad [2]', 1, 'Trazado y corte con tela CRO en Planta Central.'),
('ORD-2026-004', 'Gorro Quirúrgico Clásico Azul Cielo', 'LOT-GQ-03', 150, 'Empaque', 'En Proceso', 'Corte y Calidad [2]', 1, 'Inspección de calidad final previa a distribución.');

-- 7. Bitácora de Mermas y Retrabajos
INSERT INTO `mermas_retrabajos` (`fecha`, `descripcion_error`, `tipo`, `area`, `material_desperdiciado`, `costo_impacto`, `lote_afectado`, `sucursal_id`, `registrado_por`) VALUES
('2026-10-08', 'Tono de estampado fuera de escala por falta de muestra física aprobada en taller externo', 'Merma', 'Impresión externa', '18 gorros cortados', 810.00, 'LOT-GQ-01', 1, 'Corte y Calidad [2]'),
('2026-10-10', 'Descuadre en costura de cierre por tensión desajustada de máquina overlock', 'Retrabajo', 'Confección', 'Tiempo de descosido + 3 cierres', 240.00, 'LOT-CH-04', 1, 'Dueña / Encargada General'),
('2026-10-11', 'Error en etiquetado de tallas durante empaque apresurado', 'Retrabajo', 'Empaque', '40 bolsas selladas reabiertas', 120.00, 'LOT-GQ-03', 1, 'Área Administrativa'),
('2026-10-12', 'Melladura en tela al cortar sin fijación adecuada de plantilla', 'Merma', 'Corte', '2.5 metros tela CRO', 450.00, 'LOT-CP-02', 1, 'Corte y Calidad [2]');

-- 8. Rendimientos de Tela (historial de consumo)
INSERT INTO `rendimiento_telas` (`tipo_tela`, `metros`, `piezas_estimadas`, `sobrante_cm`, `lote_referencia`, `color`, `coleccion`, `talla`, `notas`) VALUES
('SRA', 20.00, 120, 0.00, 'LOT-GQ-01', 'Multicolor', 'Odonto Smile', 'Unitalla', '6 piezas exactas por metro en tela SRA'),
('CRO', 20.00, 80, 1000.00, 'LOT-CP-02', 'Negro', 'Protección Total', 'Unitalla', '4 piezas/metro con 50 cm de merma/sobrante por metro (10m total acumulado)');

-- 9. Roles del organigrama de Pattern Care
INSERT INTO `roles` (`clave`, `nombre`, `descripcion`, `alcance_global`) VALUES
('ENCARGADA_GENERAL', 'Dueña / Encargada General', 'Dirección y operaciones. Acceso total a todas las sucursales y a la administración de accesos.', 1),
('ADMINISTRATIVA',    'Área Administrativa',       'Compras, inventario y atención a hospitales en las sucursales que le asignen.', 0),
('CORTE_CALIDAD',     'Corte y Control de Calidad','Órdenes de producción, mermas y rendimiento de tela en su planta.', 0),
('CONFECCION',        'Confección Textil',         'Avance de fases de confección y registro de retrabajos.', 0),
('PUNTO_VENTA',       'Mostrador / Punto de Venta','Venta y ajuste de existencias únicamente en su punto de venta asignado.', 0);

-- 10. Catálogo de permisos (formato modulo.accion)
INSERT INTO `permisos` (`clave`, `modulo`, `nombre`, `descripcion`) VALUES
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

-- 11. Permisos por rol
-- Dirección: acceso total (incluye la administración de accesos)
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r CROSS JOIN `permisos` p
WHERE r.`clave` = 'ENCARGADA_GENERAL';

-- Área Administrativa: operación completa salvo administrar accesos y editar sucursales
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'ADMINISTRATIVA'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar', 'inventario.editar',
                    'transferencias.ver', 'transferencias.crear', 'produccion.ver',
                    'rendimiento.ver', 'rendimiento.registrar', 'mermas.ver', 'mermas.registrar',
                    'sucursales.ver', 'diagnostico.ver', 'reportes.ver',
                    'catalogo.ver', 'catalogo.editar', 'usuarios.ver');

-- Corte y Control de Calidad: producción, mermas y rendimiento de su planta
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'CORTE_CALIDAD'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar',
                    'produccion.ver', 'produccion.avanzar',
                    'rendimiento.ver', 'rendimiento.registrar',
                    'mermas.ver', 'mermas.registrar', 'sucursales.ver', 'catalogo.ver');

-- Confección Textil: avance de fases y registro de retrabajos
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'CONFECCION'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'produccion.ver', 'produccion.avanzar',
                    'mermas.ver', 'mermas.registrar', 'catalogo.ver');

-- Mostrador / Punto de Venta: solo mueve existencias de su punto de venta
INSERT INTO `rol_permisos` (`rol_id`, `permiso_id`)
SELECT r.`id`, p.`id` FROM `roles` r JOIN `permisos` p
WHERE r.`clave` = 'PUNTO_VENTA'
  AND p.`clave` IN ('rolecenter.ver', 'inventario.ver', 'inventario.ajustar',
                    'transferencias.ver', 'sucursales.ver', 'catalogo.ver');

-- 12. Usuarios del equipo (6 personas del organigrama + 1 mostrador)
-- Contraseña inicial de todas las cuentas: PatternCare2026
-- Se siembra como SHA-256 y el login la convierte a bcrypt en el primer acceso.
INSERT INTO `usuarios` (`id`, `usuario`, `nombre_completo`, `correo`, `password_hash`, `rol_id`, `sucursal_default_id`, `estado`) VALUES
(1, 'encargada',     'Dueña / Encargada General', 'direccion@patterncare.mx',   SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'ENCARGADA_GENERAL'), 1, 'Activo'),
(2, 'administracion','Área Administrativa',       'admin@patterncare.mx',       SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'ADMINISTRATIVA'),    2, 'Activo'),
(3, 'corte1',        'Corte y Calidad [1]',       'corte1@patterncare.mx',      SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CORTE_CALIDAD'),     1, 'Activo'),
(4, 'corte2',        'Corte y Calidad [2]',       'corte2@patterncare.mx',      SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CORTE_CALIDAD'),     1, 'Activo'),
(5, 'confeccion1',   'Confección Textil [1]',     'confeccion1@patterncare.mx', SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CONFECCION'),        1, 'Activo'),
(6, 'confeccion2',   'Confección Textil [2]',     'confeccion2@patterncare.mx', SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'CONFECCION'),        1, 'Activo'),
(7, 'pv.hospital',   'Mostrador Hospital General','mostrador@patterncare.mx',   SHA2('PatternCare2026', 256), (SELECT `id` FROM `roles` WHERE `clave` = 'PUNTO_VENTA'),       2, 'Activo');

-- 13. Asignación de usuarios a puntos de venta
-- La Dueña NO lleva filas aquí: su rol tiene alcance global.
-- El resto solo puede operar sobre las sucursales listadas a continuación.
INSERT INTO `usuario_sucursales` (`usuario_id`, `sucursal_id`, `puede_vender`, `puede_ajustar_inventario`, `asignado_por`) VALUES
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
-- FIN DEL SCRIPT MAESTRO
-- =====================================================================
