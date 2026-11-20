-- =====================================================================
-- PATTERN CARE ERP - EXTENSIÓN v2: CATÁLOGO DE PRODUCTOS CON VARIANTES
-- Agrega las tablas products y product_variants sobre el esquema base
-- Compatible con MySQL 5.7+ / 8.0+ / MariaDB
-- =====================================================================

USE `pattern_care`;

-- ---------------------------------------------------------------------
-- TABLA: products (Catálogo Maestro de Modelos / Familias de Producto)
-- Representa el "producto base" antes de separarlo por talla/color
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `numero_pieza`    VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Auto-generado: CAT-XXX-NNNN (ej. GQ-CLA-0001)',
    `nombre`          VARCHAR(150) NOT NULL,
    `nombre_normalizado` VARCHAR(150) AS (LOWER(TRIM(`nombre`))) STORED COMMENT 'Para deduplicación case-insensitive',
    `categoria`       ENUM('Gorros Quirúrgicos','Chamarras Médicas','Cubrepolvos','Otros') NOT NULL,
    `coleccion`       VARCHAR(100) NOT NULL DEFAULT 'General',
    `descripcion`     TEXT NULL,
    `tipo_tela`       ENUM('SRA','CRO','Mixta','Otra') NOT NULL DEFAULT 'SRA',
    `precio_base`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock_minimo`    INT NOT NULL DEFAULT 6 COMMENT 'Umbral de alerta por sucursal',
    -- Imagen del estampado / modelo
    `foto_url`        VARCHAR(500) NULL COMMENT 'URL en Cloudinary / S3 / ruta local',
    `foto_public_id`  VARCHAR(200) NULL COMMENT 'ID público en Cloudinary para gestión',
    -- Metadata
    `activo`          TINYINT(1) NOT NULL DEFAULT 1,
    `notas`           VARCHAR(255) NULL,
    `created_by`      VARCHAR(100) NOT NULL DEFAULT 'Administración',
    `fecha_registro`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    -- Índices
    UNIQUE KEY `uk_nombre_normalizado` (`nombre_normalizado`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_coleccion` (`coleccion`),
    INDEX `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo maestro de modelos de prenda médica';

-- ---------------------------------------------------------------------
-- TABLA: product_variants (Variantes por Color + Talla)
-- Cada fila es un SKU único = Producto + Color + Talla
-- Se vincula con productos_sku para compatibilidad hacia atrás
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id`              INT AUTO_INCREMENT PRIMARY KEY,
    `product_id`      INT NOT NULL,
    `sku`             VARCHAR(50) NOT NULL UNIQUE COMMENT 'Generado: NP-COL-TALLA (ej. GQ-CLA-0001-AZC-U)',
    `color`           VARCHAR(60) NOT NULL,
    `color_codigo`    VARCHAR(10) NULL COMMENT 'Código hex o código corto: AZC, VER, NEG…',
    `talla`           VARCHAR(20) NOT NULL,
    `precio_variante` DECIMAL(10,2) NULL COMMENT 'NULL = usa precio_base del producto',
    `activo`          TINYINT(1) NOT NULL DEFAULT 1,
    `notas`           VARCHAR(255) NULL,
    `fecha_registro`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- Relación con producto base
    CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    -- Un producto no puede tener dos variantes idénticas
    UNIQUE KEY `uk_product_color_talla` (`product_id`, `color`, `talla`),
    INDEX `idx_variant_sku` (`sku`),
    INDEX `idx_variant_product` (`product_id`),
    INDEX `idx_variant_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Variantes SKU por color y talla de cada producto base';

-- ---------------------------------------------------------------------
-- TABLA: product_images (Galería de imágenes adicionales por producto)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_images` (
    `id`           INT AUTO_INCREMENT PRIMARY KEY,
    `product_id`   INT NOT NULL,
    `url`          VARCHAR(500) NOT NULL,
    `public_id`    VARCHAR(200) NULL COMMENT 'ID en Cloudinary',
    `tipo`         ENUM('Principal','Estampado','Detalle','Empaque') DEFAULT 'Estampado',
    `orden`        TINYINT NOT NULL DEFAULT 0,
    `fecha_subida` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_img_product` FOREIGN KEY (`product_id`)
        REFERENCES `products` (`id`) ON DELETE CASCADE,
    INDEX `idx_img_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Galería de imágenes de estampados por producto';

-- ---------------------------------------------------------------------
-- SECUENCIAS DE NÚMERO DE PIEZA (tabla auxiliar para auto-increment por categoría)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `numero_pieza_seq` (
    `categoria_codigo` VARCHAR(10) NOT NULL PRIMARY KEY COMMENT 'Ej: GQ, CH, CP, OT',
    `ultimo_numero`    INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inicializar secuencias por categoría
INSERT IGNORE INTO `numero_pieza_seq` (`categoria_codigo`, `ultimo_numero`) VALUES
('GQ', 0),  -- Gorros Quirúrgicos
('CH', 0),  -- Chamarras Médicas
('CP', 0),  -- Cubrepolvos
('OT', 0);  -- Otros

-- ---------------------------------------------------------------------
-- DATOS SEMILLA: Migrar productos existentes al nuevo catálogo
-- ---------------------------------------------------------------------

-- Actualizar secuencias basadas en productos existentes
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 3 WHERE `categoria_codigo` = 'GQ';
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 3 WHERE `categoria_codigo` = 'CH';
UPDATE `numero_pieza_seq` SET `ultimo_numero` = 2 WHERE `categoria_codigo` = 'CP';

-- Insertar productos base (familias)
INSERT IGNORE INTO `products` 
    (`id`, `numero_pieza`, `nombre`, `categoria`, `coleccion`, `tipo_tela`, `precio_base`, `stock_minimo`, `notas`, `created_by`)
VALUES
(1, 'GQ-CLA-0001', 'Gorro Quirúrgico Clásico Antifluido', 'Gorros Quirúrgicos', 'Clásica Hospitalaria', 'SRA', 120.00, 6, 'Alta demanda en hospitales', 'Dueña / Encargada General'),
(2, 'GQ-EST-0002', 'Gorro Quirúrgico Estampado Odonto Smile', 'Gorros Quirúrgicos', 'Odontología Smile', 'SRA', 150.00, 6, 'Estampado en taller externo', 'Área Administrativa'),
(3, 'GQ-PAS-0003', 'Gorro Quirúrgico Colección Pastel', 'Gorros Quirúrgicos', 'Colección Pastel', 'SRA', 130.00, 6, 'Venta activa por WhatsApp', 'Área Administrativa'),
(4, 'CH-PRE-0001', 'Chamarra Médica Antifluido Stretch Premier', 'Chamarras Médicas', 'Línea Premier', 'CRO', 680.00, 6, 'Desabasto de cierres y tela', 'Dueña / Encargada General'),
(5, 'CH-EST-0002', 'Chamarra Médica Estándar Hospitalaria', 'Chamarras Médicas', 'Línea Estándar', 'CRO', 580.00, 6, 'Prenda estándar de planta', 'Dueña / Encargada General'),
(6, 'CP-IMP-0001', 'Cubrepolvo Impermeable con Ajuste', 'Cubrepolvos', 'Protección Total', 'CRO', 320.00, 6, 'Alta demanda institucional', 'Área Administrativa'),
(7, 'CP-REP-0002', 'Cubrepolvo Quirúrgico Repelente', 'Cubrepolvos', 'Protección Total', 'CRO', 320.00, 6, 'Demanda constante', 'Área Administrativa');

-- Insertar variantes de cada producto base
INSERT IGNORE INTO `product_variants` (`product_id`, `sku`, `color`, `color_codigo`, `talla`, `activo`) VALUES
-- Gorro Clásico
(1, 'GQ-CLA-0001-AZC-U', 'Azul Cielo',    'AZC', 'Unitalla', 1),
(1, 'GQ-CLA-0001-VER-U', 'Verde Quirófano','VER', 'Unitalla', 1),
(1, 'GQ-CLA-0001-LIL-U', 'Lila',          'LIL', 'Unitalla', 1),
-- Gorro Estampado Odonto
(2, 'GQ-EST-0002-MUL-U', 'Multicolor',    'MUL', 'Unitalla', 1),
-- Gorro Pastel
(3, 'GQ-PAS-0003-LIL-U', 'Lila',          'LIL', 'Unitalla', 1),
(3, 'GQ-PAS-0003-ROS-U', 'Rosa Palo',     'ROS', 'Unitalla', 1),
-- Chamarra Premier
(4, 'CH-PRE-0001-BLA-S', 'Blanco Óptico', 'BLA', 'S', 1),
(4, 'CH-PRE-0001-BLA-M', 'Blanco Óptico', 'BLA', 'M', 1),
(4, 'CH-PRE-0001-BLA-L', 'Blanco Óptico', 'BLA', 'L', 1),
(4, 'CH-PRE-0001-AZM-S', 'Azul Marino',   'AZM', 'S', 1),
(4, 'CH-PRE-0001-AZM-M', 'Azul Marino',   'AZM', 'M', 1),
(4, 'CH-PRE-0001-AZM-L', 'Azul Marino',   'AZM', 'L', 1),
(4, 'CH-PRE-0001-VER-S', 'Verde Quirófano','VER', 'S', 1),
-- Chamarra Estándar
(5, 'CH-EST-0002-AZM-M', 'Azul Marino',   'AZM', 'M', 1),
(5, 'CH-EST-0002-AZM-L', 'Azul Marino',   'AZM', 'L', 1),
-- Cubrepolvo Impermeable
(6, 'CP-IMP-0001-NEG-U', 'Negro Oxford',  'NEG', 'Unitalla', 1),
(6, 'CP-IMP-0001-AZM-U', 'Azul Marino',   'AZM', 'Unitalla', 1),
-- Cubrepolvo Repelente
(7, 'CP-REP-0002-AZM-U', 'Azul Marino',   'AZM', 'Unitalla', 1),
(7, 'CP-REP-0002-VER-U', 'Verde Quirófano','VER', 'Unitalla', 1);

-- ---------------------------------------------------------------------
-- STORED PROCEDURE: Generar Número de Pieza único con lock atómico
-- Uso: CALL generar_numero_pieza('Gorros Quirúrgicos', @numero_pieza);
-- ---------------------------------------------------------------------
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

    -- Mapear categoría a código corto
    SET v_codigo = CASE p_categoria
        WHEN 'Gorros Quirúrgicos' THEN 'GQ'
        WHEN 'Chamarras Médicas'  THEN 'CH'
        WHEN 'Cubrepolvos'        THEN 'CP'
        ELSE                           'OT'
    END;

    -- Actualizar y obtener el siguiente número de forma atómica
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

-- Vista útil para el frontend: productos con todas sus variantes en JSON
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
    p.activo,
    p.notas,
    p.fecha_registro,
    COUNT(DISTINCT pv.id) AS total_variantes,
    SUM(CASE WHEN pv.activo = 1 THEN 1 ELSE 0 END) AS variantes_activas
FROM `products` p
LEFT JOIN `product_variants` pv ON pv.product_id = p.id
GROUP BY p.id;
