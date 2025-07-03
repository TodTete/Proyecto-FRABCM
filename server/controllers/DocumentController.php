<?php
require_once __DIR__ . '/../db.php';

class DocumentController {
    
    public function obtenerDocumentos($usuario_id = null) {
        global $pdo;
        
        try {
            if ($usuario_id) {
                // Documentos del usuario específico
                $stmt = $pdo->prepare("
                    SELECT DISTINCT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                    ORDER BY m.fecha_creacion DESC
                ");
                $stmt->execute([$usuario_id, $usuario_id]);
            } else {
                // Todos los documentos (para admin)
                $stmt = $pdo->prepare("
                    SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    ORDER BY m.fecha_creacion DESC
                ");
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDocumentosPendientes($usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre,
                       dd.estatus as mi_estatus, dd.fecha_actualizacion
                FROM memorandos m 
                LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                LEFT JOIN areas a1 ON m.area_id = a1.id 
                LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                INNER JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                WHERE dd.destinatario_id = ? AND dd.estatus IN ('pendiente', 'proceso')
                ORDER BY m.fecha_creacion DESC
            ");
            $stmt->execute([$usuario_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDestinatariosDocumento($memorando_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT dd.*, u.nombre, u.correo
                FROM documento_destinatarios dd
                INNER JOIN usuarios u ON dd.destinatario_id = u.id
                WHERE dd.memorando_id = ?
                ORDER BY u.nombre
            ");
            $stmt->execute([$memorando_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener destinatarios: " . $e->getMessage());
            return [];
        }
    }
    
    public function actualizarEstatusDestinatario($memorando_id, $destinatario_id, $estatus, $comentarios = '') {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE documento_destinatarios 
                SET estatus = ?, comentarios = ?, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE memorando_id = ? AND destinatario_id = ?
            ");
            
            return $stmt->execute([$estatus, $comentarios, $memorando_id, $destinatario_id]);
        } catch (Exception $e) {
            error_log("Error al actualizar estatus: " . $e->getMessage());
            return false;
        }
    }
    
    public function puedeEditarEstatus($memorando_id, $usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM documento_destinatarios 
                WHERE memorando_id = ? AND destinatario_id = ?
            ");
            $stmt->execute([$memorando_id, $usuario_id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar permisos: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerDocumentosRecientes($usuario_id = null, $limit = 10) {
        global $pdo;
        
        try {
            if ($usuario_id) {
                $stmt = $pdo->prepare("
                    SELECT DISTINCT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                    ORDER BY m.fecha_creacion DESC
                    LIMIT ?
                ");
                $stmt->execute([$usuario_id, $usuario_id, $limit]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    ORDER BY m.fecha_creacion DESC
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos recientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerEstadisticas($usuario_id, $rol) {
        global $pdo;
        
        try {
            if ($rol === 'admin') {
                // Estadísticas globales para admin
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT m.id) as total,
                        SUM(CASE WHEN dd.estatus = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN dd.estatus = 'proceso' THEN 1 ELSE 0 END) as proceso,
                        SUM(CASE WHEN dd.estatus = 'atendido' THEN 1 ELSE 0 END) as atendidos
                    FROM memorandos m
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                ");
                $stmt->execute();
            } else {
                // Estadísticas del usuario
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT m.id) as total,
                        SUM(CASE WHEN dd.estatus = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN dd.estatus = 'proceso' THEN 1 ELSE 0 END) as proceso,
                        SUM(CASE WHEN dd.estatus = 'atendido' THEN 1 ELSE 0 END) as atendidos
                    FROM memorandos m 
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                ");
                $stmt->execute([$usuario_id, $usuario_id]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['total' => 0, 'pendientes' => 0, 'proceso' => 0, 'atendidos' => 0];
        }
    }
    
    public function obtenerDocumentoPorId($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                FROM memorandos m 
                LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                LEFT JOIN areas a1 ON m.area_id = a1.id 
                LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documento: " . $e->getMessage());
            return null;
        }
    }
    
    public function actualizarDocumento($id, $datos) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE memorandos SET 
                    folio = ?, entidad_productora = ?, fecha_documento = ?, contenido = ?,
                    urgencia = ?, area_destino_id = ?, 
                    fecha_requerida_respuesta = ?, fecha_limite = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $datos['folio'],
                $datos['entidad_productora'],
                $datos['fecha_documento'],
                $datos['contenido'],
                $datos['urgencia'],
                $datos['area_destino_id'],
                $datos['fecha_requerida_respuesta'],
                $datos['fecha_limite'],
                $id
            ]);
        } catch (Exception $e) {
            error_log("Error al actualizar documento: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerUsuarios() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, correo FROM usuarios ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerAreas() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT id, nombre FROM areas ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener áreas: " . $e->getMessage());
            return [];
        }
    }
    
    public function subirDocumento() {
        try {
            // Validar campos obligatorios
            $folio = $_POST['folio'] ?? '';
            $fecha_documento = $_POST['fecha_documento'] ?? '';
            $destinatarios = $_POST['destinatarios'] ?? [];
            $area_id = $_POST['area_id'] ?? '';
            $area_destino_id = $_POST['area_destino_id'] ?? '';
            $contenido = $_POST['contenido'] ?? '';
            $urgencia = $_POST['urgencia'] ?? 'ordinario';
            $entidad_productora = $_POST['entidad_productora'] ?? '';
            $fecha_limite = $_POST['fecha_limite'] ?? null;
            $fecha_requerida_respuesta = $_POST['fecha_requerida_respuesta'] ?? null;
            
            if (empty($folio) || empty($destinatarios) || empty($area_id) || empty($contenido) || empty($fecha_documento) || empty($area_destino_id)) {
                header("Location: /project/public/subir-documento?error=" . urlencode("Todos los campos obligatorios deben completarse"));
                exit();
            }
            
            global $pdo;
            
            // Verificar si el folio ya existe
            $stmt = $pdo->prepare("SELECT id FROM memorandos WHERE folio = ?");
            $stmt->execute([$folio]);
            
            if ($stmt->fetch()) {
                header("Location: /project/public/subir-documento?error=" . urlencode("El folio ya existe"));
                exit();
            }
            
            // Manejar archivo PDF (obligatorio)
            if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                header("Location: /project/public/subir-documento?error=" . urlencode("El archivo PDF es obligatorio"));
                exit();
            }
            
            $archivo = $_FILES['documento'];
            
            // Validar tipo de archivo
            $tipos_permitidos = ['application/pdf'];
            if (!in_array($archivo['type'], $tipos_permitidos)) {
                header("Location: /project/public/subir-documento?error=" . urlencode("Solo se permiten archivos PDF"));
                exit();
            }
            
            // Validar tamaño (máximo 10MB)
            if ($archivo['size'] > 10 * 1024 * 1024) {
                header("Location: /project/public/subir-documento?error=" . urlencode("El archivo es demasiado grande (máximo 10MB)"));
                exit();
            }
            
            $documento_blob = file_get_contents($archivo['tmp_name']);
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            try {
                // Insertar documento principal
                $stmt = $pdo->prepare("
                    INSERT INTO memorandos (
                        folio, entidad_productora, remitente_id, fecha_documento, 
                        contenido, urgencia, documento_blob, 
                        area_id, area_destino_id, fecha_limite, fecha_requerida_respuesta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $folio,
                    $entidad_productora,
                    $_SESSION['usuario']['id'],
                    $fecha_documento,
                    $contenido,
                    $urgencia,
                    $documento_blob,
                    $area_id,
                    $area_destino_id,
                    $fecha_limite ?: null,
                    $fecha_requerida_respuesta ?: null
                ]);
                
                $memorando_id = $pdo->lastInsertId();
                
                // Insertar destinatarios
                foreach ($destinatarios as $destinatario_id) {
                    $stmt = $pdo->prepare("
                        INSERT INTO documento_destinatarios (memorando_id, destinatario_id, estatus)
                        VALUES (?, ?, 'pendiente')
                    ");
                    $stmt->execute([$memorando_id, $destinatario_id]);
                    
                    // Crear notificación para cada destinatario
                    $this->crearNotificacion($destinatario_id, $folio, $_SESSION['usuario']['nombre']);
                }
                
                $pdo->commit();
                
                header("Location: /project/public/documentos?success=" . urlencode("Documento enviado a " . count($destinatarios) . " destinatario(s) exitosamente"));
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        } catch (Exception $e) {
            error_log("Error al subir documento: " . $e->getMessage());
            header("Location: /project/public/subir-documento?error=" . urlencode("Error interno del servidor: " . $e->getMessage()));
            exit();
        }
    }
    
    private function crearNotificacion($destinatario_id, $folio, $remitente_nombre) {
        global $pdo;
        
        try {
            $mensaje = "Has recibido un nuevo documento con folio: $folio de $remitente_nombre";
            
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones (usuario_id, mensaje, tipo, leida) 
                VALUES (?, ?, 'documento_recibido', 0)
            ");
            
            $stmt->execute([$destinatario_id, $mensaje]);
            return true;
        } catch (Exception $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
}
?>
