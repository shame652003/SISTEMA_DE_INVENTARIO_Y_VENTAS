<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Models\Usuario;
use App\Models\Rol;

class UsuarioController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('usuarios')) return;
        $this->render('usuarios/usuarios', array_merge([
            'titulo' => 'Gestión de Usuarios',
            'seccion' => 'usuarios',
        ], $this->datosModulo('usuarios', 'usuarios.js')), 'app');
    }

    // ─── USUARIOS ───

    public function listar(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;

        $draw    = (int) ($_POST['draw'] ?? 1);
        $start   = max(0, (int) ($_POST['start'] ?? 0));
        $length  = (int) ($_POST['length'] ?? 10);
        if ($length < 1 && $length !== -1) {
            $length = 10;
        }
        $search  = $_POST['search']['value'] ?? '';

        $colMap  = ['cedula', 'nombre', 'nombre', 'correo', 'telefono', 'nombreRol', 'status'];
        $colIdx  = (int) ($_POST['order'][0]['column'] ?? 1);
        $orderBy = $colMap[$colIdx] ?? 'cedula';
        $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');

        $usuario = new Usuario();
        $cedulaLogeado = AuthMiddleware::cedula();
        $data = $usuario->obtenerTodosPaginado($start, $length, $search, $orderBy, $orderDir, $cedulaLogeado);
        $totalFiltrado = $usuario->contarUsuarios($search, $cedulaLogeado);
        $totalSinFiltro = $usuario->contarUsuarios('', $cedulaLogeado);

        $this->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalSinFiltro,
            'recordsFiltered' => $totalFiltrado,
            'data'            => $data ?: [],
        ]);
    }

    public function obtener(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
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
        if (!$this->verificarPermiso('usuarios')) return;
        $id = $_POST['id'] ?? '';
        $cedula = $_POST['cedula'] ?? '';
        $nombre = mb_convert_case($_POST['nombre'] ?? '', MB_CASE_TITLE, 'UTF-8');
        $segNombre = mb_convert_case($_POST['segNombre'] ?? '', MB_CASE_TITLE, 'UTF-8');
        $apellido = mb_convert_case($_POST['apellido'] ?? '', MB_CASE_TITLE, 'UTF-8');
        $segApellido = mb_convert_case($_POST['segApellido'] ?? '', MB_CASE_TITLE, 'UTF-8');
        $correo = strtolower(trim($_POST['correo'] ?? ''));
        $telefono = $_POST['telefono'] ?? '';
        $idRol = $_POST['idRol'] ?? 0;
        $status = $_POST['status'] ?? 1;

        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($correo) || empty($telefono)) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos obligatorios deben estar completos.'], 400);
            return;
        }

        $cedulaInt = (int) $cedula;
        if ($cedulaInt <= 0 || $cedulaInt > 999999999) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula fuera de rango válido.'], 400);
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

        if (!in_array((int) $status, [0, 1], true)) {
            $status = 1;
        }

        $usuario = new Usuario();

        if (empty($id)) {
            if ($usuario->existeCedula((int) $cedula)) {
                $this->json(['ok' => false, 'mensaje' => "La cédula $cedula ya está registrada."], 409);
                return;
            }
            if ($usuario->existeCorreo($correo)) {
                $this->json(['ok' => false, 'mensaje' => "El correo $correo ya está registrado."], 409);
                return;
            }
        } else {
            if ($usuario->existeCorreo($correo, (int) $id)) {
                $this->json(['ok' => false, 'mensaje' => "El correo $correo ya está registrado por otro usuario."], 409);
                return;
            }
        }
        $datos = [
            'cedula' => $cedulaInt,
            'nombre' => $nombre,
            'segNombre' => $segNombre,
            'apellido' => $apellido,
            'segApellido' => $segApellido,
            'correo' => $correo,
            'telefono' => $telefono,
            'idRol' => (int) $idRol,
            'status' => (int) $status,
        ];

        // ─── Manejar imagen ───
        if (!empty($_FILES['imagen']['tmp_name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            $mime = $_FILES['imagen']['type'];

            if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
                $this->json(['ok' => false, 'mensaje' => 'La imagen debe ser JPG, PNG o WEBP.'], 400);
                return;
            }

            if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
                $this->json(['ok' => false, 'mensaje' => 'La imagen no puede superar los 5 MB.'], 400);
                return;
            }

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/usuarios/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'usuario_' . $cedulaInt . '_' . time() . '.' . $ext;
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

        $this->json(['ok' => $ok, 'mensaje' => $mensaje], $ok ? 200 : 500);
    }

    public function eliminar(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $usuario = new Usuario();
        $ok = $usuario->eliminar((int) $cedula);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Usuario eliminado correctamente.' : 'Error al eliminar usuario.'], $ok ? 200 : 500);
    }

    public function verificarCedula(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $cedula = $_POST['cedula'] ?? 0;
        if (empty($cedula)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula requerida.'], 400);
            return;
        }

        $usuario = new Usuario();
        $existe = $usuario->existeCedula((int) $cedula);
        $this->json(['ok' => true, 'existe' => $existe]);
    }

    // ─── ROLES ───

    public function listarRoles(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $rol = new Rol();
        $roles = $rol->obtenerTodos();
        $this->json(['data' => $roles ?: []]);
    }

    public function guardarRol(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $id = $_POST['idRol'] ?? '';
        $nombreRol = mb_convert_case($_POST['nombreRol'] ?? '', MB_CASE_TITLE, 'UTF-8');

        if (empty($nombreRol)) {
            $this->json(['ok' => false, 'mensaje' => 'El nombre del rol es obligatorio.'], 400);
            return;
        }

        $rol = new Rol();
        if (!empty($id)) {
            if ($rol->existeNombre($nombreRol, (int) $id)) {
                $this->json(['ok' => false, 'mensaje' => "El rol \"$nombreRol\" ya existe."], 409);
                return;
            }
            $ok = $rol->actualizar((int) $id, ['nombreRol' => $nombreRol]);
            $mensaje = $ok ? 'Rol actualizado correctamente.' : 'Error al actualizar rol.';
        } else {
            if ($rol->existeNombre($nombreRol)) {
                $this->json(['ok' => false, 'mensaje' => "El rol \"$nombreRol\" ya existe."], 409);
                return;
            }
            $ok = $rol->crear(['nombreRol' => $nombreRol]);
            $mensaje = $ok ? 'Rol creado correctamente.' : 'Error al crear rol.';
        }

        $this->json(['ok' => $ok, 'mensaje' => $mensaje], $ok ? 200 : 500);
    }

    public function eliminarRol(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $idRol = $_POST['idRol'] ?? 0;
        if (empty($idRol)) {
            $this->json(['ok' => false, 'mensaje' => 'ID de rol requerido.'], 400);
            return;
        }

        $rol = new Rol();
        if ($rol->tieneUsuariosActivos((int) $idRol)) {
            $this->json(['ok' => false, 'mensaje' => 'No se puede eliminar el rol porque está asignado a usuarios activos.'], 409);
            return;
        }
        $ok = $rol->eliminar((int) $idRol);
        $this->json(['ok' => $ok, 'mensaje' => $ok ? 'Rol eliminado correctamente.' : 'Error al eliminar rol.'], $ok ? 200 : 500);
    }

    public function verificarUsoRol(): void
    {
        if (!$this->verificarPermiso('usuarios')) return;
        $idRol = $_POST['idRol'] ?? 0;
        if (empty($idRol)) {
            $this->json(['ok' => false, 'mensaje' => 'ID de rol requerido.'], 400);
            return;
        }

        $rol = new Rol();
        $count = $rol->contarUsuariosActivos((int) $idRol);
        $this->json(['ok' => true, 'enUso' => $count > 0, 'count' => $count]);
    }
}
