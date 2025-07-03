-- Crear usuario administrador
INSERT IGNORE INTO usuarios (nombre, correo, contraseña_hash, rol) 
VALUES ('Administrador', 'admin@uttecam.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Crear usuario normal de prueba
INSERT IGNORE INTO usuarios (nombre, correo, contraseña_hash, rol) 
VALUES ('Usuario Prueba', 'usuario@uttecam.edu.mx', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'usuario');

-- La contraseña para ambos es "password"
