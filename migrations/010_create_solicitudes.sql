CREATE TABLE IF NOT EXISTS solicitudes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(200) NOT NULL,
    email VARCHAR(191) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    mascota_nombre VARCHAR(100) NOT NULL,
    mascota_especie VARCHAR(80) DEFAULT NULL,
    mascota_raza VARCHAR(100) DEFAULT NULL,
    mascota_sexo VARCHAR(20) DEFAULT NULL,
    servicio VARCHAR(150) NOT NULL,
    fecha_cita DATETIME NOT NULL,
    nota TEXT DEFAULT NULL,
    estado ENUM('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
