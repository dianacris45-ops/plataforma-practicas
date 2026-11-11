-- =========================================================
-- Base de datos: plataforma_practicas (MySQL / MariaDB - XAMPP)
-- Importar desde phpMyAdmin: pestaña "Importar" > elegir este archivo > "Continuar"
-- =========================================================
CREATE DATABASE IF NOT EXISTS plataforma_practicas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plataforma_practicas;

DROP TABLE IF EXISTS postulaciones;
DROP TABLE IF EXISTS vacantes;
DROP TABLE IF EXISTS empresas;
DROP TABLE IF EXISTS estudiantes;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  correo      VARCHAR(120) NOT NULL UNIQUE,
  clave_hash  VARCHAR(255) NOT NULL,
  rol         ENUM('estudiante','empresa','coordinador') NOT NULL,
  creado_en   DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE estudiantes (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT NOT NULL UNIQUE,
  nombres     VARCHAR(80) NOT NULL,
  apellidos   VARCHAR(80) NOT NULL,
  documento   VARCHAR(12) NOT NULL UNIQUE,
  programa    VARCHAR(100) NOT NULL,
  semestre    TINYINT NOT NULL,
  telefono    VARCHAR(20),
  CONSTRAINT fk_est_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE empresas (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id    INT NOT NULL UNIQUE,
  razon_social  VARCHAR(120) NOT NULL,
  nit           VARCHAR(12) NOT NULL UNIQUE,
  sector        VARCHAR(80) NOT NULL,
  ciudad        VARCHAR(60) NOT NULL,
  contacto      VARCHAR(100) NOT NULL,
  telefono      VARCHAR(20),
  CONSTRAINT fk_emp_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE vacantes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  empresa_id    INT NOT NULL,
  titulo        VARCHAR(120) NOT NULL,
  descripcion   TEXT NOT NULL,
  requisitos    TEXT,
  programa      VARCHAR(100) NOT NULL,
  modalidad     ENUM('presencial','remota','hibrida') NOT NULL,
  cupos         INT NOT NULL DEFAULT 1,
  fecha_cierre  DATE NOT NULL,
  estado        ENUM('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  creado_en     DATETIME NOT NULL,
  CONSTRAINT fk_vac_empresa FOREIGN KEY (empresa_id) REFERENCES empresas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE postulaciones (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  vacante_id     INT NOT NULL,
  estudiante_id  INT NOT NULL,
  estado         ENUM('enviada','en_revision','aceptada','rechazada') NOT NULL DEFAULT 'enviada',
  fecha          DATETIME NOT NULL,
  UNIQUE KEY uq_postulacion (vacante_id, estudiante_id),
  CONSTRAINT fk_pos_vacante FOREIGN KEY (vacante_id) REFERENCES vacantes(id) ON DELETE CASCADE,
  CONSTRAINT fk_pos_estudiante FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
