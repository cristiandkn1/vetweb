-- ============================================================
-- MIGRACIÓN COMPLETA: Notas + Bitácora + Cotizaciones
-- ============================================================

-- 1. Columna recomendaciones en citas
ALTER TABLE citas
  ADD COLUMN recomendaciones TEXT DEFAULT NULL AFTER nota;

-- 2. Tabla cita_bitacora
CREATE TABLE IF NOT EXISTS `cita_bitacora` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `cita_id`     INT UNSIGNED  NOT NULL,
  `hora`        TIME          NOT NULL DEFAULT (CURRENT_TIME),
  `comentario`  TEXT          NOT NULL,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bitacora_cita` (`cita_id`),
  CONSTRAINT `fk_bitacora_cita`
    FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla cotizaciones
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id`                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `numero_cotizacion`   VARCHAR(20)     NOT NULL,
  `cita_id`             INT UNSIGNED    DEFAULT NULL,
  `cliente_id`          INT UNSIGNED    NOT NULL,
  `mascota_id`          INT UNSIGNED    NOT NULL,
  `servicio`            VARCHAR(150)    NOT NULL,
  `precio_estimado_min` DECIMAL(10,2)   DEFAULT NULL,
  `precio_estimado_max` DECIMAL(10,2)   DEFAULT NULL,
  `nota`                TEXT            DEFAULT NULL,
  `estado`              ENUM('pendiente','aprobada','rechazada','vencida') NOT NULL DEFAULT 'pendiente',
  `created_at`          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME        DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_numero_cotizacion` (`numero_cotizacion`),
  KEY `idx_cotizaciones_cliente` (`cliente_id`),
  KEY `idx_cotizaciones_estado` (`estado`),
  CONSTRAINT `fk_cotizaciones_cliente`
    FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cotizaciones_mascota`
    FOREIGN KEY (`mascota_id`) REFERENCES `mascota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla cotizacion_detalles
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
