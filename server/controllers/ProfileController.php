<?php
require_once __DIR__ . '/../db.php';

class ProfileController {
    
    public function actualizarPerfil() {
        try {
            $usuario_id = $_SESSION['usuario']['id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $contraseña_actual = $_POST['contraseña_actual'] ?? '';
            $nueva_contraseña = $_POST['nueva_contraseña'] ?? '';
            $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';
            
            if (empty($nombre)) {
                return ['error' => 'El nombre es obligatorio'];
            }
            
            global $pdo;
            
            // Verificar contraseña actual si se quiere cambiar
            if (!empty($nueva_contraseña)) {
                if (empty($contraseña_actual)) {
                    return ['error' => 'Debes ingresar tu contraseña actual'];
                }
                
                if ($nueva_contraseña !== $confirmar_contraseña) {
                    return ['error' => 'Las contraseñas nuevas no coinciden'];
                }
                
                if (strlen($nueva_contraseña) < 6) {
                    return ['error' => 'La nueva contraseña debe tener al menos 6 caracteres'];
                }
                
                // Verificar contraseña actual
                $stmt = $pdo->prepare("SELECT contraseña_hash FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!password_verify($contraseña_actual, $usuario['contraseña_hash'])) {
                    return ['error' => 'La contraseña actual es incorrecta'];
                }
                
                // Actualizar con nueva contraseña
                $nueva_contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, contraseña_hash = ? WHERE id = ?");
                $stmt->execute([$nombre, $nueva_contraseña_hash, $usuario_id]);
            } else {
                // Solo actualizar nombre
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $usuario_id]);
            }
            
            // Actualizar sesión
            $_SESSION['usuario']['nombre'] = $nombre;
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }
}
?>
