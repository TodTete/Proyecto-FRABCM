<?php

class AuthMiddleware {
    public static function isAuthenticated() {
        return isset($_SESSION['usuario']) && !empty($_SESSION['usuario']);
    }
    
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: /server/login');
            exit();
        }
    }
    
    public static function requireRole($role) {
        self::requireAuth();
        
        if ($_SESSION['usuario']['rol'] !== $role) {
            http_response_code(403);
            echo "Acceso denegado";
            exit();
        }
    }
    
    public static function getUser() {
        return $_SESSION['usuario'] ?? null;
    }
}
?>
