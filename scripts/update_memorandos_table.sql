-- Actualizar tabla de memorandos con la estructura completa
DROP TABLE IF EXISTS memorandos;

CREATE TABLE memorandos (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,

  -- 🧾 Apartado de Entradas
  folio VARCHAR(50) UNIQUE NOT NULL,                          -- No. de Oficio y/o documentos
  fecha_recepcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,        -- Fecha y hora de recepción
  entidad_productora VARCHAR(255),                            -- Entidad Productora
  remitente_id BIGINT NOT NULL,                               -- Usuario que envía
  fecha_documento DATE,                                       -- Fecha del Documento
  contenido TEXT NOT NULL,                                    -- Breve extracto o contenido completo del archivo
  destinatario_id BIGINT,                                     -- Usuario destinatario
  urgencia ENUM('urgente', 'ordinario') DEFAULT 'ordinario',  -- Urgencia
  documento_blob LONGBLOB,                                    -- Anexos (archivo adjunto opcional)

  -- 📤 Apartado de Salidas
  area_destino_id BIGINT,                                     -- Se remite a área o Departamento
  fecha_recepcion_destino TIMESTAMP,                          -- Fecha y hora de recepción en destino
  fecha_requerida_respuesta DATE,                             -- Fecha requerida de respuesta
  estatus_atencion ENUM('pendiente', 'proceso', 'atendido') DEFAULT 'pendiente', -- Estado del asunto
  fecha_limite DATE,                                          -- Fecha límite
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,         -- Registro de creación
  fecha_resolucion_final DATE,                                -- Fecha de resolución final

  area_id BIGINT NOT NULL,                                    -- Área de origen

  FOREIGN KEY (remitente_id) REFERENCES usuarios(id),
  FOREIGN KEY (destinatario_id) REFERENCES usuarios(id),
  FOREIGN KEY (area_id) REFERENCES areas(id),
  FOREIGN KEY (area_destino_id) REFERENCES areas(id)
);

-- Insertar algunos datos de ejemplo
INSERT INTO memorandos (
  folio, entidad_productora, remitente_id, fecha_documento, contenido, 
  destinatario_id, urgencia, area_id, area_destino_id, fecha_requerida_respuesta, fecha_limite
) VALUES 
('MEM-2025-001', 'Dirección General', 1, '2025-01-11', 'Solicitud de presupuesto para el próximo trimestre', 2, 'urgente', 1, 2, '2025-01-20', '2025-01-25'),
('MEM-2025-002', 'Recursos Humanos', 2, '2025-01-11', 'Convocatoria para capacitación del personal docente', 1, 'ordinario', 2, 1, '2025-01-30', '2025-02-05'),
('MEM-2025-003', 'Servicios Escolares', 1, '2025-01-11', 'Reporte de inscripciones del semestre actual', 2, 'ordinario', 5, 3, '2025-01-25', '2025-01-28');
