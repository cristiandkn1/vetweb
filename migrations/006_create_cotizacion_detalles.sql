-- ============================================================
-- MIGRACIÓN 006: cotizacion_detalles + columna afecto_iva
-- ============================================================

-- 1. Tabla cotizacion_detalles
CREATE TABLE IF NOT EXISTS `cotizacion_detalles` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `cotizacion_id`   INT UNSIGNED    NOT NULL,
  `descripcion`     VARCHAR(255)    NOT NULL,
  `cantidad`        INT             NOT NULL DEFAULT 1,
  `precio_unitario` DECIMAL(10,2)   NOT NULL DEFAULT 0,
  `afecto_iva`      TINYINT         NOT NULL DEFAULT 1,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_detalle_cotizacion` (`cotizacion_id`),
  CONSTRAINT `fk_detalle_cotizacion`
    FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
