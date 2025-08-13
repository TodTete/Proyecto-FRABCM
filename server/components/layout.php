<?php
function renderLayout($title, $content, $usuario, $page_title = '', $current_page = '') {
    $base_url = '/project/public';
    
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> - UTTECAM</title>
        <link rel="stylesheet" href="../server/assets/styles.css">
        <?php include __DIR__ . '/fontawesome.php'; ?>
    </head>
    <body>
        <?php 
        include __DIR__ . '/header.php';
        include __DIR__ . '/navigation.php';
        ?>
        
        <?php echo $content; ?>
    <link rel="stylesheet" href="/project/server/views/styles/footer.css">
    <footer>
        <p>&copy; 2025 UTTECAM. Todos los Derechos Reservados.</p>
    </footer>
        <script>
            // Función para mostrar notificaciones toast
            function showToast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 500);
                }, 3000);
            }
        </script>
    </body>
    </html>
    <?php
}
?>
