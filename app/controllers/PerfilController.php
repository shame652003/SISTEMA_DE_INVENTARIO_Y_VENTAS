<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Models\Usuario;

class PerfilController extends Controller
{
    public function index(): void
    {
        $this->render('perfil/perfil', array_merge([
            'titulo' => 'Mi Perfil',
            'seccion' => 'perfil',
        ], $this->datosModulo('perfil', 'perfil.js')), 'app');
    }

    public function obtener(): void
    {
        $usuario = AuthMiddleware::usuario();
        
        // Fallback: usar $_REQUEST['auth'] directamente
        if (!$usuario) {
            $usuario = $_REQUEST['auth'] ?? null;
        }
        
        if (!$usuario) {
            $this->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401);
            return;
        }

        $model = new Usuario();
        $data = $model->obtenerPorCedula((int) $usuario['cedula']);
        $this->json(['ok' => !!$data, 'data' => $data]);
    }

    public function actualizar(): void
    {
        $usuario = AuthMiddleware::usuario();
        if (!$usuario) {
            $usuario = $_REQUEST['auth'] ?? null;
        }
        if (!$usuario) {
            $this->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401);
            return;
        }

        $cedula = (int) $usuario['cedula'];
        $nombre = mb_convert_case(trim($_POST['nombre'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $segNombre = mb_convert_case(trim($_POST['segNombre'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $apellido = mb_convert_case(trim($_POST['apellido'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $segApellido = mb_convert_case(trim($_POST['segApellido'] ?? ''), MB_CASE_TITLE, 'UTF-8');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos obligatorios deben estar completos.'], 400);
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json(['ok' => false, 'mensaje' => 'Correo electrónico inválido.'], 400);
            return;
        }

        if (!preg_match('/^[\d\-\(\)\+\s]{7,20}$/', $telefono)) {
            $this->json(['ok' => false, 'mensaje' => 'Teléfono inválido. Use solo dígitos, guiones, paréntesis o +.'], 400);
            return;
        }

        $model = new Usuario();
        $datos = [
            'nombre' => $nombre,
            'segNombre' => $segNombre,
            'apellido' => $apellido,
            'segApellido' => $segApellido,
            'correo' => $correo,
            'telefono' => $telefono,
        ];

        // Imagen
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $mime = $_FILES['imagen']['type'];
            $size = $_FILES['imagen']['size'];

            if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
                $this->json(['ok' => false, 'mensaje' => 'La imagen debe ser JPG, PNG o WEBP.'], 400);
                return;
            }

            if ($size > 5 * 1024 * 1024) {
                $this->json(['ok' => false, 'mensaje' => 'La imagen no puede superar los 5 MB.'], 400);
                return;
            }

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/usuarios/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'perfil_' . $cedula . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                $datos['img'] = BASE_URL . '/uploads/usuarios/' . $filename;
            } else {
                $this->json(['ok' => false, 'mensaje' => 'Error al subir la imagen. Verifique permisos de la carpeta uploads.'], 500);
                return;
            }
        } elseif (!empty($_POST['eliminar_img']) && $_POST['eliminar_img'] === '1') {
            $datos['img'] = null;
        }

        $ok = $model->actualizar($cedula, $datos);
        if (!$ok) {
            $this->json(['ok' => false, 'mensaje' => 'Error al actualizar en la base de datos.'], 500);
            return;
        }

        $usuarioActualizado = $model->obtenerPorCedula($cedula);
        $nuevoToken = \App\Core\JwtHandler::generarAccessToken($usuarioActualizado);
        $configJwt = require dirname(__DIR__) . '/config/jwt.php';
        setcookie('jwt_token', $nuevoToken, [
            'expires'  => time() + $configJwt['access_ttl'],
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $this->json(['ok' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
    }

    public function cambiarClave(): void
    {
        $usuario = AuthMiddleware::usuario();
        if (!$usuario) {
            $usuario = $_REQUEST['auth'] ?? null;
        }
        if (!$usuario) {
            $this->json(['ok' => false, 'mensaje' => 'No autenticado.'], 401);
            return;
        }

        $cedula = (int) $usuario['cedula'];
        $claveActual = $_POST['clave_actual'] ?? '';
        $claveNueva = $_POST['clave_nueva'] ?? '';
        $claveConfirmar = $_POST['clave_confirmar'] ?? '';

        if (empty($claveActual) || empty($claveNueva) || empty($claveConfirmar)) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos son obligatorios.'], 400);
            return;
        }

        if ($claveNueva !== $claveConfirmar) {
            $this->json(['ok' => false, 'mensaje' => 'La nueva contraseña y la confirmación no coinciden.'], 400);
            return;
        }

        if (strlen($claveNueva) < 6) {
            $this->json(['ok' => false, 'mensaje' => 'La nueva contraseña debe tener al menos 6 caracteres.'], 400);
            return;
        }

        $model = new Usuario();
        $u = $model->obtenerPorCedula($cedula);

        if (!$u || !password_verify($claveActual, $u['clave'])) {
            $this->json(['ok' => false, 'mensaje' => 'La contraseña actual es incorrecta.'], 400);
            return;
        }

        if ($claveNueva === $claveActual) {
            $this->json(['ok' => false, 'mensaje' => 'La nueva contraseña debe ser diferente a la actual.'], 400);
            return;
        }

        $ok = $model->actualizar($cedula, ['clave' => password_hash($claveNueva, PASSWORD_DEFAULT)]);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Contraseña cambiada correctamente.' : 'Error al cambiar contraseña.'], $ok ? 200 : 500);
    }
}
