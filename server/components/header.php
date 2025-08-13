<?php
$base_url = '/project/public';
$base_url_front = '/project/server/views/';
?>
<link rel="icon" href="/project/public/images/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="<?php echo $base_url_front; ?>styles/header.css">
<header>
    <div class="header-left">
        <img src="<?php echo $base_url; ?>/images/logo.png" alt="SADDO" class="logo-uttecam" >
        <div class="page-title"><?php echo $page_title ?? 'Sistema de Documentos'; ?></div>
    </div>
    <div class="user-info">
        <div class="user-dropdown" id="userDropdown">
            <button class="user-trigger" onclick="toggleUserDropdown()">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                <span class="dropdown-arrow"><i class="fas fa-chevron-down"></i></span>
            </button>
            
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <div class="dropdown-avatar">
                        <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                    </div>
                    <div class="dropdown-name"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                    <div class="dropdown-email"><?php echo htmlspecialchars($usuario['correo']); ?></div>
                    <span class="dropdown-role"><?php echo ucfirst($usuario['rol']); ?></span>
                </div>
                
                <div class="dropdown-body">
                    <div class="dropdown-info">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-user"></i> Nombre:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-envelope"></i> Correo:</span>
                            <span class="info-value"><?php echo htmlspecialchars($usuario['correo']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-tag"></i> Rol:</span>
                            <span class="info-value"><?php echo ucfirst($usuario['rol']); ?></span>
                        </div>
                    </div>
                    
                    <div class="dropdown-actions">
                        <button class="edit-btn" onclick="abrirModalPerfil()" style="border-radius: 50px;">
                            <i class="fas fa-edit"></i> Editar Perfil
                        </button>
                    <form action="<?php echo $base_url; ?>/logout" method="POST">
                            <button type="submit" class="logout-btn" style="border-radius: 50px;">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</header>


<!-- Modal para editar perfil -->
<div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <h3><i class="fas fa-user-edit"></i> Editar Mi Perfil</h3>
            <button class="close-modal" onclick="cerrarModalPerfil()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="profile-modal-body">
            <form id="profileForm" action="<?php echo $base_url; ?>/actualizar-perfil" method="POST">
                <div class="form-group">
                    <label for="nombre_perfil"><i class="fas fa-user"></i> Nombre Completo *</label>
                    <input type="text" id="nombre_perfil" name="nombre" class="form-input" required 
                            value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="correo_perfil"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <input type="email" id="correo_perfil" class="form-input" style="border: 2px dotted orange;" disabled 
                            value="<?php echo htmlspecialchars($usuario['correo']); ?>">
                    <small style="color: #666;">El correo no se puede modificar</small>
                </div>
                
                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e1e5e9;">
                
                <h4 style="color: #333; margin-bottom: 1rem;">
                    <i class="fas fa-lock"></i> Cambiar Contraseña (Opcional)
                </h4>
                
                <div class="form-group">
                    <label for="contraseña_actual"><i class="fas fa-key"></i> Contraseña Actual</label>
                    <input type="password" id="contraseña_actual" name="contraseña_actual" class="form-input" 
                            placeholder="Ingresa tu contraseña actual">
                </div>
                
                <div class="form-group">
                    <label for="nueva_contraseña"><i class="fas fa-lock"></i> Nueva Contraseña</label>
                    <input type="password" id="nueva_contraseña" name="nueva_contraseña" class="form-input" 
                            placeholder="Mínimo 6 caracteres" minlength="6">
                </div>
                
                <div class="form-group">
                    <label for="confirmar_contraseña"><i class="fas fa-lock"></i> Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirmar_contraseña" name="confirmar_contraseña" class="form-input" 
                            placeholder="Repite la nueva contraseña">
                </div>
                
                <div class="profile-modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModalPerfil()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleUserDropdown() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('active');
}

function abrirModalPerfil() {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Cerrar dropdown
    document.getElementById('userDropdown').classList.remove('active');
}

function cerrarModalPerfil() {
    const modal = document.getElementById('profileModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Limpiar campos de contraseña
    document.getElementById('contraseña_actual').value = '';
    document.getElementById('nueva_contraseña').value = '';
    document.getElementById('confirmar_contraseña').value = '';
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('userDropdown');
    if (!dropdown.contains(event.target)) {
        dropdown.classList.remove('active');
    }
});

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(event) {
    const modal = document.getElementById('profileModal');
    if (event.target === modal) {
        cerrarModalPerfil();
    }
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalPerfil();
    }
});

// Validación del formulario
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const nuevaContraseña = document.getElementById('nueva_contraseña').value;
    const confirmarContraseña = document.getElementById('confirmar_contraseña').value;
    const contraseñaActual = document.getElementById('contraseña_actual').value;
    
    if (nuevaContraseña && !contraseñaActual) {
        e.preventDefault();
        alert('Debes ingresar tu contraseña actual para cambiarla');
        return;
    }
    
    if (nuevaContraseña && nuevaContraseña !== confirmarContraseña) {
        e.preventDefault();
        alert('Las contraseñas nuevas no coinciden');
        return;
    }
    
    if (nuevaContraseña && nuevaContraseña.length < 6) {
        e.preventDefault();
        alert('La nueva contraseña debe tener al menos 6 caracteres');
        return;
    }
});
</script>