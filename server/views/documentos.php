<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../components/layout.php';

$usuario = AuthMiddleware::getUser();
$documentController = new DocumentController();

// Obtener filtros
$busqueda = $_GET['busqueda'] ?? '';
$estado = $_GET['estado'] ?? '';
$urgencia = $_GET['urgencia'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$usuario_filtro = $_GET['usuario_filtro'] ?? '';
$area_filtro = $_GET['area_filtro'] ?? '';

// Obtener datos para filtros
$usuarios = $documentController->obtenerUsuarios();
$areas = $documentController->obtenerAreas();

// Obtener documentos
if ($usuario['rol'] === 'admin') {
    $documentos = $documentController->obtenerDocumentos();
} else {
    $documentos = $documentController->obtenerDocumentos($usuario['id']);
}

// Aplicar filtros
$documentos = array_filter($documentos, function($doc) use ($busqueda, $estado, $urgencia, $fecha_desde, $fecha_hasta, $usuario_filtro, $area_filtro) {
    // Filtro de búsqueda general
    $matchBusqueda = empty($busqueda) || 
        stripos($doc['folio'], $busqueda) !== false ||
        stripos($doc['contenido'], $busqueda) !== false ||
        stripos($doc['remitente_nombre'], $busqueda) !== false ||
        stripos($doc['entidad_productora'], $busqueda) !== false;
    
    // Filtro de urgencia
    $matchUrgencia = empty($urgencia) || $doc['urgencia'] === $urgencia;
    
    // Filtro de fecha
    $matchFecha = true;
    if (!empty($fecha_desde) || !empty($fecha_hasta)) {
        $fechaDoc = $doc['fecha_documento'] ?? $doc['fecha_creacion'];
        if (!empty($fecha_desde)) {
            $matchFecha = $matchFecha && ($fechaDoc >= $fecha_desde);
        }
        if (!empty($fecha_hasta)) {
            $matchFecha = $matchFecha && ($fechaDoc <= $fecha_hasta);
        }
    }
    
    // Filtro de usuario
    $matchUsuario = empty($usuario_filtro) || $doc['remitente_id'] == $usuario_filtro;
    
    // Filtro de área
    $matchArea = empty($area_filtro) || 
        $doc['area_id'] == $area_filtro || 
        $doc['area_destino_id'] == $area_filtro;
    
    return $matchBusqueda && $matchUrgencia && $matchFecha && $matchUsuario && $matchArea;
});

$base_url = '/project/public';

ob_start();
?>

<style>
.search-bar {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.search-form .form-group {
    margin-bottom: 0;
}

.search-actions {
    display: flex;
    gap: 0.5rem;
    grid-column: 1 / -1;
    justify-content: flex-end;
    margin-top: 1rem;
}

.btn-search {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-search:hover {
    background: linear-gradient(135deg, #5a8c69 0%, #6a9c79 100%);
    transform: translateY(-2px);
}

.btn-clear {
    background: #6c757d;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.actions-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.documents-grid {
    display: grid;
    gap: 1.5rem;
}

.document-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    border-left: 4px solid #4a7c59;
    transition: all 0.3s ease;
    animation: fadeIn 0.8s ease-out;
}

.document-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.document-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.document-folio {
    font-size: 1.2rem;
    font-weight: bold;
    color: white;
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    display: inline-block;
}

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
    margin-top: 1rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
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

.btn-details {
    background: #ffc107;
    color: #212529;
}

.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.destinatario-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    margin: 0.5rem 0;
    background: white;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.destinatario-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.destinatario-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.destinatario-estatus {
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.estatus-pendiente { background: #f8d7da; color: #721c24; }
.estatus-proceso { background: #fff3cd; color: #856404; }
.estatus-atendido { background: #d4edda; color: #155724; }

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #666;
}

.filter-summary {
    background: #e9ecef;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #495057;
}

/* Modal para PDF */
.pdf-modal {
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

.pdf-modal-content {
    position: relative;
    margin: 2% auto;
    width: 90%;
    height: 90%;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.pdf-modal-header {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pdf-modal-body {
    height: calc(100% - 70px);
    padding: 0;
}

.pdf-modal-body iframe {
    width: 100%;
    height: 100%;
    border: none;
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

.btn-download-modal {
    background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-download-modal:hover {
    background: linear-gradient(135deg, #ff7b2e 0%, #ff6a1a 100%);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Responsive */
@media (max-width: 768px) {
    .search-form {
        grid-template-columns: 1fr;
    }
    
    .actions-bar {
        flex-direction: column;
        gap: 1rem;
    }
    
    .document-header {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .pdf-modal-content {
        width: 95%;
        height: 95%;
        margin: 2.5% auto;
    }
    
    .destinatario-item {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
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
    
    <div class="search-bar">
        <form method="GET" class="search-form">
            <div class="form-group">
                <label for="busqueda"><i class="fas fa-search"></i> Búsqueda General</label>
                <input type="text" id="busqueda" name="busqueda" class="form-input" 
                       placeholder="Folio, contenido, remitente..." value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            
            <div class="form-group">
                <label for="urgencia"><i class="fas fa-star"></i> Urgencia</label>
                <select id="urgencia" name="urgencia" class="form-select">
                    <option value="">Todas</option>
                    <option value="ordinario" <?php echo $urgencia === 'ordinario' ? 'selected' : ''; ?>>Ordinario</option>
                    <option value="urgente" <?php echo $urgencia === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="fecha_desde"><i class="fas fa-calendar-alt"></i> Fecha Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-input" 
                       value="<?php echo htmlspecialchars($fecha_desde); ?>">
            </div>
            
            <div class="form-group">
                <label for="fecha_hasta"><i class="fas fa-calendar-check"></i> Fecha Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-input" 
                       value="<?php echo htmlspecialchars($fecha_hasta); ?>">
            </div>
            
            <div class="form-group">
                <label for="usuario_filtro"><i class="fas fa-user"></i> Usuario</label>
                <select id="usuario_filtro" name="usuario_filtro" class="form-select">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($usuarios as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $usuario_filtro == $user['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="area_filtro"><i class="fas fa-sitemap"></i> Área</label>
                <select id="area_filtro" name="area_filtro" class="form-select">
                    <option value="">Todas las áreas</option>
                    <?php foreach ($areas as $area): ?>
                        <option value="<?php echo $area['id']; ?>" <?php echo $area_filtro == $area['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($area['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="search-actions">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="<?php echo $base_url; ?>/documentos" class="btn-clear">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
    
    <?php if (!empty($busqueda) || !empty($urgencia) || !empty($fecha_desde) || !empty($fecha_hasta) || !empty($usuario_filtro) || !empty($area_filtro)): ?>
        <div class="filter-summary">
            <i class="fas fa-filter"></i> <strong>Filtros activos:</strong>
            <?php if (!empty($busqueda)): ?>
                Búsqueda: "<?php echo htmlspecialchars($busqueda); ?>" |
            <?php endif; ?>
            <?php if (!empty($urgencia)): ?>
                Urgencia: <?php echo ucfirst($urgencia); ?> |
            <?php endif; ?>
            <?php if (!empty($fecha_desde)): ?>
                Desde: <?php echo date('d/m/Y', strtotime($fecha_desde)); ?> |
            <?php endif; ?>
            <?php if (!empty($fecha_hasta)): ?>
                Hasta: <?php echo date('d/m/Y', strtotime($fecha_hasta)); ?> |
            <?php endif; ?>
            <?php if (!empty($usuario_filtro)): ?>
                Usuario: <?php 
                    $userFound = array_filter($usuarios, function($u) use ($usuario_filtro) { return $u['id'] == $usuario_filtro; });
                    echo !empty($userFound) ? htmlspecialchars(array_values($userFound)[0]['nombre']) : 'N/A';
                ?> |
            <?php endif; ?>
            <?php if (!empty($area_filtro)): ?>
                Área: <?php 
                    $areaFound = array_filter($areas, function($a) use ($area_filtro) { return $a['id'] == $area_filtro; });
                    echo !empty($areaFound) ? htmlspecialchars(array_values($areaFound)[0]['nombre']) : 'N/A';
                ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="actions-bar">
        <h2 style="color: #333;">
            <i class="fas fa-file-alt"></i> Documentos 
            <small style="color: #666; font-weight: normal;">(<?php echo count($documentos); ?> encontrados)</small>
        </h2>
        <a href="<?php echo $base_url; ?>/subir-documento" class="btn-secondary">
            <i class="fas fa-upload"></i> Subir Documento
        </a>
    </div>
    
    <div class="documents-grid">
        <?php if (empty($documentos)): ?>
            <div class="empty-state">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3>No se encontraron documentos</h3>
                <p>No hay documentos que coincidan con los criterios de búsqueda</p>
            </div>
        <?php else: ?>
            <?php foreach ($documentos as $doc): ?>
                <div class="document-card">
                    <div class="document-header">
                        <div class="document-folio">
                            <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($doc['folio']); ?>
                        </div>
                        <div>
                            <span class="urgencia-badge urgencia-<?php echo $doc['urgencia']; ?>">
                                <?php echo ucfirst($doc['urgencia']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <p><strong><i class="fas fa-calendar"></i> Fecha:</strong> <?php echo date('d/m/Y', strtotime($doc['fecha_documento'] ?? $doc['fecha_creacion'])); ?></p>
                        <p><strong><i class="fas fa-user"></i> Remitente:</strong> <?php echo htmlspecialchars($doc['remitente_nombre']); ?></p>
                        <p><strong><i class="fas fa-sitemap"></i> Área:</strong> <?php echo htmlspecialchars($doc['area_nombre']); ?> → <?php echo htmlspecialchars($doc['area_destino_nombre'] ?? 'N/A'); ?></p>
                        <?php if ($doc['fecha_limite']): ?>
                            <p><strong><i class="fas fa-exclamation-circle"></i> Fecha Límite:</strong> <?php echo date('d/m/Y', strtotime($doc['fecha_limite'])); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <p><strong><i class="fas fa-edit"></i> Contenido:</strong></p>
                        <p><?php echo nl2br(htmlspecialchars(substr($doc['contenido'], 0, 200))); ?>
                        <?php if (strlen($doc['contenido']) > 200): ?>...</p><?php endif; ?>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="<?php echo $base_url; ?>/editar-documento/<?php echo $doc['id']; ?>" class="btn-sm btn-edit">
                            <i class="fas fa-edit"></i> Editar
                        </a>

                        <?php if ($doc['documento_blob']): ?>
                            <button class="btn-sm btn-view" onclick="abrirModalPDF(<?php echo $doc['id']; ?>, '<?php echo htmlspecialchars($doc['folio']); ?>')">
                                <i class="fas fa-eye"></i> Ver PDF
                            </button>
                        <?php endif; ?>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para PDF -->
<div id="pdfModal" class="pdf-modal">
    <div class="pdf-modal-content">
        <div class="pdf-modal-header">
            <h3 id="pdfTitle"><i class="fas fa-file-pdf"></i> Visualizar PDF</h3>
            <div>
                <button class="close-modal" onclick="cerrarModalPDF()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="pdf-modal-body">
            <iframe id="pdfViewer" src=""></iframe>
        </div>
    </div>
</div>

<script>

function abrirModalPDF(documentoId, folio) {
    const modal = document.getElementById('pdfModal');
    const viewer = document.getElementById('pdfViewer');
    const title = document.getElementById('pdfTitle');
    const downloadBtn = document.getElementById('downloadPdfBtn');
    
    title.innerHTML = `<i class="fas fa-file-pdf"></i> ${folio}`;
    viewer.src = `<?php echo $base_url; ?>/ver-pdf/${documentoId}`;
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarModalPDF() {
    const modal = document.getElementById('pdfModal');
    const viewer = document.getElementById('pdfViewer');
    
    modal.style.display = 'none';
    viewer.src = '';
    document.body.style.overflow = 'auto';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('pdfModal');
    if (event.target === modal) {
        cerrarModalPDF();
    }
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModalPDF();
    }
});

<?php if (isset($_GET['error'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['error'])); ?>', 'error');
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    showToast('<?php echo htmlspecialchars(urldecode($_GET['success'])); ?>', 'success');
<?php endif; ?>
</script>

<?php
$content = ob_get_clean();
renderLayout('Documentos', $content, $usuario, 'Gestión de Documentos', 'documentos');
?>
