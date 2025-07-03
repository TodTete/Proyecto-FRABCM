-- Crear tabla de áreas si no existe
CREATE TABLE IF NOT EXISTS areas (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar áreas por defecto
INSERT IGNORE INTO areas (nombre) VALUES 
('Dirección General'),
('Recursos Humanos'),
('Finanzas'),
('Académica'),
('Servicios Escolares'),
('Mantenimiento'),
('Sistemas'),
('Biblioteca');
