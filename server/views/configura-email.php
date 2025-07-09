<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../components/layout.php';

AuthMiddleware::requireRole('admin');
$usuario = AuthMiddleware::getUser();
$base_url = '/project/public';

ob_start();
?>

<style>
.config-card {
    background: white;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border-top: 4px solid #4a7c59;
    margin-bottom: 2rem;
}

.config-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f8f9fa;
}

.gmail-setup {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    border-left: 4px solid #17a2b8;
    margin: 1.5rem 0;
}

.step {
    margin: 1rem 0;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border-left: 3px solid #28a745;
}

.step-number {
    background: #28a745;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-right: 1rem;
}

.test-section {
    background: #fff3cd;
    padding: 1.5rem;
    border-radius: 10px;
    border-left: 4px solid #ffc107;
    margin: 1.5rem 0;
}

.btn-test {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: #212529;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-test:hover {
    background: linear-gradient(135deg, #e0a800 0%, #d39e00 100%);
    transform: translateY(-2px);
}

.code-block {
    background: #2d3748;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.warning {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}
</style>

<div class="container">
    <div class="config-card">
        <div class="config-header">
            <h2><i class="fas fa-envelope-open-text"></i> Configuración de Correo Gmail</h2>
            <p>Configura el sistema para enviar correos automáticos a través de Gmail</p>
        </div>
        
        <div class="gmail-setup">
            <h3><i class="fab fa-google"></i> Configuración de Gmail SMTP</h3>
            <p>Para que SADO pueda enviar correos automáticamente, necesitas configurar Gmail:</p>
            
            <div class="step">
                <span class="step-number">1</span>
                <div>
                    <strong>Activar verificación en 2 pasos</strong>
                    <p>Ve a tu cuenta de Google → Seguridad → Verificación en 2 pasos y actívala.</p>
                </div>
            </div>
            
            <div class="step">
                <span class="step-number">2</span>
                <div>
                    <strong>Generar contraseña de aplicación</strong>
                    <p>En Seguridad → Contraseñas de aplicaciones → Selecciona "Correo" → Genera una nueva contraseña.</p>
                </div>
            </div>
            
            <div class="step">
                <span class="step-number">3</span>
                <div>
                    <strong>Configurar credenciales</strong>
                    <p>Edita el archivo <code>server/utils/EmailService.php</code> con tus credenciales:</p>
                    <div class="code-block">
private static $smtp_username = 'tu-email@gmail.com';<br>
private static $smtp_password = 'tu-contraseña-de-aplicacion';<br>
private static $from_email = 'sado@uttecam.edu.mx';
                    </div>
                </div>
            </div>
            
            <div class="step">
                <span class="step-number">4</span>
                <div>
                    <strong>Probar configuración</strong>
                    <p>Usa el formulario de abajo para probar el envío de correos.</p>
                </div>
            </div>
        </div>
        
        <div class="warning">
            <strong><i class="fas fa-exclamation-triangle"></i> Importante:</strong>
            <ul>
                <li>Nunca uses tu contraseña normal de Gmail, solo la contraseña de aplicación.</li>
                <li>Mantén las credenciales seguras y no las compartas.</li>
                <li>El correo debe tener verificación en 2 pasos activada.</li>
            </ul>
        </div>
        
        <div class="test-section">
            <h3><i class="fas fa-paper-plane"></i> Probar Envío de Correo</h3>
            <p>Envía un correo de prueba para verificar que la configuración funciona:</p>
            
            <form action="<?php echo $base_url; ?>/test-email" method="GET" style="display: flex; gap: 1rem; align-items: end;">
                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                    <label for="email">Correo de destino:</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           placeholder="destino@ejemplo.com" value="<?php echo htmlspecialchars($usuario['correo']); ?>">
                </div>
                <button type="submit" class="btn-test">
                    <i class="fas fa-paper-plane"></i> Enviar Prueba
                </button>
            </form>
        </div>
        
        <div class="success">
            <strong><i class="fas fa-info-circle"></i> Estado Actual:</strong>
            <p>El sistema está configurado para registrar intentos de envío en los logs del servidor. 
            Una vez configurado Gmail correctamente, los correos se enviarán automáticamente cuando 
            se marque la casilla en "Subir Documento".</p>
        </div>
        
        <div style="margin-top: 2rem;">
            <h3><i class="fas fa-cog"></i> Configuración Avanzada</h3>
            <p>Para una configuración más robusta, considera instalar PHPMailer:</p>
            <div class="code-block">
composer require phpmailer/phpmailer
            </div>
            <p>Esto permitirá un mejor manejo de errores y más opciones de configuración SMTP.</p>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
renderLayout('Configuración de Email', $content, $usuario, 'Configuración de Email', '');
?>
