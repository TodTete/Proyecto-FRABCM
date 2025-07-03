<?php
$base_url = '/project/public';
$current_page = $current_page ?? '';
?>
<div class="nav">
    <ul>
        <li><a href="<?php echo $base_url; ?>/dashboard" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="<?php echo $base_url; ?>/documentos" class="<?php echo $current_page === 'documentos' ? 'active' : ''; ?>">Documentos</a></li>
        <li><a href="<?php echo $base_url; ?>/pendientes" class="<?php echo $current_page === 'pendientes' ? 'active' : ''; ?>">Pendientes</a></li>
        <?php if ($usuario['rol'] === 'admin'): ?>
            <li><a href="<?php echo $base_url; ?>/usuarios" class="<?php echo $current_page === 'usuarios' ? 'active' : ''; ?>">Usuarios</a></li>
        <?php endif; ?>
    </ul>
</div>
