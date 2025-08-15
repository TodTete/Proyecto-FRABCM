<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../components/layout.php';
$usuario = AuthMiddleware::getUser();
$documentController = new DocumentController();
$notificationController = new NotificationController();

// Obtener documentos recientes
if ($usuario['rol'] === 'admin') {
    $documentos = $documentController->obtenerDocumentosRecientes();
} else {
    $documentos = $documentController->obtenerDocumentosRecientes($usuario['id']);
}

// Estadísticas
$stats = $documentController->obtenerEstadisticas($usuario['id'], $usuario['rol']);

$base_url_front = '/project/public/images/';

$base_url = '/project/server';

ob_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>/views/styles/dashboard.css">
<link rel="icon" href="<?php echo $base_url; ?>logo.ico" type="image/ico">

<div class="container">
    <div class="welcome-section" >
        <div class="text-welcom-section">
            <h1>¡Bienvenido al Sistema de Documentos!</h1>
            <p>Gestiona tus memorandos de manera eficiente y mantente al día con las notificaciones.</p>
        </div>
        <div class="image-welcome-section">
            <img src="<?php echo $base_url_front; ?>logo_saddo.png" alt="Logo del sistema" class="saddo-img">
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total de Documentos</div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i> </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Pendientes</div>
                <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-clock"></i> </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">En Proceso</div>
                <div class="stat-number"><?php echo $stats['proceso']; ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-hourglass-half"></i> </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Atendidos</div>
                <div class="stat-number"><?php echo $stats['atendidos']; ?></div>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i> </div>
        </div>
    </div>
    
    <div class="documents-section">
        <div class="section-header">
            <h2>Memorandos Recientes</h2>
        </div>
        
        <?php if (empty($documentos)): ?>
            <div class="empty-state">
                <h3>No hay documentos recientes</h3>
                <p>Los documentos aparecerán aquí una vez que se creen</p>
            </div>
        <?php else: ?>
            <table class="documents-table">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha Documento</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Urgencia</th>
                        <th>Estado</th>
                        <th>Fecha Límite</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documentos as $doc): ?>
                        <tr>
                            <td>
                                <span class="folio-highlight"><?php echo htmlspecialchars($doc['folio']); ?></span>
                            </td>
                            <td><?php echo $doc['fecha_documento'] ? date('d/m/Y', strtotime($doc['fecha_documento'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($doc['remitente_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($doc['destinatario_nombre']); ?></td>
                            <td>
                                <span class="urgencia-badge urgencia-<?php echo $doc['urgencia']; ?>">
                                    <?php echo ucfirst($doc['urgencia']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $doc['estatus_atencion']; ?>">
                                    <?php echo ucfirst($doc['estatus_atencion']); ?>
                                </span>
                            </td>
                            <td><?php echo $doc['fecha_limite'] ? date('d/m/Y', strtotime($doc['fecha_limite'])) : '-'; ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-sm btn-edit" onclick="editDocument(<?php echo $doc['id']; ?>)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <?php if ($doc['documento_blob']): ?>
                                        <button class="btn-sm btn-view" onclick="viewPDF(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-sm btn-download" onclick="downloadPDF(<?php echo $doc['id']; ?>)">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
function editDocument(id) {
    window.location.href = '<?php echo $base_url; ?>/editar-documento/' + id;
}

function viewPDF(id) {
    window.open('<?php echo $base_url; ?>/ver-pdf/' + id, '_blank');
}

function downloadPDF(id) {
    window.location.href = '<?php echo $base_url; ?>/descargar-pdf/' + id;
}
</script>

<?php
$content = ob_get_clean();
renderLayout('Dashboard', $content, $usuario, 'Panel de Control', 'dashboard');
?>