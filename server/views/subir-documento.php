<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/DocumentController.php';
require_once __DIR__ . '/../components/layout.php';

$usuario = AuthMiddleware::getUser();
$documentController = new DocumentController();
$usuarios = $documentController->obtenerUsuarios();
$areas = $documentController->obtenerAreas();
$base_url = '/project/public';

ob_start();
?>

<style>
.form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.form-header h2 {
    color: #333;
    margin-bottom: 0.5rem;
}

.form-header p {
    color: #666;
}

.form-file {
    width: 100%;
    padding: 0.75rem;
    border: 2px dashed #4a7c59;
    border-radius: 8px;
    background: #f8f9fa;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-file:hover {
    background: #e9ecef;
}

.urgencia-options {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.radio-option:hover {
    border-color: #4a7c59;
}

.radio-option input[type="radio"]:checked + label {
    color: #4a7c59;
    font-weight: 600;
}

/* Multi-select styles */
.multi-select-container {
    position: relative;
}

.multi-select-trigger {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
}

.multi-select-trigger:focus,
.multi-select-trigger.active {
    border-color: #4a7c59;
    background: white;
    box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1);
}

.multi-select-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #4a7c59;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.multi-select-dropdown.active {
    display: block;
}

.multi-select-option {
    padding: 0.75rem;
    cursor: pointer;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.multi-select-option:hover {
    background: #f8f9fa;
}

.multi-select-option input[type="checkbox"] {
    margin: 0;
}

.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.user-tag {
    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-tag .remove {
    cursor: pointer;
    font-weight: bold;
}

/* Estilos para áreas personalizadas */
.area-container {
    position: relative;
}

.area-custom-input {
    display: none;
    margin-top: 0.5rem;
}

.area-custom-input.show {
    display: block;
    animation: slideDown 0.3s ease-out;
}

.checkbox-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #4a7c59;
    margin: 1rem 0;
}

.checkbox-container input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #4a7c59;
}

.checkbox-label {
    font-weight: 600;
    color: #333;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .urgencia-options {
        flex-direction: column;
    }
}
</style>

<div class="container">
    <div class="card">
        <div class="form-header">
            <h2><i class="fas fa-upload"></i> Subir Nuevo Documento</h2>
            <p>Complete todos los campos para enviar el documento</p>
        </div>
        
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
        
        <form action="<?php echo $base_url; ?>/subir-documento" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="folio"><i class="fas fa-file-alt"></i> Folio *</label>
                    <input type="text" id="folio" name="folio" class="form-input" required placeholder="Ej: MEM-2025-001">
                </div>
                
                <div class="form-group">
                    <label for="fecha_documento"><i class="fas fa-calendar"></i> Fecha del Documento *</label>
                    <input type="date" id="fecha_documento" name="fecha_documento" class="form-input" required value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="entidad_productora"><i class="fas fa-building"></i> Entidad Productora</label>
                <input type="text" id="entidad_productora" name="entidad_productora" class="form-input" placeholder="Ej: Dirección General">
            </div>
            
            <div class="form-group">
                <label for="destinatarios"><i class="fas fa-users"></i> Destinatarios *</label>
                <div class="multi-select-container">
                    <div class="multi-select-trigger" onclick="toggleMultiSelect()">
                        <span id="selectedText">Seleccionar destinatarios</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="multi-select-dropdown" id="multiSelectDropdown">
                        <?php foreach ($usuarios as $user): ?>
                            <div class="multi-select-option">
                                <input type="checkbox" name="destinatarios[]" value="<?php echo $user['id']; ?>" 
                                       id="user_<?php echo $user['id']; ?>" onchange="updateSelectedUsers()">
                                <label for="user_<?php echo $user['id']; ?>">
                                    <?php echo htmlspecialchars($user['nombre'] . ' (' . $user['correo'] . ')'); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="selected-users" id="selectedUsers"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="area_id"><i class="fas fa-sitemap"></i> Área de Origen *</label>
                    <div class="area-container">
                        <select id="area_id" name="area_id" class="form-select" required onchange="toggleAreaCustom('origen')">
                            <option value="">Seleccionar área</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?php echo $area['id']; ?>">
                                    <?php echo htmlspecialchars($area['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="otro">🖊️ Otro (Escribir área personalizada)</option>
                        </select>
                        <div class="area-custom-input" id="area_origen_custom">
                            <input type="text" name="area_origen_custom" class="form-input" 
                                   placeholder="Escriba el nombre del área de origen">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="area_destino_id"><i class="fas fa-arrow-right"></i> Área Destino *</label>
                    <div class="area-container">
                        <select id="area_destino_id" name="area_destino_id" class="form-select" required onchange="toggleAreaCustom('destino')">
                            <option value="">Seleccionar área destino</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?php echo $area['id']; ?>">
                                    <?php echo htmlspecialchars($area['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="otro">🖊️ Otro (Escribir área personalizada)</option>
                        </select>
                        <div class="area-custom-input" id="area_destino_custom">
                            <input type="text" name="area_destino_custom" class="form-input" 
                                   placeholder="Escriba el nombre del área destino">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="contenido"><i class="fas fa-edit"></i> Contenido del Documento *</label>
                <textarea id="contenido" name="contenido" class="form-textarea" required placeholder="Escriba el contenido del memorando..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="documento"><i class="fas fa-file-pdf"></i> Archivo PDF *</label>
                <input type="file" id="documento" name="documento" class="form-file" accept=".pdf" required>
                <small style="color: #666; font-size: 0.9rem;">Máximo 10MB, solo archivos PDF</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="fecha_requerida_respuesta"><i class="fas fa-clock"></i> Fecha Requerida de Respuesta</label>
                    <input type="date" id="fecha_requerida_respuesta" name="fecha_requerida_respuesta" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="fecha_limite"><i class="fas fa-exclamation-circle"></i> Fecha Límite</label>
                    <input type="date" id="fecha_limite" name="fecha_limite" class="form-input">
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-flag"></i> Urgencia *</label>
                <div class="urgencia-options">
                    <div class="radio-option">
                        <input type="radio" id="ordinario" name="urgencia" value="ordinario" checked>
                        <label for="ordinario"><i class="fas fa-file"></i> Ordinario</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="urgente" name="urgencia" value="urgente">
                        <label for="urgente"><i class="fas fa-star"></i> Urgente</label>
                    </div>
                </div>
            </div>
            
       
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Enviar Documento
            </button>
        </form>
    </div>
</div>

<script>
function toggleMultiSelect() {
    const dropdown = document.getElementById('multiSelectDropdown');
    const trigger = document.querySelector('.multi-select-trigger');
    
    dropdown.classList.toggle('active');
    trigger.classList.toggle('active');
}

function updateSelectedUsers() {
    const checkboxes = document.querySelectorAll('input[name="destinatarios[]"]:checked');
    const selectedUsersContainer = document.getElementById('selectedUsers');
    const selectedText = document.getElementById('selectedText');
    
    selectedUsersContainer.innerHTML = '';
    
    if (checkboxes.length === 0) {
        selectedText.textContent = 'Seleccionar destinatarios';
    } else {
        selectedText.textContent = `${checkboxes.length} destinatario(s) seleccionado(s)`;
        
        checkboxes.forEach(checkbox => {
            const label = document.querySelector(`label[for="${checkbox.id}"]`);
            const userName = label.textContent.trim();
            
            const userTag = document.createElement('div');
            userTag.className = 'user-tag';
            userTag.innerHTML = `
                <span>${userName}</span>
                <span class="remove" onclick="removeUser('${checkbox.id}')">×</span>
            `;
            selectedUsersContainer.appendChild(userTag);
        });
    }
}

function removeUser(checkboxId) {
    const checkbox = document.getElementById(checkboxId);
    checkbox.checked = false;
    updateSelectedUsers();
}

function toggleAreaCustom(tipo) {
    const select = document.getElementById(`area_${tipo === 'origen' ? 'id' : 'destino_id'}`);
    const customInput = document.getElementById(`area_${tipo}_custom`);
    
    if (select.value === 'otro') {
        customInput.classList.add('show');
        customInput.querySelector('input').required = true;
    } else {
        customInput.classList.remove('show');
        customInput.querySelector('input').required = false;
        customInput.querySelector('input').value = '';
    }
}

// Cerrar dropdown al hacer clic fuera
document.addEventListener('click', function(event) {
    const container = document.querySelector('.multi-select-container');
    if (!container.contains(event.target)) {
        document.getElementById('multiSelectDropdown').classList.remove('active');
        document.querySelector('.multi-select-trigger').classList.remove('active');
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
renderLayout('Subir Documento', $content, $usuario, 'Subir Documento', '');
?>
