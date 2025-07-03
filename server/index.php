<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/UserController.php';
require_once __DIR__ . '/controllers/DocumentController.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

class Router {
    private $routes = [];
    private $base_path = '';
    
    public function __construct($base_path = '') {
        $this->base_path = $base_path;
    }
    
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover el base path si existe
        if ($this->base_path && strpos($path, $this->base_path) === 0) {
            $path = substr($path, strlen($this->base_path));
        }
        
        // Si la ruta está vacía, establecer como raíz
        if (empty($path) || $path === '/') {
            $path = '/';
        }
        
        // Debug: mostrar la ruta procesada (remover en producción)
        // error_log("Ruta procesada: " . $path);
        
        if (isset($this->routes[$method][$path])) {
            call_user_func($this->routes[$method][$path]);
        } else {
            $this->notFound($path);
        }
    }
    
    private function notFound($path) {
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>Página no encontrada - UTTECAM</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .error-container { max-width: 500px; margin: 0 auto; }
                h1 { color: #4a7c59; }
                p { color: #666; margin: 20px 0; }
                a { color: #ff8c42; text-decoration: none; }
                a:hover { text-decoration: underline; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>404 - Página no encontrada</h1>
                <p>La ruta '<strong>$path</strong>' no existe.</p>
                <p><a href='/project/public/'>Ir al inicio</a></p>
            </div>
        </body>
        </html>";
    }
}

// Detectar el base path automáticamente
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_path = $script_name === '/' ? '' : $script_name;

$router = new Router($base_path);

// Rutas públicas
$router->get('/', function() {
    if (AuthMiddleware::isAuthenticated()) {
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/dashboard');
        exit();
    }
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/login');
    exit();
});

$router->get('/login', function() {
    if (AuthMiddleware::isAuthenticated()) {
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/dashboard');
        exit();
    }
    include __DIR__ . '/views/login.php';
});

$router->post('/login', function() {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    
    $auth = new AuthController();
    $resultado = $auth->login($correo, $contraseña);
    
    if (isset($resultado['success'])) {
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/dashboard');
    } else {
        $error = urlencode($resultado['error']);
        header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/login?error=' . $error);
    }
    exit();
});

// Rutas protegidas generales
$router->get('/dashboard', function() {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/views/dashboard.php';
});

$router->get('/perfil', function() {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/views/perfil.php';
});

$router->get('/notificaciones', function() {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/views/notificaciones.php';
});

$router->get('/documentos', function() {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/views/documentos.php';
});

$router->get('/subir-documento', function() {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/views/subir-documento.php';
});

$router->post('/subir-documento', function() {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    $controller->subirDocumento();
});

// Rutas solo para administradores
$router->get('/usuarios', function() {
    AuthMiddleware::requireRole('admin');
    include __DIR__ . '/views/usuarios.php';
});

$router->get('/crear-usuario', function() {
    AuthMiddleware::requireRole('admin');
    include __DIR__ . '/views/crear-usuario.php';
});

$router->post('/crear-usuario', function() {
    AuthMiddleware::requireRole('admin');
    $controller = new UserController();
    $controller->crearUsuario();
});

$router->post('/eliminar-usuario', function() {
    AuthMiddleware::requireRole('admin');
    $controller = new UserController();
    $controller->eliminarUsuario();
});

$router->post('/logout', function() {
    AuthMiddleware::requireAuth();
    $auth = new AuthController();
    $auth->logout();
    header('Location: ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/project/public/login');
    exit();
});

$router->run();
?>
