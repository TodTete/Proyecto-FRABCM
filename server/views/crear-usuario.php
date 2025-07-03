<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
AuthMiddleware::requireRole('admin');
$usuario = AuthMiddleware::getUser();
$base_url = '/project/public';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - UTTECAM</title>
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
        
        .btn-logout:hover {
            background: linear-gradient(135deg, #ff7b2e 0%, #ff6a1a 100%);
            transform: translateY(-1px);
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
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 2rem;
            animation: fadeInUp 0.7s ease-out;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border-top: 4px solid #4a7c59;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .form-header h2 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .form-header p {
            color: #666;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            animation: fadeIn 0.8s ease-out;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .form-input,
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #4a7c59;
            background: white;
            box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1);
            transform: translateY(-2px);
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #5a8c69 0%, #6a9c79 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 124, 89, 0.3);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.8;
        }
        
        .btn-submit .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        .btn-submit.loading .spinner {
            display: inline-block;
        }
        
        .error-message,
        .success-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: slideInDown 0.5s ease-out;
        }
        
        .error-message {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        .success-message {
            background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
            color: white;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #4a7c59;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            color: #ff8c42;
            transform: translateX(-5px);
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
        
        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .header-left {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .nav ul {
                flex-direction: column;
            }
            
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="logo">UTTECAM</div>
            <div class="page-title">Crear Usuario</div>
        </div>
        <div class="user-info">
            <span class="user-name">Bienvenido, <?php echo htmlspecialchars($usuario['nombre']); ?></span>
            <form action="<?php echo $base_url; ?>/logout" method="POST" style="display: inline;">
                <button type="submit" class="btn-logout">Cerrar Sesión</button>
            </form>
        </div>
    </div>
    
    <div class="nav">
        <ul>
            <li><a href="<?php echo $base_url; ?>/dashboard">Dashboard</a></li>
            <li><a href="<?php echo $base_url; ?>/documentos">Documentos</a></li>
            <li><a href="<?php echo $base_url; ?>/notificaciones">Notificaciones</a></li>
            <li><a href="<?php echo $base_url; ?>/usuarios" class="active">Usuarios</a></li>
            <li><a href="<?php echo $base_url; ?>/perfil">Perfil</a></li>
        </ul>
    </div>
    
    <div class="container">
        <a href="<?php echo $base_url; ?>/usuarios" class="back-btn">
            ← Volver a Usuarios
        </a>
        
        <div class="card">
            <div class="form-header">
                <h2>Crear Nuevo Usuario</h2>
                <p>Complete todos los campos para crear una nueva cuenta</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    ⚠ <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    ✓ <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo $base_url; ?>/crear-usuario" method="POST" id="createUserForm">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required placeholder="Ej: Juan Pérez García">
                </div>
                
                <div class="form-group">
                    <label for="correo">Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" class="form-input" required placeholder="ejemplo@uttecam.edu.mx">
                </div>
                
                <div class="form-group">
                    <label for="contraseña">Contraseña *</label>
                    <input type="password" id="contraseña" name="contraseña" class="form-input" required placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol del Usuario *</label>
                    <select id="rol" name="rol" class="form-select" required>
                        <option value="">Seleccionar rol</option>
                        <option value="usuario">Usuario Regular</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    <div class="spinner"></div>
                    👤 Crear Usuario
                </button>
            </form>
        </div>
    </div>
    
    <div class="footer">
        © 2025 UTTECAM. Todos los derechos reservados.
    </div>
    
    <script>
        document.getElementById('createUserForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<div class="spinner"></div>Creando usuario...';
        });
    </script>
</body>
</html>
