<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../components/layout.php';

AuthMiddleware::requireRole('admin');
$usuario = AuthMiddleware::getUser();
$userController = new UserController();
$usuarios = $userController->obtenerUsuarios();
$base_url = '/project/public';
$base_url_front = '/project/server';

ob_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="<?php echo $base_url_front; ?>/views/styles/usuarios.css">

<style>
    .container-user{
        min-height: 80vh;
    }
    .password-input-container {
        position: relative;
    }

    .password-input-container .form-input {
        padding-right: 45px; /* Espacio para el icono */
    }

    .password-input-container #togglePasswordIcon {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d; /* Color gris neutro */
    }

</style>


<div class="container container-user">
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars(urldecode($_GET['error'])); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
        </div>
    <?php endif; ?>
    
    <div class="users-table-container">
        <div class="table-header">
            <h2><i class="fas fa-users"></i> Gestión de Usuarios del Sistema</h2>
            <button class="btn-secondary" style="border-radius: 50px;" onclick="abrirModalCrearUsuario()">
                <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
            </button>
        </div>
        
        <?php if (empty($usuarios)): ?>
            <div class="empty-state" style="padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">
                    <i class="fas fa-users"></i>
                </div>
                <h3>No hay usuarios registrados</h3>
                <p>Crea el primer usuario para comenzar</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Usuario</th>
                            <th><i class="fas fa-tag"></i> Rol</th>
                            <th><i class="fas fa-calendar-plus"></i> Fecha de Registro</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-table">
                                            <?php echo strtoupper(substr($user['nombre'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo htmlspecialchars($user['nombre']); ?></div>
                                            <div class="user-email">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['correo']); ?>
                                            </div>
                                            <div class="user-id">ID: #<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['rol']; ?>">
                                        <i class="fas fa-<?php echo $user['rol'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                                        <?php echo ucfirst($user['rol']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($user['creado_en'])); ?>
                                        <br>
                                        <small style="color: #999;">
                                            <?php echo date('H:i', strtotime($user['creado_en'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td class="actions-cell">
                                    <?php if ($user['id'] != $usuario['id']): ?>
                                        <form action="<?php echo $base_url; ?>/eliminar-usuario" method="POST" 
                                                onsubmit="return confirm('¿Estás seguro de eliminar a <?php echo htmlspecialchars($user['nombre']); ?>? Esta acción no se puede deshacer.')" 
                                                style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-delete">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="current-user-indicator">
                                            <i class="fas fa-user-check"></i> Tu cuenta
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="stats-row">
                <span><strong>Total de usuarios:</strong> <?php echo count($usuarios); ?></span>
                <span><strong>Administradores:</strong> <?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; })); ?></span>
                <span><strong>Usuarios regulares:</strong> <?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'usuario'; })); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="crearUsuarioModal" class="profile-modal" style="display:none;">
    <div class="profile-modal-content" style="max-width: 600px;">
        <div class="profile-modal-header">
            <h3><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h3>
            <button class="close-modal" onclick="cerrarModalCrearUsuario()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="profile-modal-body">
            <form id="createUserForm" action="<?php echo $base_url; ?>/crear-usuario" method="POST">
                <div class="form-group">
                    <label for="nombre"><i class="fas fa-user"></i> Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" class="form-input" required placeholder="Ej: Juan Pérez García">
                </div>
                
                <div class="form-group">
                    <label for="correo"><i class="fas fa-envelope"></i> Correo Electrónico *</label>
                    <input type="email" id="correo" name="correo" class="form-input" required placeholder="ejemplo@uttecam.edu.mx">
                </div>
                
                <div class="form-group">
                    <label for="contraseña"><i class="fas fa-lock"></i> Contraseña *</label>
                    <div class="password-input-container">
                        <input type="password" id="contraseña" name="contraseña" class="form-input" required placeholder="Mínimo 6 caracteres" minlength="6">
                        <i class="fas fa-eye" id="togglePasswordIcon"></i>
                    </div>
                    <div id="caps-warning" style="color: red; display: none;">
                        ⚠ Bloq Mayús está activado
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="rol"><i class="fas fa-user-tag"></i> Rol del Usuario *</label>
                    <select id="rol" name="rol" class="form-select" required>
                        <option value="">Seleccionar rol</option>
                        <option value="usuario">Usuario Regular</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="profile-modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModalCrearUsuario()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-save" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div><script>
// ===================================================================
// SCRIPT PRINCIPAL
// ===================================================================

// Selección de elementos del DOM
const passwordInput = document.getElementById("contraseña");
const capsWarning = document.getElementById("caps-warning");
const togglePasswordIcon = document.getElementById('togglePasswordIcon');

// ===================================================================
// Detección de Bloq Mayús mejorada y más rápida
// ===================================================================
function checkCapsLock(event) {
  // La función event.getModifierState() es la forma moderna y confiable
  // de saber si Bloq Mayús está activo.
  if (event.getModifierState("CapsLock")) {
    capsWarning.style.display = "block";
  } else {
    capsWarning.style.display = "none";
  }
}

// 1. Usamos 'keydown' para que el aviso aparezca tan pronto presionas una letra (no después).
passwordInput.addEventListener('keydown', checkCapsLock);

// 2. Usamos 'keyup' para detectar el cambio de estado de la propia tecla "Bloq Mayús".
passwordInput.addEventListener('keyup', checkCapsLock);

// 3. Usamos 'focus' para verificar si Bloq Mayús ya estaba activo al hacer clic en el campo.
passwordInput.addEventListener('focus', checkCapsLock);

// 4. Usamos 'blur' para ocultar el aviso cuando sales del campo, por limpieza.
passwordInput.addEventListener('blur', () => {
    capsWarning.style.display = 'none';
});


// Funcionalidad para mostrar/ocultar contraseña
togglePasswordIcon.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

// Ocultar alertas automáticamente
setTimeout(() => {
    document.querySelectorAll('.success-message, .error-message').forEach(el => {
        el.style.transition = "opacity 0.5s ease";
        el.style.opacity = "0";
        setTimeout(() => el.remove(), 500);
    });
}, 3000);

// Funciones para el Modal
function abrirModalCrearUsuario() {
    document.getElementById('crearUsuarioModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalCrearUsuario() {
    document.getElementById('crearUsuarioModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('createUserForm').reset();
    
    // Restaurar el campo de contraseña y el icono a su estado inicial
    passwordInput.setAttribute('type', 'password');
    togglePasswordIcon.classList.remove('fa-eye-slash');
    togglePasswordIcon.classList.add('fa-eye');
    capsWarning.style.display = 'none';
}

// Evento para cerrar el modal al hacer clic fuera de él
window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('crearUsuarioModal')) {
        cerrarModalCrearUsuario();
    }
});

// Evento para cerrar el modal al presionar la tecla "Escape"
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalCrearUsuario();
    }
});

// Desactivar botón al enviar formulario para evitar envíos múltiples
document.getElementById('createUserForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<div class="spinner"></div> Creando usuario...';
});
</script>

<?php
$content = ob_get_clean();
renderLayout('Gestión de Usuarios', $content, $usuario, 'Gestión de Usuarios', 'usuarios');
?>