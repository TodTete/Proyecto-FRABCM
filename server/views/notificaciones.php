<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/layouts/Layout.php';

$usuario = AuthMiddleware::getUser();
$notificationController = new NotificationController();
$notificaciones = $notificationController->obtenerNotificaciones($usuario['id']);
$base_url = '/project/public';

$layout = new Layout($usuario, 'Notificaciones', $base_url);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones - UTTECAM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
            padding: 1rem 2rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: slideDown 0.5s ease-out;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            font-style: italic;
            letter-spacing: 1px;
        }
        
        .page-title {
            font-size: 1.2rem;
            font-weight: 500;
            opacity: 0.9;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-name {
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .nav {
            background: white;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-bottom: 3px solid #4a7c59;
            animation: slideDown 0.6s ease-out;
        }
        
        .nav ul {
            list-style: none;
            display: flex;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav li {
            flex: 1;
        }
        
        .nav a {
            display: block;
            text-decoration: none;
            color: #4a7c59;
            font-weight: 600;
            padding: 1rem 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }
        
        .nav a:hover,
        .nav a.active {
            background: #f8f9fa;
            border-bottom-color: #ff8c42;
            color: #333;
        }
        
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
            animation: fadeInUp 0.7s ease-out;
        }
        
        .notifications-header {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a8c69 0%, #6a9c79 100%);
            transform: translateY(-2px);
        }
        
        .notifications-list {
            display: grid;
            gap: 1rem;
        }
        
        .notification-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #4a7c59;
            transition: all 0.3s ease;
            animation: fadeIn 0.8s ease-out;
            cursor: pointer;
        }
        
        .notification-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .notification-card.unread {
            border-left-color: #ff8c42;
            background: linear-gradient(135deg, #fff9f5 0%, #ffffff 100%);
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .notification-type {
            background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .notification-date {
            color: #666;
            font-size: 0.85rem;
        }
        
        .notification-message {
            color: #333;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .notification-status {
            margin-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-unread {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background: #d4edda;
            color: #155724;
        }
        
        .btn-mark-read {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-mark-read:hover {
            background: #138496;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .footer {
            background: #6c757d;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }
        
        /* Animaciones */
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .notification-header {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .notification-status {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <?php echo $layout->header(); ?>
    <?php echo $layout->nav(); ?>
    
    <div class="container">
        <div class="notifications-header">
            <h2 style="color: #333;">
                Mis Notificaciones 
                <small style="color: #666; font-weight: normal;">(<?php echo count($notificaciones); ?> total)</small>
            </h2>
            <?php if (!empty($notificaciones)): ?>
                <form action="<?php echo $base_url; ?>/marcar-todas-leidas" method="POST" style="display: inline;">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check-double"></i> Marcar todas como leídas
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="notifications-list">
            <?php if (empty($notificaciones)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>No tienes notificaciones</h3>
                    <p>Aquí aparecerán las notificaciones de documentos que te hayan sido enviados.<br>
                    Mantente al día con todas las actualizaciones importantes.</p>
                </div>
            <?php else: ?>
                <?php foreach ($notificaciones as $notif): ?>
                    <div class="notification-card <?php echo !$notif['leida'] ? 'unread' : ''; ?>" 
                         onclick="markAsRead(<?php echo $notif['id']; ?>, this)">
                        <div class="notification-header">
                            <span class="notification-type"><?php echo ucfirst(str_replace('_', ' ', $notif['tipo'])); ?></span>
                            <span class="notification-date"><?php echo date('d/m/Y H:i', strtotime($notif['creado_en'])); ?></span>
                        </div>
                        
                        <div class="notification-message">
                            <?php echo htmlspecialchars($notif['mensaje']); ?>
                        </div>
                        
                        <div class="notification-status">
                            <span class="status-badge <?php echo !$notif['leida'] ? 'status-unread' : 'status-read'; ?>">
                                <?php echo !$notif['leida'] ? 'No leída' : 'Leída'; ?>
                            </span>
                            
                            <?php if (!$notif['leida']): ?>
                                <button class="btn-mark-read" onclick="event.stopPropagation(); markAsRead(<?php echo $notif['id']; ?>, this)">
                                    <i class="fas fa-check"></i> Marcar como leída
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        © 2025 UTTECAM. Todos los derechos reservados.
    </div>
    
    <script>
        function markAsRead(id, element) {
            fetch('<?php echo $base_url; ?>/marcar-notificacion-leida', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI without reloading the page
                    const notificationCard = element.closest('.notification-card');
                    notificationCard.classList.remove('unread');
                    const statusBadge = notificationCard.querySelector('.status-badge');
                    statusBadge.classList.remove('status-unread');
                    statusBadge.classList.add('status-read');
                    statusBadge.textContent = 'Leída';
                    const markAsReadButton = notificationCard.querySelector('.btn-mark-read');
                    if (markAsReadButton) {
                        markAsReadButton.remove();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Actualizar notificaciones cada 30 segundos
        setInterval(function() {
            // No es necesario recargar la página completamente
            // Se puede implementar una actualización asincrónica de las notificaciones
        }, 30000);
    </script>
</body>
</html>
