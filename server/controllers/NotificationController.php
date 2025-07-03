<?php
require_once __DIR__ . '/../db.php';

class NotificationController {
    
    public function obtenerNotificaciones($usuario_id, $limit = 50) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM notificaciones 
                WHERE usuario_id = ? 
                ORDER BY creado_en DESC 
                LIMIT ?
            ");
            $stmt->execute([$usuario_id, $limit]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener notificaciones: " . $e->getMessage());
            return [];
        }
    }
    
    public function contarNotificacionesNoLeidas($usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM notificaciones 
                WHERE usuario_id = ? AND leida = 0
            ");
            $stmt->execute([$usuario_id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            error_log("Error al contar notificaciones: " . $e->getMessage());
            return 0;
        }
    }
    
    public function marcarComoLeida($id, $usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE notificaciones 
                SET leida = 1 
                WHERE id = ? AND usuario_id = ?
            ");
            
            return $stmt->execute([$id, $usuario_id]);
        } catch (Exception $e) {
            error_log("Error al marcar notificación como leída: " . $e->getMessage());
            return false;
        }
    }
    
    public function marcarTodasComoLeidas($usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE notificaciones 
                SET leida = 1 
                WHERE usuario_id = ?
            ");
            
            return $stmt->execute([$usuario_id]);
        } catch (Exception $e) {
            error_log("Error al marcar todas las notificaciones como leídas: " . $e->getMessage());
            return false;
        }
    }

    // Agregar método para crear notificaciones automáticamente:

    public function crearNotificacion($usuario_id, $mensaje, $tipo = 'general') {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones (usuario_id, mensaje, tipo, leida) 
                VALUES (?, ?, ?, 0)
            ");
            
            return $stmt->execute([$usuario_id, $mensaje, $tipo]);
        } catch (Exception $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
}
?>
