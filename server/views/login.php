<!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión - UTTECAM</title>
    <link rel="stylesheet" href="/project/server/views/styles/var.css" />
    <link rel="stylesheet" href="/project/server/views/styles/main.css" />
    <link rel="stylesheet" href="/project/server/views/styles/login.css" />
    <link rel="icon" href="/project/public/images/logo.ico" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* ... Animaciones y media queries (idénticas a las que ya incluiste) ... */
        .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(74, 124, 89, 0.9);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        }
        .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid rgba(255, 255, 255, 0.3);
        border-top: 4px solid white;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
        }
        @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
        }
    </style>
    </head>
    <body>

    <!-- LOADING OVERLAY -->
    <div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <div class="loading-spinner"></div>
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
    document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault(); 

    const loginBtn = document.getElementById('loginBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');

    loginBtn.disabled = true;
    loginBtn.innerHTML = '<div class="loading-spinner" style="width:20px; height:20px; border-width:3px; display:inline-block; vertical-align:middle; margin-right:8px;"></div>Iniciando sesión...';
    loadingOverlay.style.display = 'flex';

    setTimeout(() => {
        e.target.submit(); 
    }, 2000); 
});

</script>

</body>
</html>
