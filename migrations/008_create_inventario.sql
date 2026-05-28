CREATE TABLE IF NOT EXISTS `inventario` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nombre`           VARCHAR(200)    NOT NULL,
  `descripcion`      TEXT,
  `categoria`        ENUM('vacuna','remedio','equipo','insumo','otro') NOT NULL DEFAULT 'otro',
  `cantidad`         INT             NOT NULL DEFAULT 0,
  `precio`           DECIMAL(10,2)   DEFAULT NULL,
  `fecha_vencimiento` DATE           DEFAULT NULL,
  `lote`             VARCHAR(100)    DEFAULT NULL,
  `proveedor`        VARCHAR(200)    DEFAULT NULL,
  `ubicacion`        VARCHAR(200)    DEFAULT NULL,
  `created_at`       DATETIME        DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME        DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_inventario_categoria` (`categoria`),
  INDEX `idx_inventario_vencimiento` (`fecha_vencimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
