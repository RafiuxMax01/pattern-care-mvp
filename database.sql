-- =====================================================================
-- PATTERN CARE - SISTEMA DE GESTIÓN OPERATIVA, PRODUCCIÓN E INVENTARIOS
-- Script de Creación de Base de Datos y Datos Semilla
-- Compatible con MySQL 5.7+ / MySQL 8.0+ / MariaDB / Servidores IIS & XAMPP
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `pattern_care` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pattern_care`;

-- ---------------------------------------------------------------------
-- 1. TABLA: productos_sku
-- Parámetros de inventario con control de stock mínimo general (6 piezas)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `productos_sku`;
CREATE TABLE `productos_sku` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `nombre` VARCHAR(150) NOT NULL,
    `categoria` ENUM('Gorros Quirúrgicos', 'Chamarras Médicas', 'Cubrepolvos', 'Otros') NOT NULL,
    `color` VARCHAR(50) NOT NULL,
    `talla` VARCHAR(20) NOT NULL,
    `coleccion` VARCHAR(100) NOT NULL,
    `stock_actual` INT NOT NULL DEFAULT 0,
    `stock_minimo` INT NOT NULL DEFAULT 6,
    `notas` VARCHAR(255) NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_categoria` (`categoria`),
    INDEX `idx_stock` (`stock_actual`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TABLA: produccion_ordenes
-- Seguimiento de fases de producción y responsables de la organización
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `produccion_ordenes`;
CREATE TABLE `produccion_ordenes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_orden` VARCHAR(50) NOT NULL UNIQUE,
    `producto` VARCHAR(150) NOT NULL,
    `lote` VARCHAR(50) NOT NULL,
    `cantidad` INT NOT NULL,
    `fase_actual` ENUM('Diseño', 'Abastecimiento', 'Impresión', 'Corte', 'Confección', 'Empaque', 'Distribución') NOT NULL DEFAULT 'Diseño',
    `estado` ENUM('En Proceso', 'Detenido / Alerta', 'Completado') NOT NULL DEFAULT 'En Proceso',
    `responsable` VARCHAR(100) NOT NULL,
    `notas` TEXT NULL,
    `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fase` (`fase_actual`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_lote` (`lote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABLA: mermas_retrabajos
-- Bitácora para mitigar el punto crítico de mermas ocultas y retrabajos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `mermas_retrabajos`;
CREATE TABLE `mermas_retrabajos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fecha` DATE NOT NULL,
    `descripcion_error` TEXT NOT NULL,
    `tipo` ENUM('Merma', 'Retrabajo') NOT NULL,
    `area` VARCHAR(60) NOT NULL,
    `material_desperdiciado` VARCHAR(120) NOT NULL,
    `costo_impacto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `lote_afectado` VARCHAR(50) NULL,
    `registrado_por` VARCHAR(100) NOT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_area` (`area`),
    INDEX `idx_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABLA: rendimiento_telas
-- Registro de cálculos de rendimiento (Tela SRA: 6 pzas/m, CRO: 4 pzas/m + 50cm)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `rendimiento_telas`;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- INSERCIÓN DE DATOS SEMILLA (REALISTAS PARA EL DIAGNÓSTICO)
-- ---------------------------------------------------------------------

-- Datos de SKUs iniciales (Gorros, Chamarras, Cubrepolvos)
-- Nota: Stock Mínimo es 6 piezas. Varios items están intencionalmente < 6 para detonar alertas.
INSERT INTO `productos_sku` (`sku`, `nombre`, `categoria`, `color`, `talla`, `coleccion`, `stock_actual`, `stock_minimo`, `notas`) VALUES
('GQ-AZC-U', 'Gorro Quirúrgico Clásico Antifluido', 'Gorros Quirúrgicos', 'Azul Cielo', 'Unitalla', 'Clásica Hospitalaria', 4, 6, 'Stock crítico por alta demanda en Hospital General'),
('GQ-EST-OD', 'Gorro Quirúrgico Estampado Odonto', 'Gorros Quirúrgicos', 'Multicolor', 'Unitalla', 'Odontología Smile', 14, 6, 'Lote reciente con estampado externo'),
('GQ-LIL-U', 'Gorro Quirúrgico Quirófano Pastel', 'Gorros Quirúrgicos', 'Lila', 'Unitalla', 'Colección Pastel', 11, 6, 'Buena rotación en venta en línea'),
('CH-BLA-M', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Blanco Óptico', 'M', 'Línea Premier', 3, 6, 'Desabasto de insumos (cierres y tela blanca)'),
('CH-AZM-L', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Azul Marino', 'L', 'Línea Premier', 8, 6, 'Stock estable'),
('CH-VER-S', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Verde Quirófano', 'S', 'Línea Premier', 5, 6, 'Bajo nivel de inventario'),
('CP-NEG-U', 'Cubrepolvo Impermeable con Ajuste', 'Cubrepolvos', 'Negro Oxford', 'Unitalla', 'Protección Total', 2, 6, 'Riesgo inminente de agotamiento'),
('CP-AZM-U', 'Cubrepolvo Quirúrgico Repelente', 'Cubrepolvos', 'Azul Marino', 'Unitalla', 'Protección Total', 9, 6, 'Demanda constante en canal directo');

-- Datos de Órdenes de Producción iniciales
INSERT INTO `produccion_ordenes` (`codigo_orden`, `producto`, `lote`, `cantidad`, `fase_actual`, `estado`, `responsable`, `notas`) VALUES
('ORD-2026-001', 'Gorro Quirúrgico Odonto Smile', 'LOT-GQ-01', 120, 'Impresión', 'Detenido / Alerta', 'Área Administrativa', 'Retraso de 48 horas en taller de estampado externo. Cuello de botella detectado.'),
('ORD-2026-002', 'Chamarra Médica Premier Azul Marino', 'LOT-CH-04', 50, 'Confección', 'En Proceso', 'Confección [2]', 'Ensamble de cremalleras y pespuntes dobles.'),
('ORD-2026-003', 'Cubrepolvo Impermeable Negro', 'LOT-CP-02', 80, 'Corte', 'En Proceso', 'Corte y Calidad [2]', 'Trazado y corte con tela CRO.'),
('ORD-2026-004', 'Gorro Quirúrgico Clásico Azul Cielo', 'LOT-GQ-03', 150, 'Empaque', 'En Proceso', 'Corte y Calidad [2]', 'Control de calidad final y etiquetado para entrega directa a Hospital.');

-- Datos de Bitácora de Mermas y Retrabajos
INSERT INTO `mermas_retrabajos` (`fecha`, `descripcion_error`, `tipo`, `area`, `material_desperdiciado`, `costo_impacto`, `lote_afectado`, `registrado_por`) VALUES
('2026-10-08', 'Tono de estampado fuera de escala por falta de muestra física aprobada en taller externo', 'Merma', 'Impresión externa', '18 gorros cortados', 810.00, 'LOT-GQ-01', 'Corte y Calidad [2]'),
('2026-10-10', 'Descuadre en costura de cierre por tensión desajustada de máquina overlock', 'Retrabajo', 'Confección', 'Tiempo de descosido + 3 cierres', 240.00, 'LOT-CH-04', 'Dueña/Encargada General'),
('2026-10-11', 'Error en etiquetado de tallas durante empaque apresurado', 'Retrabajo', 'Empaque', '40 bolsas selladas reabiertas', 120.00, 'LOT-GQ-03', 'Área Administrativa'),
('2026-10-12', 'Melladura en tela al cortar sin fijación adecuada de plantilla', 'Merma', 'Corte', '2.5 metros tela CRO', 450.00, 'LOT-CP-02', 'Corte y Calidad [2]');

-- Datos de Simulaciones de Rendimiento de Tela previas
INSERT INTO `rendimiento_telas` (`tipo_tela`, `metros`, `piezas_estimadas`, `sobrante_cm`, `lote_referencia`, `color`, `coleccion`, `talla`, `notas`) VALUES
('SRA', 20.00, 120, 0.00, 'LOT-GQ-01', 'Multicolor', 'Odonto Smile', 'Unitalla', '6 piezas exactas por metro en tela SRA'),
('CRO', 20.00, 80, 1000.00, 'LOT-CP-02', 'Negro', 'Protección Total', 'Unitalla', '4 piezas/metro con 50 cm de merma/sobrante por metro (10m total acumulado)');
