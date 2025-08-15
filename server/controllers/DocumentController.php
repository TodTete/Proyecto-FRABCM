<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils/EmailService.php';

class DocumentController {
    
    public function obtenerDocumentos($usuario_id = null) {
        global $pdo;
        
        try {
            if ($usuario_id) {
                $stmt = $pdo->prepare("
                    SELECT DISTINCT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                    ORDER BY m.fecha_creacion DESC
                ");
                $stmt->execute([$usuario_id, $usuario_id]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    ORDER BY m.fecha_creacion DESC
                ");
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDocumentosPendientes($usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre,
                       dd.estatus as mi_estatus, dd.fecha_actualizacion
                FROM memorandos m 
                LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                LEFT JOIN areas a1 ON m.area_id = a1.id 
                LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                INNER JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                WHERE dd.destinatario_id = ? AND dd.estatus IN ('pendiente', 'proceso')
                ORDER BY m.fecha_creacion DESC
            ");
            $stmt->execute([$usuario_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos pendientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerDestinatariosDocumento($memorando_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT dd.*, u.nombre, u.correo
                FROM documento_destinatarios dd
                INNER JOIN usuarios u ON dd.destinatario_id = u.id
                WHERE dd.memorando_id = ?
                ORDER BY u.nombre
            ");
            $stmt->execute([$memorando_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener destinatarios: " . $e->getMessage());
            return [];
        }
    }
    
    public function actualizarEstatusDestinatario($memorando_id, $destinatario_id, $estatus, $comentarios = '') {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE documento_destinatarios 
                SET estatus = ?, comentarios = ?, fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE memorando_id = ? AND destinatario_id = ?
            ");
            
            return $stmt->execute([$estatus, $comentarios, $memorando_id, $destinatario_id]);
        } catch (Exception $e) {
            error_log("Error al actualizar estatus: " . $e->getMessage());
            return false;
        }
    }
    
    public function puedeEditarEstatus($memorando_id, $usuario_id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM documento_destinatarios 
                WHERE memorando_id = ? AND destinatario_id = ?
            ");
            $stmt->execute([$memorando_id, $usuario_id]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error al verificar permisos: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerDocumentosRecientes($usuario_id = null, $limit = 10) {
        global $pdo;
        
        try {
            if ($usuario_id) {
                $stmt = $pdo->prepare("
                    SELECT DISTINCT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                    ORDER BY m.fecha_creacion DESC
                    LIMIT ?
                ");
                $stmt->execute([$usuario_id, $usuario_id, $limit]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                    FROM memorandos m 
                    LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                    LEFT JOIN areas a1 ON m.area_id = a1.id 
                    LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                    ORDER BY m.fecha_creacion DESC
                    LIMIT ?
                ");
                $stmt->execute([$limit]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documentos recientes: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerEstadisticas($usuario_id, $rol) {
        global $pdo;
        
        try {
            if ($rol === 'admin') {
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT m.id) as total,
                        SUM(CASE WHEN dd.estatus = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN dd.estatus = 'proceso' THEN 1 ELSE 0 END) as proceso,
                        SUM(CASE WHEN dd.estatus = 'atendido' THEN 1 ELSE 0 END) as atendidos
                    FROM memorandos m
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                ");
                $stmt->execute();
            } else {
                $stmt = $pdo->prepare("
                    SELECT 
                        COUNT(DISTINCT m.id) as total,
                        SUM(CASE WHEN dd.estatus = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                        SUM(CASE WHEN dd.estatus = 'proceso' THEN 1 ELSE 0 END) as proceso,
                        SUM(CASE WHEN dd.estatus = 'atendido' THEN 1 ELSE 0 END) as atendidos
                    FROM memorandos m 
                    LEFT JOIN documento_destinatarios dd ON m.id = dd.memorando_id
                    WHERE m.remitente_id = ? OR dd.destinatario_id = ?
                ");
                $stmt->execute([$usuario_id, $usuario_id]);
            }
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['total' => 0, 'pendientes' => 0, 'proceso' => 0, 'atendidos' => 0];
        }
    }
    
    public function obtenerDocumentoPorId($id) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT m.*, u1.nombre as remitente_nombre, a1.nombre as area_nombre, a2.nombre as area_destino_nombre
                FROM memorandos m 
                LEFT JOIN usuarios u1 ON m.remitente_id = u1.id 
                LEFT JOIN areas a1 ON m.area_id = a1.id 
                LEFT JOIN areas a2 ON m.area_destino_id = a2.id
                WHERE m.id = ?
            ");
            $stmt->execute([$id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener documento: " . $e->getMessage());
            return null;
        }
    }
    
    public function actualizarDocumento($id, $datos) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                UPDATE memorandos SET 
                    folio = ?, entidad_productora = ?, fecha_documento = ?, contenido = ?,
                    urgencia = ?, area_destino_id = ?, 
                    fecha_requerida_respuesta = ?, fecha_limite = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $datos['folio'],
                $datos['entidad_productora'],
                $datos['fecha_documento'],
                $datos['contenido'],
                $datos['urgencia'],
                $datos['area_destino_id'],
                $datos['fecha_requerida_respuesta'],
                $datos['fecha_limite'],
                $id
            ]);
        } catch (Exception $e) {
            error_log("Error al actualizar documento: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerUsuarios() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, correo FROM usuarios ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
    
    public function obtenerAreas() {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("SELECT id, nombre FROM areas ORDER BY nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener áreas: " . $e->getMessage());
            return [];
        }
    }
    
    public function crearAreaPersonalizada($nombre) {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO areas (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear área: " . $e->getMessage());
            return false;
        }
    }
    
    public function subirDocumento() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            // Recoger datos del formulario
            $form_data = [
                'folio' => $_POST['folio'] ?? '',
                'fecha_documento' => $_POST['fecha_documento'] ?? date('Y-m-d'),
                'destinatarios' => $_POST['destinatarios'] ?? [],
                'area_id' => $_POST['area_id'] ?? '',
                'area_destino_id' => $_POST['area_destino_id'] ?? '',
                'contenido' => $_POST['contenido'] ?? '',
                'urgencia' => $_POST['urgencia'] ?? 'ordinario',
                'entidad_productora' => $_POST['entidad_productora'] ?? '',
                'fecha_limite' => $_POST['fecha_limite'] ?? null,
                'fecha_requerida_respuesta' => $_POST['fecha_requerida_respuesta'] ?? null,
                'enviar_correo' => isset($_POST['enviar_correo']),
                'area_origen_custom' => $_POST['area_origen_custom'] ?? '',
                'area_destino_custom' => $_POST['area_destino_custom'] ?? ''
            ];
            
            if (isset($_FILES['documento'])) {
                $form_data['documento_name'] = $_FILES['documento']['name'];
            }

            // Validaciones
            $errores = [];
            if (empty($form_data['folio'])) $errores[] = "Folio requerido";
            if (empty($form_data['fecha_documento'])) $errores[] = "Fecha del documento requerida";
            if (empty($form_data['destinatarios'])) $errores[] = "Seleccione al menos un destinatario";
            if (empty($form_data['area_id'])) $errores[] = "Área de origen requerida";
            if (empty($form_data['area_destino_id'])) $errores[] = "Área destino requerida";
            if (empty($form_data['contenido'])) $errores[] = "Contenido requerido";

            if ($form_data['area_id'] === 'otro' && empty(trim($form_data['area_origen_custom']))) {
                $errores[] = "Especifique el área de origen personalizada";
            }
            
            if ($form_data['area_destino_id'] === 'otro' && empty(trim($form_data['area_destino_custom']))) {
                $errores[] = "Especifique el área destino personalizada";
            }

            if (!empty($errores)) {
                $_SESSION['form_data'] = $form_data;
                header("Location: /project/public/subir-documento?error=" . urlencode(implode("|", $errores)));
                exit();
            }

            global $pdo;
            
            // Verificar folio único
            $stmt = $pdo->prepare("SELECT id FROM memorandos WHERE folio = ?");
            $stmt->execute([$form_data['folio']]);
            
            if ($stmt->fetch()) {
                $_SESSION['form_data'] = $form_data;
                header("Location: /project/public/subir-documento?error=" . urlencode("El folio ya existe"));
                exit();
            }
            
            // Validar archivo PDF
            if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['form_data'] = $form_data;
                header("Location: /project/public/subir-documento?error=" . urlencode("Archivo PDF requerido"));
                exit();
            }
            
            $archivo = $_FILES['documento'];
            $tipoArchivo = mime_content_type($archivo['tmp_name']);
            
            if (!in_array($tipoArchivo, ['application/pdf', 'application/x-pdf'])) {
                $_SESSION['form_data'] = $form_data;
                header("Location: /project/public/subir-documento?error=" . urlencode("Solo se permiten archivos PDF"));
                exit();
            }
            
            if ($archivo['size'] > 10 * 1024 * 1024) {
                $_SESSION['form_data'] = $form_data;
                header("Location: /project/public/subir-documento?error=" . urlencode("El archivo excede el tamaño máximo de 10MB"));
                exit();
            }
            
            // Procesar áreas personalizadas
            if ($form_data['area_id'] === 'otro') {
                $area_id = $this->crearAreaPersonalizada(trim($form_data['area_origen_custom']));
                if (!$area_id) {
                    $_SESSION['form_data'] = $form_data;
                    header("Location: /project/public/subir-documento?error=" . urlencode("Error al crear área de origen"));
                    exit();
                }
                $form_data['area_id'] = $area_id;
            }
            
            if ($form_data['area_destino_id'] === 'otro') {
                $area_destino_id = $this->crearAreaPersonalizada(trim($form_data['area_destino_custom']));
                if (!$area_destino_id) {
                    $_SESSION['form_data'] = $form_data;
                    header("Location: /project/public/subir-documento?error=" . urlencode("Error al crear área destino"));
                    exit();
                }
                $form_data['area_destino_id'] = $area_destino_id;
            }
            
            // Procesar documento
            $documento_blob = file_get_contents($archivo['tmp_name']);
            
            // Iniciar transacción
            $pdo->beginTransaction();
            
            try {
                // Insertar documento principal
                $stmt = $pdo->prepare("
                    INSERT INTO memorandos (
                        folio, entidad_productora, remitente_id, fecha_documento, 
                        contenido, urgencia, documento_blob, 
                        area_id, area_destino_id, fecha_limite, fecha_requerida_respuesta
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $form_data['folio'],
                    $form_data['entidad_productora'],
                    $_SESSION['usuario']['id'],
                    $form_data['fecha_documento'],
                    $form_data['contenido'],
                    $form_data['urgencia'],
                    $documento_blob,
                    $form_data['area_id'],
                    $form_data['area_destino_id'],
                    $form_data['fecha_limite'],
                    $form_data['fecha_requerida_respuesta']
                ]);
                
                $memorando_id = $pdo->lastInsertId();
                $remitente_nombre = $_SESSION['usuario']['nombre'];
                
                // Insertar destinatarios
                foreach ($form_data['destinatarios'] as $destinatario_id) {
                    $stmt = $pdo->prepare("
                        INSERT INTO documento_destinatarios (memorando_id, destinatario_id, estatus)
                        VALUES (?, ?, 'pendiente')
                    ");
                    $stmt->execute([$memorando_id, $destinatario_id]);
                    
                    $this->crearNotificacion($destinatario_id, $form_data['folio'], $remitente_nombre);
                    
                    if ($form_data['enviar_correo']) {
                        $stmt = $pdo->prepare("SELECT nombre, correo FROM usuarios WHERE id = ?");
                        $stmt->execute([$destinatario_id]);
                        $destinatario = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($destinatario) {
                            EmailService::enviarNotificacionDocumento(
                                $destinatario['correo'],
                                $destinatario['nombre'],
                                $form_data['folio'],
                                $remitente_nombre
                            );
                        }
                    }
                }
                
                $pdo->commit();
                
                // Limpiar datos de sesión
                unset($_SESSION['form_data']);
                
                // Redirigir con éxito
                $mensaje = "Documento subido correctamente a " . count($form_data['destinatarios']) . " destinatario(s)";
                if ($form_data['enviar_correo']) {
                    $mensaje .= " (notificaciones enviadas)";
                }
                
                header("Location: /project/public/documentos?success=" . urlencode($mensaje));
                exit();
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['form_data'] = $form_data;
                error_log("Error en transacción: " . $e->getMessage());
                header("Location: /project/public/subir-documento?error=" . urlencode("Error al guardar el documento"));
                exit();
            }
            
        } catch (Exception $e) {
            $_SESSION['form_data'] = $form_data;
            error_log("Error en subirDocumento: " . $e->getMessage());
            header("Location: /project/public/subir-documento?error=" . urlencode("Error interno del servidor"));
            exit();
        }
    }
    
    private function crearNotificacion($destinatario_id, $folio, $remitente_nombre) {
        global $pdo;
        
        try {
            $mensaje = "Nuevo documento recibido: $folio de $remitente_nombre";
            
            $stmt = $pdo->prepare("
                INSERT INTO notificaciones (usuario_id, mensaje, tipo, leida) 
                VALUES (?, ?, 'documento_recibido', 0)
            ");
            
            return $stmt->execute([$destinatario_id, $mensaje]);
        } catch (Exception $e) {
            error_log("Error al crear notificación: " . $e->getMessage());
            return false;
        }
    }
}
?>