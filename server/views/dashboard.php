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

$base_url = '/project/public';

ob_start();
?>

<style>
.welcome-section {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 5px 20px rgba(74, 124, 89, 0.2);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #4a7c59;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #4a7c59;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.documents-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.documents-table {
    width: 100%;
    border-collapse: collapse;
}

.documents-table th,
.documents-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e1e5e9;
}

.documents-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.documents-table tbody tr:hover {
    background: #f8f9fa;
}

.folio-highlight {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-weight: bold;
    font-size: 0.9rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pendiente { background: #fff3cd; color: #856404; }
.status-proceso { background: #d1ecf1; color: #0c5460; }
.status-atendido { background: #d4edda; color: #155724; }

.urgencia-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.urgencia-ordinario { background: #f8f9fa; color: #666; }
.urgencia-urgente { background: #f8d7da; color: #721c24; }

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-edit {
    background: #17a2b8;
    color: white;
}

.btn-view {
    background: #28a745;
    color: white;
}

.btn-download {
    background: #6c757d;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

@media (max-width: 768px) {
    .documents-table {
        font-size: 0.8rem;
    }
    
    .documents-table th,
    .documents-table td {
        padding: 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="container">
    <div class="welcome-section">
        <h1>¡Bienvenido al Sistema de Documentos!</h1>
        <p>Gestiona tus memorandos de manera eficiente y mantente al día con las notificaciones.</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total de Documentos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['pendientes']; ?></div>
            <div class="stat-label">Pendientes</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['proceso']; ?></div>
            <div class="stat-label">En Proceso</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $stats['atendidos']; ?></div>
            <div class="stat-label">Atendidos</div>
        </div>
    </div>
    
    <div class="documents-section">
        <div class="section-header">
            <h2>Memorandos Recientes</h2>
            <a href="<?php echo $base_url; ?>/subir-documento" class="btn-secondary">📄 Nuevo Documento</a>
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
                                    <button class="btn-sm btn-edit" onclick="editDocument(<?php echo $doc['id']; ?>)">✏️</button>
                                    <?php if ($doc['documento_blob']): ?>
                                        <button class="btn-sm btn-view" onclick="viewPDF(<?php echo $doc['id']; ?>)">👁️</button>
                                        <button class="btn-sm btn-download" onclick="downloadPDF(<?php echo $doc['id']; ?>)">⬇️</button>
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
