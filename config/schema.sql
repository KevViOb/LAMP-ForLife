-- =============================================
-- Base de datos: forlife_db
-- Sistema de autenticación de usuarios
-- For Life, S.A. — Costa Rica
-- Aplicando estándares sql-advanced-kev
-- =============================================

CREATE DATABASE IF NOT EXISTS forlife_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE forlife_db;

-- Tabla principal de usuarios del sistema
-- Almacena tanto usuarios normales como administradores
CREATE TABLE IF NOT EXISTS usuarios (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    usuario          VARCHAR(50)  NOT NULL UNIQUE,
    nombre           VARCHAR(100) NOT NULL,
    apellido1        VARCHAR(100) NOT NULL,
    apellido2        VARCHAR(100) NOT NULL,
    password_hash    VARCHAR(255) NOT NULL,
    rol              ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    activo           TINYINT(1) NOT NULL DEFAULT 1,
    fecha_registro   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso    TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice en usuario porque es la columna principal del WHERE en login
CREATE INDEX idx_usuario ON usuarios (usuario);

-- Índice en rol para filtrar en el panel de administración
CREATE INDEX idx_rol ON usuarios (rol);

-- Tabla de auditoría para registrar intentos de acceso
-- Permite al administrador ver actividad sospechosa
CREATE TABLE IF NOT EXISTS logs_acceso (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT NULL,
    usuario_texto VARCHAR(50) NOT NULL,
    resultado     ENUM('exitoso', 'fallido') NOT NULL,
    ip_origen     VARCHAR(45) NOT NULL,
    fecha_intento TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice en fecha para consultas del panel admin ordenadas por tiempo
CREATE INDEX idx_fecha_intento ON logs_acceso (fecha_intento);

-- Usuario administrador inicial del sistema
-- No se incluye un admin con contraseña por defecto porque este script es
-- público. Registre su usuario desde vista/registro.php y luego promuévalo:
-- UPDATE usuarios SET rol = 'admin' WHERE usuario = 'su_usuario';

-- Usuario de aplicación con permisos limitados
-- Ejecutar estos comandos dentro de MySQL como root
-- CREATE USER 'forlife_user'@'localhost' IDENTIFIED BY 'TU_PASSWORD_AQUI';
-- GRANT SELECT, INSERT, UPDATE ON forlife_db.* TO 'forlife_user'@'localhost';
-- FLUSH PRIVILEGES;
