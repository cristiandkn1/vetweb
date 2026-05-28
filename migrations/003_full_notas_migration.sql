-- ============================================================
-- Migration: Agregar columnas recomendaciones y tabla cita_bitacora
-- ============================================================

-- 1. Agregar columna recomendaciones a la tabla citas
ALTER TABLE citas
  ADD COLUMN recomendaciones TEXT DEFAULT NULL AFTER nota;

-- 2. Crear tabla cita_bitacora para el registro cronológico
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
