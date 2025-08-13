<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
AuthMiddleware::requireAuth();

$documento_id = $_GET['id'] ?? 0;
$documentController = new DocumentController();
$documento = $documentController->obtenerDocumentoPorId($documento_id);

if (!$documento) {
    header("Location: /project/public/documentos?error=" . urlencode("Documento no encontrado"));
    exit();
}

$usuario = AuthMiddleware::getUser();
$usuarios = $documentController->obtenerUsuarios();
$areas = $documentController->obtenerAreas();
$destinatarios = $documentController->obtenerDestinatariosDocumento($documento_id);

// Verificar si el usuario puede editar el estatus
$puedeEditarEstatus = $documentController->puedeEditarEstatus($documento_id, $usuario['id']);
$esRemitente = $documento['remitente_id'] == $usuario['id'];
$esAdmin = $usuario['rol'] === 'admin';

$base_url = '/project/public';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Documento - UTTECAM</title>
    <link rel="stylesheet" href="./../../server/assets/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .folio-highlight {
            background: #32785A;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            font-size: 1.1rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        
        .permission-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .permission-notice.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .permission-notice.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .estatus-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #32785A;
            margin: 1.5rem 0;
        }
        
        .estatus-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .estatus-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
        }
        
        .estatus-option:hover {
            border-color: #32785A;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .estatus-option.selected {
            border-color: #32785A;
            background: linear-gradient(135deg, #f8fff9 0%, #ffffff 100%);
        }
        
        .estatus-option input[type="radio"] {
            margin: 0;
        }
        
        .estatus-icon {
            font-size: 1.2rem;
        }
        
        .estatus-pendiente .estatus-icon { color: #dc3545; }
        .estatus-proceso .estatus-icon { color: #ffc107; }
        .estatus-atendido .estatus-icon { color: #28a745; }
        
        .form-input:disabled,
        .form-select:disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .disabled-field {
            position: relative;
        }
        
        .disabled-field::after {
            content: "🔒";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .destinatarios-readonly {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            border: 2px solid #e1e5e9;
        }
        
        .destinatario-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary-green);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            margin: 0.25rem;
            font-size: 0.9rem;
        }
        
        .destinatario-avatar {
            width: 24px;
            height: 24px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }
.volver{
    background-color: var(--primary-green);
    color: white; 
    border-radius:50px; 
    padding:10px; 
    margin: 10px; 
    width: 200px; 
}

.back-btn{
    text-decoration: none; 
    color: white;
}

.back-btn:hover{
    color:#f2f7ff;
    transform: translateY(-2px);

}

.btn-submit{
    background-color: var(--primary-green)!important ;
}

</style>
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php $current_page = 'documentos'; include __DIR__ . '/../components/navigation.php'; ?>
    
    <div class="container"> 

        <div class="volver">
            <a href="<?php echo $base_url; ?>/documentos" class="back-btn">
                <i class="fas fa-arrow-left"></i> Volver a Documentos
            </a>
        </div>
        
        <div class="card">
            <div class="form-header">
                <div class="folio-highlight">
                    <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($documento['folio']); ?>
                </div>
                <h2 style="color: #333;">Editar Documento</h2>
                <p style="color: #666;">Modifica los campos permitidos del memorando</p>
            </div>
            
            <?php if (!$esRemitente && !$esAdmin && !$puedeEditarEstatus): ?>
                <div class="permission-notice error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>No tienes permisos para editar este documento. Solo el remitente, destinatarios o administradores pueden editarlo.</span>
                </div>
            <?php elseif ($puedeEditarEstatus && !$esRemitente && !$esAdmin): ?>
                <div class="permission-notice">
                    <i class="fas fa-info-circle"></i>
                    <span>Como destinatario, solo puedes actualizar el estado de atención del documento.</span>
                </div>
            <?php elseif ($esRemitente || $esAdmin): ?>
                <div class="permission-notice success">
                    <i class="fas fa-check-circle"></i>
                    <span>Tienes permisos completos para editar este documento.</span>
                </div>
            <?php endif; ?>
            
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
            
            <form action="<?php echo $base_url; ?>/actualizar-documento" method="POST">
                <input type="hidden" name="id" value="<?php echo $documento['id']; ?>">
                
                <?php if ($esRemitente || $esAdmin): ?>
                    <!-- Campos editables para remitente/admin -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="folio"><i class="fas fa-file-alt"></i> Folio *</label>
                            <input type="text" id="folio" name="folio" class="form-input" required 
                                   value="<?php echo htmlspecialchars($documento['folio']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_documento"><i class="fas fa-calendar"></i> Fecha del Documento</label>
                            <input type="date" id="fecha_documento" name="fecha_documento" class="form-input" 
                                   value="<?php echo $documento['fecha_documento']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="entidad_productora"><i class="fas fa-building"></i> Entidad Productora</label>
                        <input type="text" id="entidad_productora" name="entidad_productora" class="form-input" 
                               placeholder="Ej: Dirección General" value="<?php echo htmlspecialchars($documento['entidad_productora']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Destinatarios (No editable)</label>
                        <div class="destinatarios-readonly">
                            <?php foreach ($destinatarios as $dest): ?>
                                <span class="destinatario-tag">
                                    <div class="destinatario-avatar">
                                        <?php echo strtoupper(substr($dest['nombre'], 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($dest['nombre']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <small style="color: #666; font-size: 0.9rem;">
                            <i class="fas fa-info-circle"></i> Los destinatarios no pueden modificarse después de crear el documento
                        </small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="area_destino_id"><i class="fas fa-arrow-right"></i> Área Destino</label>
                            <select id="area_destino_id" name="area_destino_id" class="form-select">
                                <option value="">Seleccionar área destino</option>
                                <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>" 
                                            <?php echo $area['id'] == $documento['area_destino_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($area['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="urgencia"><i class="fas fa-flag"></i> Urgencia *</label>
                            <select id="urgencia" name="urgencia" class="form-select" required>
                                <option value="ordinario" <?php echo $documento['urgencia'] === 'ordinario' ? 'selected' : ''; ?>>Ordinario</option>
                                <option value="urgente" <?php echo $documento['urgencia'] === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contenido"><i class="fas fa-edit"></i> Contenido del Documento *</label>
                        <textarea id="contenido" name="contenido" class="form-textarea" required 
                                    placeholder="Escriba el contenido del memorando..."><?php echo htmlspecialchars($documento['contenido']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_requerida_respuesta"><i class="fas fa-clock"></i> Fecha Requerida de Respuesta</label>
                            <input type="date" id="fecha_requerida_respuesta" name="fecha_requerida_respuesta" class="form-input" 
                                    value="<?php echo $documento['fecha_requerida_respuesta']; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_limite"><i class="fas fa-exclamation-circle"></i> Fecha Límite</label>
                            <input type="date" id="fecha_limite" name="fecha_limite" class="form-input" 
                                    value="<?php echo $documento['fecha_limite']; ?>">
                        </div>
                    </div>
                    
                    <button type="submit" style="border-radius:50px; " class="btn-submit">
                        <i class="fas fa-save"></i> Actualizar Documento
                    </button>
                    
                <?php else: ?>
                    <!-- Vista de solo lectura para campos no editables -->
                    <div class="form-row">
                        <div class="form-group disabled-field">
                            <label><i class="fas fa-file-alt"></i> Folio</label>
                            <input type="text" class="form-input" disabled value="<?php echo htmlspecialchars($documento['folio']); ?>">
                        </div>
                        
                        <div class="form-group disabled-field">
                            <label><i class="fas fa-calendar"></i> Fecha del Documento</label>
                            <input type="text" class="form-input" disabled value="<?php echo $documento['fecha_documento'] ? date('d/m/Y', strtotime($documento['fecha_documento'])) : 'N/A'; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group disabled-field">
                        <label><i class="fas fa-building"></i> Entidad Productora</label>
                        <input type="text" class="form-input" disabled value="<?php echo htmlspecialchars($documento['entidad_productora']); ?>">
                    </div>
                    
                    <div class="form-group disabled-field">
                        <label><i class="fas fa-edit"></i> Contenido del Documento</label>
                        <textarea class="form-textarea" disabled><?php echo htmlspecialchars($documento['contenido']); ?></textarea>
                    </div>
                <?php endif; ?>
                
                <?php if ($puedeEditarEstatus): ?>
                    <!-- Sección de estatus solo para destinatarios -->
                    <div class="estatus-section">
                        <h3><i class="fas fa-tasks"></i> Actualizar Mi Estado de Atención</h3>
                        <p style="color: #666; margin-bottom: 1rem;">Selecciona el estado actual de tu atención a este documento:</p>
                        
                        <?php 
                        $miEstatus = 'pendiente';
                        foreach ($destinatarios as $dest) {
                            if ($dest['destinatario_id'] == $usuario['id']) {
                                $miEstatus = $dest['estatus'];
                                break;
                            }
                        }
                        ?>
                        
                        <div class="estatus-options">
                            <label class="estatus-option estatus-pendiente <?php echo $miEstatus === 'pendiente' ? 'selected' : ''; ?>">
                                <input type="radio" name="mi_estatus" value="pendiente" <?php echo $miEstatus === 'pendiente' ? 'checked' : ''; ?>>
                                <i class="fas fa-clock estatus-icon"></i>
                                <div>
                                    <strong>Pendiente</strong><br>
                                    <small>Aún no he comenzado</small>
                                </div>
                            </label>
                            
                            <label class="estatus-option estatus-proceso <?php echo $miEstatus === 'proceso' ? 'selected' : ''; ?>">
                                <input type="radio" name="mi_estatus" value="proceso" <?php echo $miEstatus === 'proceso' ? 'checked' : ''; ?>>
                                <i class="fas fa-spinner estatus-icon"></i>
                                <div>
                                    <strong>En Proceso</strong><br>
                                    <small>Trabajando en ello</small>
                                </div>
                            </label>
                            
                            <label class="estatus-option estatus-atendido <?php echo $miEstatus === 'atendido' ? 'selected' : ''; ?>">
                                <input type="radio" name="mi_estatus" value="atendido" <?php echo $miEstatus === 'atendido' ? 'checked' : ''; ?>>
                                <i class="fas fa-check estatus-icon"></i>
                                <div>
                                    <strong>Atendido</strong><br>
                                    <small>Completado</small>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-group" style="margin-top: 1rem;">
                            <label for="comentarios"><i class="fas fa-comment"></i> Comentarios (Opcional)</label>
                            <textarea id="comentarios" name="comentarios" class="form-textarea" 
                                      placeholder="Agrega comentarios sobre el estado del documento..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> Actualizar Mi Estado
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="footer">
        © 2025 UTTECAM. Todos los derechos reservados.
    </div>
    
    <script>
        // Manejar selección de estatus
        document.querySelectorAll('.estatus-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.estatus-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        <?php if (isset($_GET['error'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['error'])); ?>', 'error');
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            showToast('<?php echo htmlspecialchars(urldecode($_GET['success'])); ?>', 'success');
        <?php endif; ?>
    </script>
</body>
</html>
