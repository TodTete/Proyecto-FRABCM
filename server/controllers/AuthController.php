<?php
require_once __DIR__ . '/../db.php';

class AuthController {
    public function login($correo, $contraseña) {
        global $pdo;

        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                return ['error' => 'Correo no registrado'];
            }

            if (!password_verify($contraseña, $usuario['contraseña_hash'])) {
                return ['error' => 'Contraseña incorreta'];
            }

            // Iniciar sesión
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'correo' => $usuario['correo'],
                'rol' => $usuario['rol']
            ];

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }
    
    public function logout() {
        session_destroy();
        session_start();
    }
    
    public function register($nombre, $correo, $contraseña, $rol = 'usuario') {
        global $pdo;
        
        try {
            // Verificar si el correo ya existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->fetch()) {
                return ['error' => 'El correo ya está registrado'];
            }
            
            // Crear usuario
            $contraseña_hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, contraseña_hash, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $correo, $contraseña_hash, $rol]);
            
            return ['success' => true, 'id' => $pdo->lastInsertId()];
        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            return ['error' => 'Error interno del servidor'];
        }
    }
}
?>
