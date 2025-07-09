<?php
$base_url = '/project/public';
?>
<div class="header">
    <div class="header-left">
        <div class="logo">UTTECAM</div>
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
                        <button class="btn-edit-profile" onclick="abrirModalPerfil()">
                            <i class="fas fa-edit"></i> Editar Perfil
                        </button>
                        
                        <form action="<?php echo $base_url; ?>/logout" method="POST">
                            <button type="submit" class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <input type="email" id="correo_perfil" class="form-input" disabled 
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

<style>
.dropdown-actions {
    display: grid;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-edit-profile {
    width: 100%;
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-edit-profile:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    transform: translateY(-1px);
}

/* Modal de perfil */
.profile-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    animation: fadeIn 0.3s ease-out;
}

.profile-modal-content {
    position: relative;
    margin: 5% auto;
    width: 90%;
    max-width: 500px;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    animation: slideInDown 0.3s ease-out;
}

.profile-modal-header {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.profile-modal-header h3 {
    margin: 0;
    font-size: 1.2rem;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: background 0.3s ease;
}

.close-modal:hover {
    background: rgba(255,255,255,0.2);
}

.profile-modal-body {
    padding: 2rem;
    max-height: 70vh;
    overflow-y: auto;
}

.profile-modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-cancel {
    flex: 1;
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-1px);
}

.btn-save {
    flex: 1;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    border: none;
    padding: 0.75rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-save:hover {
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    transform: translateY(-1px);
}

@keyframes slideInDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .profile-modal-content {
        width: 95%;
        margin: 10% auto;
    }
    
    .profile-modal-body {
        padding: 1.5rem;
    }
    
    .profile-modal-actions {
        flex-direction: column;
    }
}
</style>

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
