<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;

class ProductoController extends Controller
{
    private const IMG_DIR = __DIR__ . '/../../public/assets/img/productos/';
    private const IMG_URL = 'assets/img/productos/';

    public function index(): void
    {
        if (!$this->verificarPermisoVista('productos')) return;
        $producto = new Producto();
        $this->render('productos/productos', [
            'titulo' => 'Gestión de Productos',
            'seccion' => 'productos',
            'tipos' => $producto->obtenerTipos(),
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/productos.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $producto = new Producto();
        $productos = $producto->obtenerTodos();
        $this->json(['data' => $productos ?: []]);
    }

    public function obtener(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $producto = new Producto();
        $p = $producto->obtenerPorId($id);
        $this->json(['ok' => !!$p, 'data' => $p]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('productos')) return;

        $id = $_POST['id'] ?? '';
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $marca = trim($_POST['marca'] ?? '');
        $idTipoA = (int) ($_POST['idTipoA'] ?? 0);

        if (empty($codigo) || empty($nombre) || $idTipoA <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Código, nombre y tipo son obligatorios.']);
            return;
        }

        $producto = new Producto();

        if (empty($id)) {
            if ($producto->existeCodigo($codigo)) {
                $this->json(['ok' => false, 'mensaje' => 'El código de producto ya está registrado.']);
                return;
            }
        } else {
            if ($producto->existeCodigo($codigo, (int) $id)) {
                $this->json(['ok' => false, 'mensaje' => 'El código de producto ya está registrado.']);
                return;
            }
        }

        $datos = [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'marca' => $marca,
            'idTipoA' => $idTipoA,
        ];

        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['imagen']['tmp_name'];
            $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nombreArchivo = uniqid('prod_') . '.' . strtolower($ext);
            $rutaDestino = self::IMG_DIR . $nombreArchivo;

            if (move_uploaded_file($tmp, $rutaDestino)) {
                $datos['imgproducto'] = self::IMG_URL . $nombreArchivo;
            }
        }

        if (empty($id)) {
            $ok = $producto->crear($datos);
            $mensaje = $ok ? 'Producto registrado correctamente.' : 'Error al registrar el producto.';
        } else {
            $p = $producto->obtenerPorId((int) $id);
            if ($p && isset($datos['imgproducto']) && !empty($p['imgproducto'])) {
                $rutaVieja = __DIR__ . '/../../public/' . $p['imgproducto'];
                if (file_exists($rutaVieja)) {
                    unlink($rutaVieja);
                }
            }
            $ok = $producto->actualizar((int) $id, $datos);
            $mensaje = $ok ? 'Producto actualizado correctamente.' : 'Error al actualizar el producto.';
        }

        $this->json(['ok' => $ok, 'mensaje' => $mensaje]);
    }

    public function eliminar(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $producto = new Producto();
        $p = $producto->obtenerPorId($id);
        if ($p && !empty($p['imgproducto'])) {
            $ruta = __DIR__ . '/../../public/' . $p['imgproducto'];
            if (file_exists($ruta)) {
                unlink($ruta);
            }
        }
        $ok = $producto->eliminar($id);
        $this->json([
            'ok' => $ok,
            'mensaje' => $ok ? 'Producto eliminado correctamente.' : 'Error al eliminar el producto.'
        ]);
    }

    /* ===== CRUD tipo_productos ===== */

    public function listarTipos(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $producto = new Producto();
        $tipos = $producto->obtenerTipos();
        $this->json(['data' => $tipos ?: []]);
    }

    public function guardarTipo(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $tipo = trim($_POST['tipo'] ?? '');
        if (empty($tipo)) {
            $this->json(['ok' => false, 'mensaje' => 'El nombre del tipo es obligatorio.']);
            return;
        }
        $producto = new Producto();
        $ok = $producto->crearTipo(['tipo' => $tipo, 'status' => 1]);
        $this->json([
            'ok' => $ok,
            'mensaje' => $ok ? 'Tipo de producto registrado correctamente.' : 'Error al registrar el tipo.'
        ]);
    }

    public function eliminarTipo(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $producto = new Producto();
        if ($producto->tipoEnUso($id)) {
            $this->json(['ok' => false, 'mensaje' => 'No se puede eliminar el tipo porque está asignado a productos activos.']);
            return;
        }
        $ok = $producto->eliminarTipo($id);
        $this->json([
            'ok' => $ok,
            'mensaje' => $ok ? 'Tipo eliminado correctamente.' : 'Error al eliminar el tipo.'
        ]);
    }
}
