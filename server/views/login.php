<!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión - UTTECAM</title>
    <link rel="stylesheet" href="/project/server/views/styles/var.css" />
    <link rel="stylesheet" href="/project/server/views/styles/main.css" />
    <link rel="stylesheet" href="/project/server/views/styles/login.css" />
    <link rel="icon" href="/project/public/images/logo.ico" type="image/ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="icon" href="/project/public/images/favicon.ico" type="image/x-icon">
    </head>
    <body>

    <!-- LOADING OVERLAY -->
<!-- LOADING OVERLAY -->
<div id="loadingOverlay" class="loading-overlay">
  <div class="loading-content">
    <!-- Se corrige la ruta (src) y se añade la clase "loading-logo" -->
    <img src="/project/public/images/head-motocle.png" alt="Cargando..." class="loading-logo">
    <p>Cargando...</p>
  </div>
</div>

    <?php require_once __DIR__ . '/components/header.html'; ?>

    <div class="main-container">
    <div class="image-section" style="padding:0;">
        <img src="/project/public/images/fondo.jpg" alt="Fondo UTTECAM" style="width:100%; height:100%; object-fit:cover; border-top-right-radius:10px; border-bottom-right-radius:10px; display:block;">
    </div>

    <div class="login-section">
        <div class="login-card">
        <div class="login-header">
            <h2>Iniciar Sesión</h2>
            <p>Completa los campos para acceder a tu cuenta</p>
        </div>

        <div class="login-form">
            <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <span><i class="fa-solid fa-triangle-exclamation"></i></span>
                <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
            </div>
            <?php endif; ?>

            <form id="loginForm" action="/project/public/login" method="POST">
            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <div class="input-container">
                <span class="input-icon"><i class="fa-regular fa-envelope"></i></span>
                <input 
                    type="email" 
                    id="correo" 
                    name="correo" 
                    class="form-input"
                    placeholder="example@correo.com"
                    required
                >
                </div>
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <div class="input-container">
                <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                <input 
                type="password" 
                id="contraseña" 
                name="contraseña" 
                class="form-input"
                placeholder="••••••••"
                required
                >
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">Iniciar Sesión</button>
            </form>
        </div>
        </div>
    </div>
    </div>

    <?php require_once __DIR__ . '/components/footer.html'; ?>

    <script>
        /**
   * Evento que captura el envío del formulario de login.
   * 
   * - Previene el envío inmediato con `preventDefault()`.
   * - Deshabilita el botón de inicio de sesión para evitar clics repetidos.
   * - Muestra una pantalla de carga mientras se procesa la solicitud.
   * - Después de 2 segundos, envía el formulario de manera normal.
   */
    document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita el envío inmediato del formulario

    const loginBtn = document.getElementById('loginBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');

    loginBtn.disabled = true;
    loadingOverlay.style.display = 'flex';
 // Simulación de carga (2 segundos) antes de enviar el formulario
    setTimeout(() => {
        e.target.submit(); // Envía el formulario después del tiempo de espera
    }, 2000);
    }, 2000); 
});
</script>

</body>
</html>
