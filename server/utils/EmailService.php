<?php

class EmailService {
    
    public static function enviarNotificacionDocumento($destinatario_email, $destinatario_nombre, $folio, $remitente_nombre) {
        try {
            $asunto = "Nuevo documento recibido - SADO";
            $mensaje = self::generarPlantillaEmail($destinatario_nombre, $folio, $remitente_nombre);
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8',
                'From: SADO - Sistema de Administración de Documentos <noreply@uttecam.edu.mx>',
                'Reply-To: noreply@uttecam.edu.mx',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            return mail($destinatario_email, $asunto, $mensaje, implode("\r\n", $headers));
            
        } catch (Exception $e) {
            error_log("Error al enviar correo: " . $e->getMessage());
            return false;
        }
    }
    
    private static function generarPlantillaEmail($destinatario_nombre, $folio, $remitente_nombre) {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Nuevo Documento - SADO</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f8f9fa;
                }
                .container {
                    background: white;
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                }
                .header {
                    background: linear-gradient(135deg, #4a7c59 0%, #5a8c69 100%);
                    color: white;
                    padding: 2rem;
                    text-align: center;
                }
                .header h1 {
                    margin: 0;
                    font-size: 1.8rem;
                }
                .content {
                    padding: 2rem;
                }
                .document-info {
                    background: #f8f9fa;
                    padding: 1.5rem;
                    border-radius: 10px;
                    border-left: 4px solid #4a7c59;
                    margin: 1.5rem 0;
                }
                .btn-access {
                    display: inline-block;
                    background: linear-gradient(135deg, #ff8c42 0%, #ff7b2e 100%);
                    color: white;
                    text-decoration: none;
                    padding: 1rem 2rem;
                    border-radius: 8px;
                    font-weight: 600;
                    margin: 1rem 0;
                    text-align: center;
                }
                .footer {
                    background: #6c757d;
                    color: white;
                    padding: 1rem;
                    text-align: center;
                    font-size: 0.9rem;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏛️ UTTECAM</h1>
                    <p>Sistema de Administración de Documentos (SADO)</p>
                </div>
                
                <div class='content'>
                    <h2>¡Hola, " . htmlspecialchars($destinatario_nombre) . "!</h2>
                    
                    <p>Has recibido un nuevo documento en la plataforma SADO.</p>
                    
                    <div class='document-info'>
                        <h3>📄 Detalles del Documento</h3>
                        <p><strong>Folio:</strong> " . htmlspecialchars($folio) . "</p>
                        <p><strong>Remitente:</strong> " . htmlspecialchars($remitente_nombre) . "</p>
                        <p><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</p>
                    </div>
                    
                    <p>Para revisar el documento, ingresa a la plataforma SADO:</p>
                    
                    <a href='http://localhost/project/public/pendientes' class='btn-access'>
                        🔗 Acceder a SADO
                    </a>
                    
                    <p><small>Si no puedes hacer clic en el enlace, copia y pega esta URL en tu navegador:<br>
                    http://localhost/project/public/pendientes</small></p>
                    
                    <hr style='margin: 2rem 0; border: none; border-top: 1px solid #e1e5e9;'>
                    
                    <p><strong>Importante:</strong></p>
                    <ul>
                        <li>Este correo es generado automáticamente, no responder.</li>
                        <li>Revisa el documento lo antes posible.</li>
                        <li>Actualiza el estado del documento en la plataforma.</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    © 2025 UTTECAM - Universidad Tecnológica de Tecámac<br>
                    Sistema de Administración de Documentos (SADO)
                </div>
            </div>
        </body>
        </html>";
    }
}
?>
