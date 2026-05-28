CREATE TABLE IF NOT EXISTS `mascota_enfermedades` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mascota_id` INT UNSIGNED NOT NULL,
    `enfermedad` VARCHAR(255) NOT NULL,
    `recurrencia_dias` INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Días entre controles requeridos',
    `ultimo_control` DATE DEFAULT NULL COMMENT 'Fecha del último control',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`mascota_id`) REFERENCES `mascota`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
