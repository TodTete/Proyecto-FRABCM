<?php
// Punto de entrada principal
session_start();
require_once __DIR__ . '/../server/config.php';
require_once __DIR__ . '/../server/db.php';
require_once __DIR__ . '/../server/controllers/AuthController.php';
require_once __DIR__ . '/../server/controllers/UserController.php';
require_once __DIR__ . '/../server/controllers/DocumentController.php';
require_once __DIR__ . '/../server/controllers/NotificationController.php';
require_once __DIR__ . '/../server/middleware/AuthMiddleware.php';

// Definir la ruta base
define('BASE_URL', '/project/public');

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/project/public';

if (strpos($request_uri, $base_path) === 0) {
    $route = substr($request_uri, strlen($base_path));
} else {
    $route = $request_uri;
}

if (empty($route) || $route === '/') {
    $route = '/';
}

$route = parse_url($route, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Rutas públicas
if ($route === '/' || $route === '') {
    if (AuthMiddleware::isAuthenticated()) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit();
    }
    header('Location: ' . BASE_URL . '/login');
    exit();
}
else if ($route === '/login' && $method === 'GET') {
    if (AuthMiddleware::isAuthenticated()) {
        header('Location: ' . BASE_URL . '/dashboard');
        exit();
    }
    include __DIR__ . '/../server/views/login.php';
    exit();
}
else if ($route === '/login' && $method === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $contraseña = $_POST['contraseña'] ?? '';
    
    $auth = new AuthController();
    $resultado = $auth->login($correo, $contraseña);
    
    if (isset($resultado['success'])) {
        header('Location: ' . BASE_URL . '/dashboard');
    } else {
        $error = urlencode($resultado['error']);
        header('Location: ' . BASE_URL . '/login?error=' . $error);
    }
    exit();
}
// Rutas protegidas generales
else if ($route === '/dashboard') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/dashboard.php';
    exit();
}
else if ($route === '/perfil') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/perfil.php';
    exit();
}
// Cambiar la ruta de notificaciones por pendientes
else if ($route === '/pendientes') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/pendientes.php';
    exit();
}
else if ($route === '/notificaciones') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/notificaciones.php';
    exit();
}
else if ($route === '/documentos') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/documentos.php';
    exit();
}
else if ($route === '/subir-documento' && $method === 'GET') {
    AuthMiddleware::requireAuth();
    include __DIR__ . '/../server/views/subir-documento.php';
    exit();
}
else if ($route === '/subir-documento' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    $controller->subirDocumento();
    exit();
}
else if (preg_match('/^\/editar-documento\/(\d+)$/', $route, $matches)) {
    AuthMiddleware::requireAuth();
    $_GET['id'] = $matches[1];
    include __DIR__ . '/../server/views/editar-documento.php';
    exit();
}
// Agregar ruta para actualizar estatus de destinatario
else if ($route === '/actualizar-estatus-documento' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    
    $memorando_id = $_POST['memorando_id'] ?? 0;
    $estatus = $_POST['estatus'] ?? '';
    
    if ($controller->puedeEditarEstatus($memorando_id, $_SESSION['usuario']['id'])) {
        $success = $controller->actualizarEstatusDestinatario($memorando_id, $_SESSION['usuario']['id'], $estatus);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Sin permisos']);
    }
    exit();
}

// Agregar ruta para obtener destinatarios de documento
else if (preg_match('/^\/api\/destinatarios-documento\/(\d+)$/', $route, $matches)) {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    $destinatarios = $controller->obtenerDestinatariosDocumento($matches[1]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'destinatarios' => $destinatarios]);
    exit();
}

// Actualizar la ruta de actualizar documento para manejar estatus de destinatario
else if ($route === '/actualizar-documento' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    
    $id = $_POST['id'] ?? 0;
    $usuario_id = $_SESSION['usuario']['id'];
    
    // Verificar si es actualización de estatus de destinatario
    if (isset($_POST['mi_estatus'])) {
        $estatus = $_POST['mi_estatus'];
        $comentarios = $_POST['comentarios'] ?? '';
        
        if ($controller->puedeEditarEstatus($id, $usuario_id)) {
            if ($controller->actualizarEstatusDestinatario($id, $usuario_id, $estatus, $comentarios)) {
                header('Location: ' . BASE_URL . '/pendientes?success=' . urlencode('Estado actualizado exitosamente'));
            } else {
                header('Location: ' . BASE_URL . '/editar-documento/' . $id . '?error=' . urlencode('Error al actualizar el estado'));
            }
        } else {
            header('Location: ' . BASE_URL . '/documentos?error=' . urlencode('Sin permisos para actualizar este documento'));
        }
    } else {
        // Actualización normal del documento (solo para remitente/admin)
        $documento = $controller->obtenerDocumentoPorId($id);
        $esRemitente = $documento['remitente_id'] == $usuario_id;
        $esAdmin = $_SESSION['usuario']['rol'] === 'admin';
        
        if ($esRemitente || $esAdmin) {
            $datos = [
                'folio' => $_POST['folio'] ?? '',
                'entidad_productora' => $_POST['entidad_productora'] ?? '',
                'fecha_documento' => $_POST['fecha_documento'] ?? null,
                'contenido' => $_POST['contenido'] ?? '',
                'urgencia' => $_POST['urgencia'] ?? 'ordinario',
                'area_destino_id' => $_POST['area_destino_id'] ?? null,
                'fecha_requerida_respuesta' => $_POST['fecha_requerida_respuesta'] ?? null,
                'fecha_limite' => $_POST['fecha_limite'] ?? null
            ];
            
            if ($controller->actualizarDocumento($id, $datos)) {
                header('Location: ' . BASE_URL . '/documentos?success=' . urlencode('Documento actualizado exitosamente'));
            } else {
                header('Location: ' . BASE_URL . '/editar-documento/' . $id . '?error=' . urlencode('Error al actualizar el documento'));
            }
        } else {
            header('Location: ' . BASE_URL . '/documentos?error=' . urlencode('Sin permisos para editar este documento'));
        }
    }
    exit();
}
else if (preg_match('/^\/ver-pdf\/(\d+)$/', $route, $matches)) {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    $documento = $controller->obtenerDocumentoPorId($matches[1]);
    
    if ($documento && $documento['documento_blob']) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $documento['folio'] . '.pdf"');
        echo $documento['documento_blob'];
    } else {
        http_response_code(404);
        echo "PDF no encontrado";
    }
    exit();
}
else if (preg_match('/^\/descargar-pdf\/(\d+)$/', $route, $matches)) {
    AuthMiddleware::requireAuth();
    $controller = new DocumentController();
    $documento = $controller->obtenerDocumentoPorId($matches[1]);
    
    if ($documento && $documento['documento_blob']) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $documento['folio'] . '.pdf"');
        echo $documento['documento_blob'];
    } else {
        http_response_code(404);
        echo "PDF no encontrado";
    }
    exit();
}
// Ruta para actualizar perfil
else if ($route === '/actualizar-perfil' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    require_once __DIR__ . '/../server/controllers/ProfileController.php';
    $controller = new ProfileController();
    $resultado = $controller->actualizarPerfil();
    
    if (isset($resultado['success'])) {
        header('Location: ' . BASE_URL . '/dashboard?success=' . urlencode('Perfil actualizado exitosamente'));
    } else {
        header('Location: ' . BASE_URL . '/dashboard?error=' . urlencode($resultado['error']));
    }
    exit();
}
// Agregar rutas para manejar las notificaciones:
else if ($route === '/marcar-notificacion-leida' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $controller = new NotificationController();
    $id = $_POST['id'] ?? 0;
    
    $success = $controller->marcarComoLeida($id, $_SESSION['usuario']['id']);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit();
}
else if ($route === '/marcar-todas-leidas' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $controller = new NotificationController();
    
    $success = $controller->marcarTodasComoLeidas($_SESSION['usuario']['id']);
    
    if ($success) {
        header('Location: ' . BASE_URL . '/notificaciones?success=' . urlencode('Todas las notificaciones marcadas como leídas'));
    } else {
        header('Location: ' . BASE_URL . '/notificaciones?error=' . urlencode('Error al marcar las notificaciones'));
    }
    exit();
}
else if ($route === '/api/notificaciones-count') {
    AuthMiddleware::requireAuth();
    $controller = new NotificationController();
    $count = $controller->contarNotificacionesNoLeidas($_SESSION['usuario']['id']);
    
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
    exit();
}
// Rutas solo para administradores
else if ($route === '/usuarios') {
    AuthMiddleware::requireRole('admin');
    include __DIR__ . '/../server/views/usuarios.php';
    exit();
}
else if ($route === '/crear-usuario' && $method === 'GET') {
    AuthMiddleware::requireRole('admin');
    include __DIR__ . '/../server/views/crear-usuario.php';
    exit();
}
else if ($route === '/crear-usuario' && $method === 'POST') {
    AuthMiddleware::requireRole('admin');
    $controller = new UserController();
    $controller->crearUsuario();
    exit();
}
else if ($route === '/eliminar-usuario' && $method === 'POST') {
    AuthMiddleware::requireRole('admin');
    $controller = new UserController();
    $controller->eliminarUsuario();
    exit();
}
else if ($route === '/logout' && $method === 'POST') {
    AuthMiddleware::requireAuth();
    $auth = new AuthController();
    $auth->logout();
    header('Location: ' . BASE_URL . '/login');
    exit();
}
else {
    // Página no encontrada
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
            <p>La ruta '<strong>$route</strong>' no existe.</p>
            <p><a href='" . BASE_URL . "'>Ir al inicio</a></p>
        </div>
    </body>
    </html>";
    exit();
}
?>
