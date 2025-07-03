<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../components/layout.php';

$usuario = AuthMiddleware::getUser();
$documentController = new DocumentController();
$documentos = $documentController->obtenerDocumentosPendientes($usuario['id']);
$base_url = '/project/public';

ob_start();
?>

<style>
.pendientes-header {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pendientes-list {
    display: grid;
    gap: 1.5rem;
}

.documento-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #4a7c59;
    transition: all 0.3s ease;
    animation: fadeIn 0.8s ease-out;
}

.documento-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.documento-card.pendiente {
    border-left-color: #dc3545;
}

.documento-card.proceso {
    border-left-color: #ffc107;
}

.documento-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.documento-folio {
    font-size: 1.2rem;
    font-weight: bold;
    color: white;
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-block;
}

.estatus-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.estatus-pendiente { 
    background: #f8d7da; 
    color: #721c24; 
}

.estatus-proceso { 
    background: #fff3cd; 
    color: #856404; 
}

.urgencia-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    margin-left: 0.5rem;
}

.urgencia-ordinario { background: #f8f9fa; color: #666; }
.urgencia-urgente { background: #f8d7da; color: #721c24; }

.documento-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.info-label {
    font-weight: 600;
    color: #333;
}

.info-value {
    color: #666;
}

.documento-contenido {
    margin: 1rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #4a7c59;
}

.estatus-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #e1e5e9;
}

.btn-estatus {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-proceso {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #212529;
}

.btn-atendido {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

.btn-estatus:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
    .pendientes-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .documento-header {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .documento-info {
        grid-template-columns: 1fr;
    }
    
    .estatus-actions {
        flex-direction: column;
    }
}
</style>

<div class="container">
    <div class="pendientes-header">
        <h2 style="color: #333;">
            <i class="fas fa-tasks"></i> Mis Documentos Pendientes 
            <small style="color: #666; font-weight: normal;">(<?php echo count($documentos); ?> documentos)</small>
        </h2>
    </div>
    
    <div class="pendientes-list">
        <?php if (empty($documentos)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>¡Excelente trabajo!</h3>
                <p>No tienes documentos pendientes por atender.<br>
                Todos tus documentos están al día.</p>
            </div>
        <?php else: ?>
            <?php foreach ($documentos as $doc): ?>
                <div class="documento-card <?php echo $doc['mi_estatus']; ?>">
                    <div class="documento-header">
                        <div>
                            <span class="documento-folio">
                                <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($doc['folio']); ?>
                            </span>
                            <span class="urgencia-badge urgencia-<?php echo $doc['urgencia']; ?>">
                                <?php echo ucfirst($doc['urgencia']); ?>
                            </span>
                        </div>
                        <span class="estatus-badge estatus-<?php echo $doc['mi_estatus']; ?>">
                            <i class="fas fa-<?php echo $doc['mi_estatus'] === 'pendiente' ? 'clock' : 'spinner'; ?>"></i>
                            <?php echo ucfirst($doc['mi_estatus']); ?>
                        </span>
                    </div>
                    
                    <div class="documento-info">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar"></i> Fecha:</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($doc['fecha_documento'] ?? $doc['fecha_creacion'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-user"></i> Remitente:</span>
                            <span class="info-value"><?php echo htmlspecialchars($doc['remitente_nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-sitemap"></i> Área:</span>
                            <span class="info-value"><?php echo htmlspecialchars($doc['area_nombre']); ?></span>
                        </div>
                        <?php if ($doc['fecha_limite']): ?>
                            <div class="info-item">
                                <span class="info-label"><i class="fas fa-exclamation-circle"></i> Fecha Límite:</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($doc['fecha_limite'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="documento-contenido">
                        <strong><i class="fas fa-edit"></i> Contenido:</strong>
                        <p><?php echo nl2br(htmlspecialchars($doc['contenido'])); ?></p>
                    </div>
                    
                    <div class="estatus-actions">
                        <?php if ($doc['mi_estatus'] === 'pendiente'): ?>
                            <button class="btn-estatus btn-proceso" onclick="actualizarEstatus(<?php echo $doc['id']; ?>, 'proceso')">
                                <i class="fas fa-play"></i> Marcar En Proceso
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn-estatus btn-atendido" onclick="actualizarEstatus(<?php echo $doc['id']; ?>, 'atendido')">
                            <i class="fas fa-check"></i> Marcar como Atendido
                        </button>
                        
                        <?php if ($doc['documento_blob']): ?>
                            <button class="btn-estatus" style="background: #6c757d; color: white;" onclick="verPDF(<?php echo $doc['id']; ?>)">
                                <i class="fas fa-eye"></i> Ver PDF
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function actualizarEstatus(memorandoId, nuevoEstatus) {
    if (confirm(`¿Estás seguro de marcar este documento como "${nuevoEstatus}"?`)) {
        fetch('<?php echo $base_url; ?>/actualizar-estatus-documento', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `memorando_id=${memorandoId}&estatus=${nuevoEstatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Estatus actualizado correctamente', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('Error al actualizar el estatus', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error al actualizar el estatus', 'error');
        });
    }
}

function verPDF(documentoId) {
    window.open('<?php echo $base_url; ?>/ver-pdf/' + documentoId, '_blank');
}

<?php if (isset($_GET['error'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['error'])); ?>', 'error');
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['success'])); ?>', 'success');
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
renderLayout('Documentos Pendientes', $content, $usuario, 'Documentos Pendientes', 'pendientes');
?>
