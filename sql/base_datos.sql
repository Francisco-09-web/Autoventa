CREATE DATABASE IF NOT EXISTS autoventa
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE autoventa;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(60) NOT NULL,
  apellido VARCHAR(60) NOT NULL,
  dni VARCHAR(8) NOT NULL,
  email VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_archivado DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_dni (dni),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB;
-- El administrador se crea ejecutando una sola vez crear_admin.php en el navegador.
