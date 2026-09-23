CREATE DATABASE IF NOT EXISTS catalogo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE catalogo;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','empresa') NOT NULL DEFAULT 'empresa',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) DEFAULT 0.00,
    disponible TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    servicio_id INT NOT NULL,
    empresa_id INT NOT NULL,
    estado ENUM('pendiente','aceptada','rechazada','realizada','validada') NOT NULL DEFAULT 'pendiente',
    tx_hash VARCHAR(64) DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    FOREIGN KEY (empresa_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- Admin por defecto (generar hash con: php -r "echo password_hash('admin123', PASSWORD_ARGON2ID);")
INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Admin', 'admin@instituto.es', 'HASH_AQUI', 'admin');