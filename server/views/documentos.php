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


//variable para estilos
$base_url_front = '/project/server';
$base_url = '/project/public';


ob_start();
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="<?php echo $base_url_front; ?>/views/styles/documentos.css">

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
        
        <!-- Búsqueda general -->
        <div class="form-group form-group-busqueda">
            <label for="busqueda" class="label-title">
                <i class="fas fa-search"></i>
                <span class="label-text">Búsqueda General</span>
            </label>
            <input type="text" id="busqueda" name="busqueda" class="form-input" 
                placeholder="Folio, contenido, remitente..." value="<?php echo htmlspecialchars($busqueda); ?>">
        </div>


        
        <!-- Urgencia -->
        <div class="form-group form-group-urgencia">
            <label for="urgencia"><i class="fa-regular fa-star"></i> Urgencia</label>
            <select id="urgencia" name="urgencia" class="form-select">
                <option value="">Todas</option>
                <option value="ordinario" <?php echo $urgencia === 'ordinario' ? 'selected' : ''; ?>>Ordinario</option>
                <option value="urgente" <?php echo $urgencia === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
            </select>
        </div>
        
        <!-- Fecha Desde -->
        <div class="form-group form-group-fecha-desde">
            <label for="fecha_desde"><i class="fa-regular fa-calendar-alt"></i> Fecha Desde</label>
            <input type="date" id="fecha_desde" name="fecha_desde" class="form-input" 
                value="<?php echo htmlspecialchars($fecha_desde); ?>">
        </div>
        
        <!-- Fecha Hasta -->
        <div class="form-group form-group-fecha-hasta">
            <label for="fecha_hasta"><i class="fa-regular fa-calendar-check"></i> Fecha Hasta</label>
            <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-input" 
                value="<?php echo htmlspecialchars($fecha_hasta); ?>">
        </div>
        
        <!-- Usuario -->
        <div class="form-group form-group-usuario">
            <label for="usuario_filtro"><i class="fa-regular fa-user"></i> Usuario</label>
            <select id="usuario_filtro" name="usuario_filtro" class="form-select">
                <option value="">Todos los usuarios</option>
                <?php foreach ($usuarios as $user): ?>
                    <option value="<?php echo $user['id']; ?>" <?php echo $usuario_filtro == $user['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Área -->
        <div class="form-group form-group-area">
            <label for="area_filtro"><i class="fa-regular fa-building"></i> Área</label>
            <select id="area_filtro" name="area_filtro" class="form-select">
                <option value="">Todas las áreas</option>
                <?php foreach ($areas as $area): ?>
                    <option value="<?php echo $area['id']; ?>" <?php echo $area_filtro == $area['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($area['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <!-- Botones -->
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
            <i class="fa-regular fa-file-alt"></i> Documentos 
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
                            <div class="document-icon">
                                <i class="fas fa-file-alt"> </i> 
                            </div>
                            <div class="document-folio-name">
                                <?php echo htmlspecialchars($doc['folio']); ?>
                            </div>
                        </div>
                        <div>
                            <span class="urgencia-badge urgencia-<?php echo $doc['urgencia']; ?>">
                                <?php echo ucfirst($doc['urgencia']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="document-details">
                        <p><strong><i class="fa-regular fa-calendar-alt"></i> Fecha:</strong> <span style="margin-left: 0.5em;"><?php echo date('d/m/Y', strtotime($doc['fecha_documento'] ?? $doc['fecha_creacion'])); ?></span></p>
                        <p><strong><i class="fa-regular fa-user"></i> Remitente:</strong> <span style="margin-left: 0.5em;"><?php echo htmlspecialchars($doc['remitente_nombre']); ?></span></p>
                        <p><strong><i class="fa-regular fa-building"></i> Área:</strong> <span style="margin-left: 0.5em;"><?php echo htmlspecialchars($doc['area_nombre']); ?> → <?php echo htmlspecialchars($doc['area_destino_nombre'] ?? 'N/A'); ?></span></p>
                        <?php if ($doc['fecha_limite']): ?>
                            <p><strong><i class="fa-regular fa-clock"></i> Fecha Límite:</strong> <span style="margin-left: 0.5em;"><?php echo date('d/m/Y', strtotime($doc['fecha_limite'])); ?></span></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="document-content-div" style="margin-top: 1rem; flex-direction: column; align-items: flex-start; height: auto;">
    <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
        <strong><i class="fas fa-edit"></i> <span style="margin-left: 0.5em;">Contenido:</span></strong>
    </div>
    <div style="width: 100%;">
        <?php echo nl2br(htmlspecialchars(substr($doc['contenido'], 0, 200))); ?>
        <?php if (strlen($doc['contenido']) > 200): ?>...<?php endif; ?>
    </div>
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
            <h3 id="pdfTitle">
                <i class="fas fa-file-pdf" style="margin-right: 0.5em;"></i> Visualizar PDF
            </h3>
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
