<?php
require_once __DIR__ . '/../db.php';

class ProfileController {

    public function actualizarPerfil() {
        try {
            $usuario_id = $_SESSION['usuario']['id'];
            $nombre = trim($_POST['nombre'] ?? '');
            $contraseña_actual = $_POST['contraseña_actual'] ?? '';
            $nueva_contraseña = $_POST['nueva_contraseña'] ?? '';
            $confirmar_contraseña = $_POST['confirmar_contraseña'] ?? '';

            if (empty($nombre)) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'El nombre es obligatorio'];
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            global $pdo;

            // Verificar si se desea cambiar la contraseña
            if (!empty($nueva_contraseña)) {
                if (empty($contraseña_actual)) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Debes ingresar tu contraseña actual'];
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit;
                }

                if ($nueva_contraseña !== $confirmar_contraseña) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Las contraseñas nuevas no coinciden'];
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit;
                }

                if (strlen($nueva_contraseña) < 6) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'];
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit;
                }

                // Verificar contraseña actual
                $stmt = $pdo->prepare("SELECT contraseña_hash FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!password_verify($contraseña_actual, $usuario['contraseña_hash'])) {
                    $_SESSION['alert'] = ['type' => 'error', 'message' => 'La contraseña actual es incorrecta'];
                    header("Location: " . $_SERVER['HTTP_REFERER']);
                    exit;
                }

                // Actualizar nombre y contraseña
                $nueva_contraseña_hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, contraseña_hash = ? WHERE id = ?");
                $stmt->execute([$nombre, $nueva_contraseña_hash, $usuario_id]);

                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Perfil y contraseña actualizados correctamente'];
            } else {
                // Solo actualizar nombre
                $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?");
                $stmt->execute([$nombre, $usuario_id]);

                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Perfil actualizado correctamente'];
            }

            // Actualizar sesión
            $_SESSION['usuario']['nombre'] = $nombre;

            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;

        } catch (Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error interno del servidor'];
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
}
?>
