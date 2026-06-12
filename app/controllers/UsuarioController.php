<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->render('usuarios/usuarios', [
            'titulo' => 'Gestión de Usuarios',
            'seccion' => 'usuarios',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/usuarios.js"></script>',
        ], 'app');
    }

    // ─── USUARIOS ───

    public function listar(): void
    {
        $usuario = new Usuario();
        $usuarios = $usuario->obtenerTodos();
        $this->json(['data' => $usuarios ?: []]);
    }

    public function obtener(): void
    {
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $usuario = new Usuario();
        $u = $usuario->obtenerPorCedula((int) $cedula);
        $this->json(['ok' => !!$u, 'data' => $u]);
    }

    public function guardar(): void
    {
        $id = $_POST['id'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $segNombre = $_POST['segNombre'] ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $segApellido = $_POST['segApellido'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $idRol = $_POST['idRol'] ?? 0;
        $status = $_POST['status'] ?? 1;

        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos obligatorios deben estar completos.'], 400);
            return;
        }

        $usuario = new Usuario();
        $datos = [
            'cedula' => $cedula,
            'nombre' => $nombre,
            'segNombre' => $segNombre,
            'apellido' => $apellido,
            'segApellido' => $segApellido,
            'correo' => $correo,
            'telefono' => $telefono,
            'idRol' => $idRol,
            'status' => $status,
        ];

        // ─── Manejar imagen ───
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/usuarios/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename = 'usuario_' . $cedula . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                $datos['img'] = BASE_URL . '/uploads/usuarios/' . $filename;
            }
        }

        if (!empty($id)) {
            // Actualizar
            unset($datos['cedula']);
            if (!empty($_POST['clave'])) {
                $datos['clave'] = password_hash($_POST['clave'], PASSWORD_DEFAULT);
            }
            $ok = $usuario->actualizar((int) $id, $datos);
            $mensaje = $ok ? 'Usuario actualizado correctamente.' : 'Error al actualizar usuario.';
        } else {
            // Crear - contraseña predeterminada
            $datos['clave'] = password_hash('123456', PASSWORD_DEFAULT);
            $ok = $usuario->crear($datos);
            $mensaje = $ok ? 'Usuario creado correctamente. Contraseña predeterminada: 123456' : 'Error al crear usuario.';
        }

        $this->json(['ok' => $ok, 'mensaje' => $mensaje]);
    }

    public function eliminar(): void
    {
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $usuario = new Usuario();
        $ok = $usuario->eliminar((int) $cedula);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Usuario eliminado correctamente.' : 'Error al eliminar usuario.']);
    }

    // ─── ROLES ───

    public function listarRoles(): void
    {
        $rol = new Rol();
        $roles = $rol->obtenerTodos();
        $this->json(['data' => $roles ?: []]);
    }

    public function guardarRol(): void
    {
        $id = $_POST['idRol'] ?? '';
        $nombreRol = $_POST['nombreRol'] ?? '';

        if (empty($nombreRol)) {
            $this->json(['ok' => false, 'mensaje' => 'El nombre del rol es obligatorio.'], 400);
            return;
        }

        $rol = new Rol();
        if (!empty($id)) {
            $ok = $rol->actualizar((int) $id, ['nombreRol' => $nombreRol]);
            $mensaje = $ok ? 'Rol actualizado correctamente.' : 'Error al actualizar rol.';
        } else {
            $ok = $rol->crear(['nombreRol' => $nombreRol]);
            $mensaje = $ok ? 'Rol creado correctamente.' : 'Error al crear rol.';
        }

        $this->json(['ok' => $ok, 'mensaje' => $mensaje]);
    }

    public function eliminarRol(): void
    {
        $idRol = $_POST['idRol'] ?? 0;
        if (empty($idRol)) {
            $this->json(['ok' => false, 'mensaje' => 'ID de rol requerido.'], 400);
            return;
        }

        $rol = new Rol();
        $ok = $rol->eliminar((int) $idRol);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Rol eliminado correctamente.' : 'Error al eliminar rol.']);
    }
}
