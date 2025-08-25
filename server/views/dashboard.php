<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../components/layout.php';

/**
 * Usuario autenticado actualmente en la sesión.
 *
 * Se obtiene mediante el middleware de autenticación. Esta variable
 * contiene los datos esenciales del usuario logueado, incluyendo:
 *  - id (int) Identificador único en BD
 *  - nombre (string) Nombre completo
 *  - rol (string) Rol de usuario (ej: admin, empleado, etc.)
 *
 * @var array $usuario Datos del usuario activo.
 */
$usuario = AuthMiddleware::getUser();

/**
 * Controlador de documentos.
 * 
 * Permite realizar operaciones relacionadas con documentos
 * como obtener documentos recientes, estadísticas, crear o editar.
 *
 * @var DocumentController $documentController
 */
$documentController = new DocumentController();

/**
 * Controlador de notificaciones.
 *
 * Responsable de la gestión de notificaciones asociadas
 * a documentos y usuarios.
 *
 * @var NotificationController $notificationController
 */
$notificationController = new NotificationController();

/**
 * Obtención de documentos recientes.
 *
 * 🔒 Si el usuario tiene rol `admin`, obtiene todos los documentos recientes.
 * 🔒 Si es otro rol, solo obtiene los documentos creados/asignados a dicho usuario.
 *
 * @var array $documentos Lista de documentos recientes (folio, remitente, estado, etc.)
 */
if ($usuario['rol'] === 'admin') {
    $documentos = $documentController->obtenerDocumentosRecientes();
} else {
    $documentos = $documentController->obtenerDocumentosRecientes($usuario['id']);
}

/**
 * Estadísticas generales de documentos.
 *
 * Genera un resumen numérico de documentos según el rol del usuario.
 * Los valores más comunes son:
 *  - total (int)
 *  - pendientes (int)
 *  - proceso (int)
 *  - atendidos (int)
 *
 * @var array $stats Resumen de estadísticas de documentos.
 */
$stats = $documentController->obtenerEstadisticas($usuario['id'], $usuario['rol']);

/**
 * URL base para acceso a imágenes públicas.
 *
 * @var string $base_url_front
 */
$base_url_front = '/project/public/images/';

/**
 * URL base del servidor (para rutas internas).
 *
 * @var string $base_url
 */
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

/**
 * Redirige al usuario a la vista de edición del documento.
 *
 * @param {number} id Identificador único del documento.
 */
function editDocument(id) {
    window.location.href = '<?php echo $base_url; ?>/editar-documento/' + id;
}

/**
 * Abre en una nueva pestaña la vista previa del PDF del documento.
 *
 * @param {number} id Identificador único del documento.
 */
function viewPDF(id) {
    window.open('<?php echo $base_url; ?>/ver-pdf/' + id, '_blank');
}

 /**
 * Descarga el archivo PDF asociado a un documento.
 *
 * @param {number} id Identificador único del documento.
 */
function downloadPDF(id) {
    window.location.href = '<?php echo $base_url; ?>/descargar-pdf/' + id;
}
</script>

<?php
/**
 * Captura el contenido generado en el buffer de salida y lo almacena en una variable.
 *
 * La función `ob_get_clean()` obtiene todo lo que se haya enviado al buffer de salida 
 * (por ejemplo, HTML generado previamente) y lo guarda en la variable `$content`. 
 * Además, limpia (vacía) el buffer para evitar que se muestre duplicado.
 *
 * @var string $content Contiene el contenido HTML o texto procesado en el buffer de salida.
 */
$content = ob_get_clean();
/**
 * Renderiza el layout principal de la aplicación.
 *
 * La función `renderLayout()` se encarga de ensamblar el contenido capturado (`$content`) 
 * dentro de una plantilla base. Se le pasan parámetros adicionales para configurar 
 * el título de la página, el usuario autenticado, el subtítulo o encabezado, 
 * y el identificador de la sección que se está mostrando.
 *
 * @param string $title        Título principal de la vista, en este caso "Dashboard".
 * @param string $content      Contenido HTML capturado desde el buffer de salida.
 * @param mixed  $usuario      Datos del usuario autenticado (puede ser un objeto o array).
 * @param string $subtitle     Subtítulo o descripción corta de la sección, en este caso "Panel de Control".
 * @param string $section      Identificador interno de la sección actual, aquí "dashboard".
 *
 * @return void No retorna ningún valor, se encarga de renderizar la salida directamente al navegador.
 */
renderLayout('Dashboard', $content, $usuario, 'Panel de Control', 'dashboard');
?>
