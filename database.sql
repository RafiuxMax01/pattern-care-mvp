-- =====================================================================
-- DYNAMICS 365 BUSINESS CENTRAL ERP (MVP) - PATTERN CARE
-- MÓDULO DE CONTROL MULTI-SUCURSAL, PRODUCCIÓN, RENDIMIENTO E INVENTARIOS
-- Compatible con MySQL 5.7+ / 8.0+ / MariaDB / Servidores IIS & XAMPP
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `pattern_care` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pattern_care`;

-- ---------------------------------------------------------------------
-- 1. TABLA: sucursales (Multi-Location / Almacenes de Business Central)
-- ---------------------------------------------------------------------
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TABLA: productos_sku (Catálogo Maestro de Productos)
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
    `stock_minimo_general` INT NOT NULL DEFAULT 6,
    `precio_unitario` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `notas` VARCHAR(255) NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. TABLA: inventario_sucursales (Existencias por Sucursal)
-- Permite control individual de stock mínimo (< 6) por cada sucursal
-- ---------------------------------------------------------------------
CREATE TABLE `inventario_sucursales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `producto_id` INT NOT NULL,
    `sucursal_id` INT NOT NULL,
    `stock_actual` INT NOT NULL DEFAULT 0,
    `stock_minimo` INT NOT NULL DEFAULT 6,
    `ubicacion_pasillo` VARCHAR(50) NULL,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_producto_sucursal` (`producto_id`, `sucursal_id`),
    INDEX `idx_prod_suc` (`producto_id`, `sucursal_id`),
    CONSTRAINT `fk_inv_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos_sku` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inv_sucursal` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. TABLA: transferencias_stock (Órdenes de Transferencia Inter-Sucursal)
-- ---------------------------------------------------------------------
CREATE TABLE `transferencias_stock` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo_transferencia` VARCHAR(50) NOT NULL UNIQUE,
    `sucursal_origen_id` INT NOT NULL,
    `sucursal_destino_id` INT NOT NULL,
    `producto_id` INT NOT NULL,
    `cantidad` INT NOT NULL,
    `estado` ENUM('Pendiente', 'En Tránsito', 'Recibido', 'Cancelado') DEFAULT 'Recibido',
    `responsable` VARCHAR(100) NOT NULL,
    `notas` VARCHAR(255) NULL,
    `fecha_transferencia` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_trf_origen` (`sucursal_origen_id`),
    INDEX `idx_trf_destino` (`sucursal_destino_id`),
    CONSTRAINT `fk_trf_origen` FOREIGN KEY (`sucursal_origen_id`) REFERENCES `sucursales` (`id`),
    CONSTRAINT `fk_trf_destino` FOREIGN KEY (`sucursal_destino_id`) REFERENCES `sucursales` (`id`),
    CONSTRAINT `fk_trf_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos_sku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. TABLA: produccion_ordenes (Órdenes de Producción en 7 Fases)
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
    `sucursal_id` INT NULL,
    `notas` TEXT NULL,
    `fecha_inicio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fase` (`fase_actual`),
    INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. TABLA: mermas_retrabajos (Bitácora de Costos Ocultos y Mermas)
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
    `sucursal_id` INT NULL,
    `registrado_por` VARCHAR(100) NOT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tipo` (`tipo`),
    INDEX `idx_area` (`area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. TABLA: rendimiento_telas (Simulador de Consumo de Tela SRA y CRO)
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
-- INSERCIÓN DE DATOS SEMILLA (MULTI-SUCURSAL Y OPERATIVO TEXTIL)
-- ---------------------------------------------------------------------

-- 1. Sucursales de Pattern Care
INSERT INTO `sucursales` (`id`, `codigo`, `nombre`, `tipo`, `encargado`, `telefono`, `direccion`, `estado`) VALUES
(1, 'MAT-01', 'Matriz & Planta de Confección', 'Planta / Taller Central', 'Dueña / Encargada General', '55-1234-5678', 'Parque Industrial Textil, Nave 4', 'Activo'),
(2, 'SUC-HOSP', 'Sucursal Hospital General', 'Punto de Venta / Showroom', 'Área Administrativa', '55-8765-4321', 'Av. Médicos 405, Frente a Hospital Regional', 'Activo'),
(3, 'SUC-NORTE', 'Sucursal Zona Norte (Clínica Palmas)', 'Punto de Venta / Showroom', 'Corte y Calidad [2]', '55-9988-7766', 'Plaza Médica Las Palmas, Local 12', 'Activo'),
(4, 'ALM-DIG', 'Almacén Digital & Envíos WhatsApp', 'Almacén Digital', 'Área Administrativa', '55-4433-2211', 'Bodega Central de Logística y Paquetería', 'Activo');

-- 2. Productos SKU
INSERT INTO `productos_sku` (`id`, `sku`, `nombre`, `categoria`, `color`, `talla`, `coleccion`, `stock_minimo_general`, `precio_unitario`, `notas`) VALUES
(1, 'GQ-AZC-U', 'Gorro Quirúrgico Clásico Antifluido', 'Gorros Quirúrgicos', 'Azul Cielo', 'Unitalla', 'Clásica Hospitalaria', 6, 120.00, 'Alta demanda en Hospital General'),
(2, 'GQ-EST-OD', 'Gorro Quirúrgico Estampado Odonto', 'Gorros Quirúrgicos', 'Multicolor', 'Unitalla', 'Odontología Smile', 6, 150.00, 'Estampado en taller externo'),
(3, 'GQ-LIL-U', 'Gorro Quirúrgico Quirófano Pastel', 'Gorros Quirúrgicos', 'Lila', 'Unitalla', 'Colección Pastel', 6, 130.00, 'Venta en línea activa'),
(4, 'CH-BLA-M', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Blanco Óptico', 'M', 'Línea Premier', 6, 680.00, 'Desabasto de cierres y tela blanca'),
(5, 'CH-AZM-L', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Azul Marino', 'L', 'Línea Premier', 6, 680.00, 'Prenda estándar hospitalaria'),
(6, 'CH-VER-S', 'Chamarra Médica Antifluido Stretch', 'Chamarras Médicas', 'Verde Quirófano', 'S', 'Línea Premier', 6, 680.00, 'Bajo stock en sucursales'),
(7, 'CP-NEG-U', 'Cubrepolvo Impermeable con Ajuste', 'Cubrepolvos', 'Negro Oxford', 'Unitalla', 'Protección Total', 6, 320.00, 'Riesgo inminente de agotamiento'),
(8, 'CP-AZM-U', 'Cubrepolvo Quirúrgico Repelente', 'Cubrepolvos', 'Azul Marino', 'Unitalla', 'Protección Total', 6, 320.00, 'Demanda constante institucional');

-- 3. Inventario distribuido por Sucursal
-- Nota: Stock Mínimo normativo es 6. Varias sucursales tienen < 6 para detonar alertas por sucursal.
INSERT INTO `inventario_sucursales` (`producto_id`, `sucursal_id`, `stock_actual`, `stock_minimo`, `ubicacion_pasillo`) VALUES
-- GQ-AZC-U
(1, 1, 14, 6, 'Estante A-1'),
(1, 2, 2, 6, 'Exhibidor Showroom'), -- ALERTA CRÍTICA en Sucursal Hospital
(1, 3, 5, 6, 'Mostrador B'), -- ALERTA en Sucursal Norte
(1, 4, 8, 6, 'Rack Digital 1'),

-- GQ-EST-OD
(2, 1, 20, 6, 'Estante A-2'),
(2, 2, 8, 6, 'Exhibidor Showroom'),
(2, 3, 7, 6, 'Mostrador A'),
(2, 4, 12, 6, 'Rack Digital 1'),

-- GQ-LIL-U
(3, 1, 12, 6, 'Estante A-3'),
(3, 2, 4, 6, 'Exhibidor Showroom'), -- ALERTA
(3, 3, 6, 6, 'Mostrador C'),
(3, 4, 15, 6, 'Rack Digital 1'),

-- CH-BLA-M
(4, 1, 5, 6, 'Estante B-1'), -- ALERTA en Planta por desabasto
(4, 2, 2, 6, 'Perchero Premier'), -- ALERTA
(4, 3, 1, 6, 'Perchero Showroom'), -- ALERTA
(4, 4, 3, 6, 'Rack Digital 2'), -- ALERTA

-- CH-AZM-L
(5, 1, 15, 6, 'Estante B-2'),
(5, 2, 7, 6, 'Perchero Premier'),
(5, 3, 6, 6, 'Perchero Showroom'),
(5, 4, 9, 6, 'Rack Digital 2'),

-- CH-VER-S
(6, 1, 8, 6, 'Estante B-3'),
(6, 2, 3, 6, 'Perchero Premier'), -- ALERTA
(6, 3, 4, 6, 'Perchero Showroom'), -- ALERTA
(6, 4, 5, 6, 'Rack Digital 2'), -- ALERTA

-- CP-NEG-U
(7, 1, 6, 6, 'Estante C-1'),
(7, 2, 1, 6, 'Estante Protección'), -- ALERTA CRÍTICA
(7, 3, 2, 6, 'Estante Protección'), -- ALERTA
(7, 4, 3, 6, 'Rack Digital 3'), -- ALERTA

-- CP-AZM-U
(8, 1, 18, 6, 'Estante C-2'),
(8, 2, 8, 6, 'Estante Protección'),
(8, 3, 7, 6, 'Estante Protección'),
(8, 4, 10, 6, 'Rack Digital 3');

-- 4. Transferencias de Stock Inter-Sucursal
INSERT INTO `transferencias_stock` (`codigo_transferencia`, `sucursal_origen_id`, `sucursal_destino_id`, `producto_id`, `cantidad`, `estado`, `responsable`, `notas`) VALUES
('TRF-2026-001', 1, 2, 1, 10, 'Recibido', 'Área Administrativa', 'Reabastecimiento urgente de Gorro Azul a Sucursal Hospital'),
('TRF-2026-002', 1, 4, 2, 15, 'Recibido', 'Dueña / Encargada General', 'Envío para pedidos de catálogo digital WhatsApp'),
('TRF-2026-003', 1, 3, 5, 6, 'En Tránsito', 'Corte y Calidad [2]', 'Traspaso de Chamarras Azul L por mensajería interna');

-- 5. Órdenes de Producción
INSERT INTO `produccion_ordenes` (`codigo_orden`, `producto`, `lote`, `cantidad`, `fase_actual`, `estado`, `responsable`, `sucursal_id`, `notas`) VALUES
('ORD-2026-001', 'Gorro Quirúrgico Odonto Smile', 'LOT-GQ-01', 120, 'Impresión', 'Detenido / Alerta', 'Área Administrativa', 1, 'Retraso de 48 horas en taller de estampado externo. Cuello de botella detectado.'),
('ORD-2026-002', 'Chamarra Médica Premier Azul Marino', 'LOT-CH-04', 50, 'Confección', 'En Proceso', 'Confección [2]', 1, 'Ensamble de cremalleras y pespuntes dobles en Planta.'),
('ORD-2026-003', 'Cubrepolvo Impermeable Negro', 'LOT-CP-02', 80, 'Corte', 'En Proceso', 'Corte y Calidad [2]', 1, 'Trazado y corte con tela CRO en Planta Central.'),
('ORD-2026-004', 'Gorro Quirúrgico Clásico Azul Cielo', 'LOT-GQ-03', 150, 'Empaque', 'En Proceso', 'Corte y Calidad [2]', 1, 'Inspección de calidad final previa a distribución.');

-- 6. Bitácora de Mermas
INSERT INTO `mermas_retrabajos` (`fecha`, `descripcion_error`, `tipo`, `area`, `material_desperdiciado`, `costo_impacto`, `lote_afectado`, `sucursal_id`, `registrado_por`) VALUES
('2026-10-08', 'Tono de estampado fuera de escala por falta de muestra física aprobada en taller externo', 'Merma', 'Impresión externa', '18 gorros cortados', 810.00, 'LOT-GQ-01', 1, 'Corte y Calidad [2]'),
('2026-10-10', 'Descuadre en costura de cierre por tensión desajustada de máquina overlock', 'Retrabajo', 'Confección', 'Tiempo de descosido + 3 cierres', 240.00, 'LOT-CH-04', 1, 'Dueña / Encargada General'),
('2026-10-11', 'Error en etiquetado de tallas durante empaque apresurado', 'Retrabajo', 'Empaque', '40 bolsas selladas reabiertas', 120.00, 'LOT-GQ-03', 1, 'Área Administrativa'),
('2026-10-12', 'Melladura en tela al cortar sin fijación adecuada de plantilla', 'Merma', 'Corte', '2.5 metros tela CRO', 450.00, 'LOT-CP-02', 1, 'Corte y Calidad [2]');

-- 7. Rendimientos de Tela
INSERT INTO `rendimiento_telas` (`tipo_tela`, `metros`, `piezas_estimadas`, `sobrante_cm`, `lote_referencia`, `color`, `coleccion`, `talla`, `notas`) VALUES
('SRA', 20.00, 120, 0.00, 'LOT-GQ-01', 'Multicolor', 'Odonto Smile', 'Unitalla', '6 piezas exactas por metro en tela SRA'),
('CRO', 20.00, 80, 1000.00, 'LOT-CP-02', 'Negro', 'Protección Total', 'Unitalla', '4 piezas/metro con 50 cm de merma/sobrante por metro (10m total acumulado)');
