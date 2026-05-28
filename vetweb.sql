-- ============================================================
--  VetApp DB — Base de datos reconstruida desde el código fuente
--  Proyecto  : VetWeb / Kuimera - VetApp
--  Motor     : MySQL 5.7+ / MariaDB 10.3+
--  Charset   : utf8mb4
--  Generado  : Reconstruido a partir de los archivos PHP del repositorio
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- ------------------------------------------------------------
-- Base de datos
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `vetapp_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `vetapp_db`;

-- ============================================================
-- TABLA: user
--   Referenciada en: login/auth.php, register/api/auth.php
--   Campos detectados:
--     id, name, email, password, role, active, createdAt
-- ============================================================
CREATE TABLE IF NOT EXISTS `user` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(150)     NOT NULL,
  `email`      VARCHAR(191)     NOT NULL,
  `password`   VARCHAR(255)     NOT NULL,          -- bcrypt hash
  `role`       ENUM('ADMIN','CLIENTE') NOT NULL DEFAULT 'CLIENTE',
  `active`     TINYINT(1)       NOT NULL DEFAULT 0, -- 0 = pendiente, 1 = activo
  `createdAt`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cliente
--   Referenciada en:
--     admin/citas/api/buscar_cliente.php
--     admin/citas/api/crear_cita.php
--     admin/usuarios/api/guardar_usuario.php
--     admin/usuarios/api/listar_usuarios.php
--     mascota/vista-estado-mascota.php
--     citas/seguimiento_cita.php
--   Campos detectados:
--     id, nombre_completo, rut, telefono, email, direccion, fecha_registro
-- ============================================================
CREATE TABLE IF NOT EXISTS `cliente` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `nombre_completo` VARCHAR(200)  NOT NULL,
  `rut`             VARCHAR(20)   DEFAULT NULL,           -- RUT chileno, opcional
  `telefono`        VARCHAR(30)   NOT NULL,
  `email`           VARCHAR(191)  DEFAULT NULL,
  `direccion`       VARCHAR(255)  DEFAULT NULL,
  `fecha_registro`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cliente_telefono` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: mascota
--   Referenciada en:
--     admin/usuarios/api/guardar_mascota.php
--     admin/usuarios/api/listar_mascotas.php
--     admin/usuarios/api/eliminar_mascota.php
--     admin/citas/api/buscar_cliente.php
--     admin/citas/api/crear_cita.php
--     admin/citas/api/finalizar_cita.php
--     mascota/vista-estado-mascota.php
--     citas/seguimiento_cita.php
--   Campos detectados:
--     id, cliente_id, nombre, especie, raza, fecha_nacimiento, sexo,
--     color, peso, esterilizado, numero_chip, ultima_revision,
--     alergias, notas_internas, observaciones,
--     fecha_registro, fecha_actualizacion
-- ============================================================
CREATE TABLE IF NOT EXISTS `mascota` (
  `id`                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `cliente_id`         INT UNSIGNED    NOT NULL,
  `nombre`             VARCHAR(100)    NOT NULL,
  `especie`            VARCHAR(80)     DEFAULT NULL,   -- ej. Perro, Gato, Ave
  `raza`               VARCHAR(100)    DEFAULT NULL,
  `fecha_nacimiento`   DATE            DEFAULT NULL,
  `sexo`               VARCHAR(20)     DEFAULT NULL,   -- Macho / Hembra
  `color`              VARCHAR(80)     DEFAULT NULL,
  `peso`               DECIMAL(6,2)    DEFAULT NULL,   -- kg
  `esterilizado`       TINYINT(1)      NOT NULL DEFAULT 0,
  `numero_chip`        VARCHAR(50)     DEFAULT NULL,
  `ultima_revision`    DATE            DEFAULT NULL,
  `alergias`           TEXT            DEFAULT NULL,
  `notas_internas`     TEXT            DEFAULT NULL,   -- Solo visible para el personal
  `observaciones`      TEXT            DEFAULT NULL,   -- Historial clínico visible al cliente
  `fecha_registro`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mascota_cliente` (`cliente_id`),
  CONSTRAINT `fk_mascota_cliente`
    FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: vacuna
--   Referenciada en:
--     admin/usuarios/api/guardar_mascota.php
--     admin/usuarios/api/listar_vacunas.php
--     admin/usuarios/api/eliminar_mascota.php  (cascade manual)
--     mascota/vista-estado-mascota.php
--   Campos detectados:
--     id, mascota_id, nombre, fecha_aplicacion, fecha_proxima,
--     veterinario, lote, notas, created_at
-- ============================================================
CREATE TABLE IF NOT EXISTS `vacuna` (
  `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `mascota_id`        INT UNSIGNED  NOT NULL,
  `nombre`            VARCHAR(150)  NOT NULL,
  `fecha_aplicacion`  DATE          DEFAULT NULL,
  `fecha_proxima`     DATE          DEFAULT NULL,
  `veterinario`       VARCHAR(150)  DEFAULT NULL,
  `lote`              VARCHAR(80)   DEFAULT NULL,
  `notas`             TEXT          DEFAULT NULL,
  `created_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vacuna_mascota` (`mascota_id`),
  CONSTRAINT `fk_vacuna_mascota`
    FOREIGN KEY (`mascota_id`) REFERENCES `mascota` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: servicios
--   Referenciada en:
--     admin/servicios/api/guardar_servicio.php
--     admin/servicios/api/listar_servicios.php
--     admin/servicios/api/eliminar_servicio.php
--     admin/citas/api/listar_citas.php  (LEFT JOIN servicios s ON citas.tipo = s.nombre)
--   Campos detectados:
--     id, nombre, descripcion, precio_min, precio_max, duracion_min, activo
-- ============================================================
CREATE TABLE IF NOT EXISTS `servicios` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(150)    NOT NULL,
  `descripcion`  TEXT            DEFAULT NULL,
  `precio_min`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `precio_max`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `duracion_min` SMALLINT        NOT NULL DEFAULT 30,   -- duración estimada en minutos
  `activo`       TINYINT(1)      NOT NULL DEFAULT 1,
  `icono`        VARCHAR(50)     DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_servicios_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: citas
--   Referenciada en:
--     admin/citas/api/crear_cita.php
--     admin/citas/api/listar_citas.php
--     admin/citas/api/cambiar_estado_cita.php
--     admin/citas/api/finalizar_cita.php
--     admin/citas/api/eliminar_cita.php   (soft-delete: oculta=1)
--     citas/seguimiento_cita.php
--   Campos detectados:
--     id, cliente_id, mascota_id, fecha, tipo, nota, estado,
--     token_publico, oculta, precio_final, observaciones_vet,
--     pagado, created_at, updated_at
-- ============================================================
CREATE TABLE IF NOT EXISTS `citas` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `cliente_id`        INT UNSIGNED    NOT NULL,
  `mascota_id`        INT UNSIGNED    NOT NULL,
  `fecha`             DATETIME        NOT NULL,
  `tipo`              VARCHAR(150)    NOT NULL,   -- nombre del servicio (igual que servicios.nombre)
  `nota`              TEXT            DEFAULT NULL,  -- nota de recepción / ingreso
  `recomendaciones`   TEXT            DEFAULT NULL,  -- recomendaciones previas a la cita
  `comentarios`       TEXT            DEFAULT NULL,  -- comentarios / procedimientos durante la cita
  `estado`            ENUM('pendiente','confirmada','completada','cancelada')
                                      NOT NULL DEFAULT 'pendiente',
  `token_publico`     CHAR(48)        NOT NULL,   -- 48 hex chars para seguimiento público
  `oculta`            TINYINT(1)      NOT NULL DEFAULT 0,  -- soft-delete
  -- Campos de cierre / facturación (se llenan al finalizar)
  `precio_final`      DECIMAL(10,2)   DEFAULT NULL,
  `observaciones_vet` TEXT            DEFAULT NULL,   -- diagnóstico del veterinario
  `pagado`            TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_citas_token` (`token_publico`),
  KEY `idx_citas_cliente` (`cliente_id`),
  KEY `idx_citas_mascota` (`mascota_id`),
  KEY `idx_citas_estado`  (`estado`),
  KEY `idx_citas_fecha`   (`fecha`),
  CONSTRAINT `fk_citas_cliente`
    FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_citas_mascota`
    FOREIGN KEY (`mascota_id`) REFERENCES `mascota` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLA: cita_bitacora — Registro cronológico de eventos/procedimientos
-- ============================================================
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

-- ============================================================
-- TABLA: cotizaciones — Cotizaciones generadas desde citas
-- ============================================================
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

-- ============================================================
-- TABLA: cotizacion_detalles — Líneas individuales de cada cotización
-- ============================================================
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

-- ============================================================
-- TABLA: inventario — Control de stock de vacunas, remedios, equipos e insumos
-- ============================================================
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

-- ============================================================
-- DATOS INICIALES — Usuario administrador por defecto
-- Contraseña: admin1234  (hash bcrypt, cámbiala después del primer login)
-- ============================================================
INSERT IGNORE INTO `user` (`name`, `email`, `password`, `role`, `active`, `createdAt`)
VALUES (
  'Administrador',
  'admin@vetweb.cl',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- "password" en bcrypt
  'ADMIN',
  1,
  NOW()
);

-- ============================================================
-- DATOS INICIALES — Servicios veterinarios de ejemplo
-- ============================================================
INSERT IGNORE INTO `servicios` (`nombre`, `descripcion`, `precio_min`, `precio_max`, `duracion_min`, `activo`) VALUES
('Consulta General',        'Revisión médica general del paciente.',                         15000, 25000,  30, 1),
('Vacunación',              'Aplicación de vacunas según calendario.',                        8000, 15000,  20, 1),
('Desparasitación',         'Tratamiento antiparasitario interno/externo.',                   6000, 12000,  15, 1),
('Control de peso',         'Evaluación nutricional y control de peso.',                      8000, 15000,  30, 1),
('Limpieza dental',         'Profilaxis dental y eliminación de sarro.',                     35000, 60000,  60, 1),
('Cirugía menor',           'Procedimientos quirúrgicos ambulatorios de baja complejidad.',  60000,120000,  90, 1),
('Esterilización',          'Cirugía de esterilización (macho/hembra).',                     80000,150000, 120, 1),
('Ecografía',               'Estudio por imágenes de ultrasonido.',                          25000, 45000,  30, 1),
('Exámenes de laboratorio', 'Hemograma, bioquímica y otros exámenes.',                       15000, 40000,  20, 1),
('Urgencia',                'Atención de urgencia fuera de horario regular.',                30000, 80000,  45, 1);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
