-- ============================================================
-- MIGRACIÓN 007: Empleados y horarios semanales
-- ============================================================

CREATE TABLE IF NOT EXISTS `empleados` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(150)    NOT NULL,
  `color`        VARCHAR(7)      NOT NULL DEFAULT '#6366f1',
  `telefono`     VARCHAR(30)     DEFAULT NULL,
  `especialidad` VARCHAR(100)    DEFAULT NULL,
  `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_empleados_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `horario_semanal` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `empleado_id`  INT UNSIGNED    NOT NULL,
  `dia_semana`   TINYINT         NOT NULL COMMENT '0=Lunes…6=Domingo',
  `hora_inicio`  TIME            NOT NULL,
  `hora_fin`     TIME            NOT NULL,
  `tipo`         ENUM('trabajo','colacion') NOT NULL DEFAULT 'trabajo',
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_horario_empleado` (`empleado_id`),
  KEY `idx_horario_dia` (`dia_semana`),
  CONSTRAINT `fk_horario_empleado`
    FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
