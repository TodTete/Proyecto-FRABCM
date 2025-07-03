<?php
require_once __DIR__ . '/../db.php';

class UserController {
    
    public function obtenerUsuarios() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, correo, rol, creado_en FROM usuarios ORDER BY creado_en DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    public function crearUsuario() {
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        $rol = $_POST['rol'] ?? 'usuario';
        
        if (empty($nombre) || empty($correo) || empty($contraseña)) {
            header("Location: /project/public/crear-usuario?error=" . urlencode("Todos los campos son obligatorios"));
            exit();
        }
        
        global $pdo;
        
        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->fetch()) {
                header("Location: /project/public/crear-usuario?error=" . urlencode("El correo ya está registrado"));
                exit();
            }
            
            // Crear usuario
            $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña_hash, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $correo, $contraseña_hash, $rol]);
            
            header("Location: /project/public/usuarios?success=" . urlencode("Usuario creado exitosamente"));
            exit();
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            header("Location: /project/public/crear-usuario?error=" . urlencode("Error interno del servidor"));
            exit();
        }
    }
    
    public function eliminarUsuario() {
        $id = $_POST['id'] ?? '';
        
        if (empty($id)) {
            header("Location: /project/public/usuarios?error=" . urlencode("ID de usuario no válido"));
            exit();
        }
        
        // No permitir eliminar al usuario actual
        if ($id == $_SESSION['usuario']['id']) {
            header("Location: /project/public/usuarios?error=" . urlencode("No puedes eliminar tu propia cuenta"));
            exit();
        }
        
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            header("Location: /project/public/usuarios?success=" . urlencode("Usuario eliminado exitosamente"));
            exit();
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            header("Location: /project/public/usuarios?error=" . urlencode("Error interno del servidor"));
            exit();
        }
    }
}
?>
