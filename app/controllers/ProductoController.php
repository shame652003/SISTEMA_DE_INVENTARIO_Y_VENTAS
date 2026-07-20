<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;

class ProductoController extends Controller
{
    private const IMG_DIR = __DIR__ . '/../../public/assets/img/productos/';
    private const IMG_URL = 'assets/img/productos/';
    private const MAX_WIDTH = 800;
    private const MAX_HEIGHT = 800;
    private const WEBP_QUALITY = 80;
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];

    public function index(): void
    {
        if (!$this->verificarPermisoVista('productos')) return;
        $producto = new Producto();
        $this->render('productos/productos', array_merge([
            'titulo' => 'Gestión de Productos',
            'seccion' => 'productos',
            'tipos' => $producto->obtenerTipos(),
        ], $this->datosModulo('productos', 'productos.js')), 'app');
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
        if ($p) {
            $p['tiene_ventas'] = $producto->productoTieneVentas($id);
        }
        $this->json(['ok' => !!$p, 'data' => $p]);
    }

    private function procesarImagen(array $file): ?string
    {
        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
        if (!$mime) {
            $mime = $file['type'] ?? '';
        }

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            return null;
        }

        if (!is_dir(self::IMG_DIR)) {
            if (!mkdir(self::IMG_DIR, 0755, true)) {
                return null;
            }
        }

        $tieneGD = function_exists('imagecreatefromjpeg');

        if (!$tieneGD) {
            $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $ext = $extMap[$mime] ?? 'jpg';
            $nombreArchivo = uniqid('prod_') . '.' . $ext;
            $rutaDestino = self::IMG_DIR . $nombreArchivo;
            if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                return self::IMG_URL . $nombreArchivo;
            }
            return null;
        }

        switch ($mime) {
            case 'image/jpeg':
                $src = @imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $src = @imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $src = @imagecreatefromwebp($file['tmp_name']);
                break;
            default:
                $src = @imagecreatefromstring(file_get_contents($file['tmp_name']));
                break;
        }

        if (!$src) return null;

        $origW = imagesx($src);
        $origH = imagesy($src);
        $ratio = min(self::MAX_WIDTH / $origW, self::MAX_HEIGHT / $origH, 1);
        $newW = (int) round($origW * $ratio);
        $newH = (int) round($origH * $ratio);

        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        $usaWebp = function_exists('imagewebp');
        $ext = $usaWebp ? 'webp' : 'jpg';
        $nombreArchivo = uniqid('prod_') . '.' . $ext;
        $rutaDestino = self::IMG_DIR . $nombreArchivo;

        if ($usaWebp) {
            $ok = imagewebp($resized, $rutaDestino, self::WEBP_QUALITY);
        } else {
            if ($mime === 'image/png') {
                $bg = imagecreatetruecolor($newW, $newH);
                imagefill($bg, 0, 0, 0xFFFFFF);
                imagecopy($bg, $resized, 0, 0, 0, 0, $newW, $newH);
                imagedestroy($resized);
                $resized = $bg;
            }
            $ok = imagejpeg($resized, $rutaDestino, 85);
        }
        imagedestroy($resized);

        return $ok ? (self::IMG_URL . $nombreArchivo) : null;
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('productos')) return;

        $id = $_POST['id'] ?? '';
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $nombre = strtoupper(trim($_POST['nombre'] ?? ''));
        $marca = ucwords(trim($_POST['marca'] ?? ''));
        $idTipoA = (int) ($_POST['idTipoA'] ?? 0);

        if (empty($codigo) || empty($nombre) || $idTipoA <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Código, nombre y tipo son obligatorios.']);
            return;
        }

        if (!preg_match('/^[A-Z0-9]+$/', $codigo)) {
            $this->json(['ok' => false, 'mensaje' => 'El código solo debe contener letras y números.']);
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
            $imgPath = $this->procesarImagen($_FILES['imagen']);
            if ($imgPath !== null) {
                $datos['imgproducto'] = $imgPath;
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

        $this->json(['ok' => $ok, 'mensaje' => $mensaje], $ok ? 200 : 500);
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
        if ($producto->productoTieneVentas($id)) {
            $this->json(['ok' => false, 'mensaje' => 'No se puede eliminar el producto porque tiene ventas asociadas.']);
            return;
        }
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

    public function verificarCodigo(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $excluirId = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $producto = new Producto();
        $existe = $excluirId
            ? $producto->existeCodigo($codigo, $excluirId)
            : $producto->existeCodigo($codigo);
        $this->json(['ok' => true, 'existe' => $existe]);
    }

    public function verificarTipo(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        $tipo = trim($_POST['tipo'] ?? '');
        $producto = new Producto();
        $existe = $producto->existeTipo($tipo);
        $this->json(['ok' => true, 'existe' => $existe]);
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
        $tipo = ucwords(trim($_POST['tipo'] ?? ''));
        if (empty($tipo)) {
            $this->json(['ok' => false, 'mensaje' => 'El nombre del tipo es obligatorio.']);
            return;
        }
        $producto = new Producto();
        if ($producto->existeTipo($tipo)) {
            $this->json(['ok' => false, 'mensaje' => 'El tipo de producto ya está registrado.']);
            return;
        }
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
