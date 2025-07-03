<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/UserController.php';
require_once __DIR__ . '/../components/layout.php';

AuthMiddleware::requireRole('admin');
$usuario = AuthMiddleware::getUser();
$userController = new UserController();
$usuarios = $userController->obtenerUsuarios();
$base_url = '/project/public';

ob_start();
?>

<style>
.users-table-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

.table-header {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

.users-table th {
    background: #f8f9fa;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e1e5e9;
    position: sticky;
    top: 0;
    z-index: 10;
}

.users-table td {
    padding: 1rem;
    border-bottom: 1px solid #e1e5e9;
    vertical-align: middle;
}

.users-table tbody tr {
    transition: all 0.3s ease;
}

.users-table tbody tr:hover {
    background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar-table {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
    flex-shrink: 0;
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
    font-size: 1rem;
}

.user-email {
    color: #666;
    font-size: 0.9rem;
}

.user-id {
    color: #999;
    font-size: 0.8rem;
    font-family: monospace;
}

.role-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.role-admin {
    background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
    color: white;
}

.role-usuario {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.date-info {
    color: #666;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.actions-cell {
    text-align: center;
}

.btn-delete {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    font-size: 0.85rem;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
}

.btn-delete:disabled {
    background: #6c757d;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.current-user-indicator {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.stats-row {
    background: #f8f9fa;
    padding: 1rem;
    border-top: 1px solid #e1e5e9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #666;
}

.table-responsive {
    overflow-x: auto;
    max-height: 70vh;
    overflow-y: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .users-table {
        font-size: 0.85rem;
    }
    
    .users-table th,
    .users-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .user-avatar-table {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }
    
    .table-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .stats-row {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .users-table th:nth-child(3),
    .users-table td:nth-child(3) {
        display: none;
    }
}
</style>

<div class="container">
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
            <a href="<?php echo $base_url; ?>/crear-usuario" class="btn-secondary">
                <i class="fas fa-user-plus"></i> Crear Nuevo Usuario
            </a>
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

<script>
<?php if (isset($_GET['error'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['error'])); ?>', 'error');
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['success'])); ?>', 'success');
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
renderLayout('Gestión de Usuarios', $content, $usuario, 'Gestión de Usuarios', 'usuarios');
?>
