-- Script para crear un usuario de prueba
INSERT INTO usuarios (nombre, correo, contraseña_hash, rol) 
VALUES ('Usuario Prueba', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');

-- La contraseña es "password"
-- Hash generado con: password_hash('password', PASSWORD_DEFAULT)
