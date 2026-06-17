<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Salida;

class SalidaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('salidas')) return;
        $salida = new Salida();
        $this->render('salidas/salidas', [
            'titulo' => 'Salidas de Productos',
            'seccion' => 'salidas',
            'tipos' => $salida->obtenerTipos(),
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/salidas.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('salidas')) return;
        $salida = new Salida();
        $salidas = $salida->obtenerTodos();
        $this->json(['data' => $salidas ?: []]);
    }

    public function buscarProductos(): void
    {
        if (!$this->verificarPermiso('salidas')) return;
        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }
        $salida = new Salida();
        $productos = $salida->buscarProductos($q);
        $this->json(['results' => $productos ?: []]);
    }

    public function productoInfo(): void
    {
        if (!$this->verificarPermiso('salidas')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $salida = new Salida();
        $producto = $salida->obtenerProductoInfo($id);
        $this->json(['ok' => !!$producto, 'data' => $producto]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('salidas')) return;

        $itemsJson = $_POST['items'] ?? '';

        if (empty($itemsJson)) {
            $this->json(['ok' => false, 'mensaje' => 'No hay productos en la lista.']);
            return;
        }

        $items = json_decode($itemsJson, true);
        if (!is_array($items) || count($items) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'La lista de productos está vacía.']);
            return;
        }

        $salida = new Salida();

        try {
            $procesados = 0;

            foreach ($items as $item) {
                $idproducto = (int) ($item['idproducto'] ?? 0);
                $idTipoSalidaA = (int) ($item['idTipoSalidaA'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $descripcion = trim($item['descripcion'] ?? '');

                if ($idproducto <= 0 || $idTipoSalidaA <= 0 || $cantidad <= 0) {
                    throw new \Exception('Datos inválidos en un producto.');
                }

                if (empty($descripcion)) {
                    throw new \Exception('La descripción es obligatoria para todos los productos.');
                }

                $producto = $salida->obtenerProductoInfo($idproducto);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado o inactivo.');
                }

                if ($cantidad > (float) $producto['stock']) {
                    throw new \Exception(
                        'Stock insuficiente para ' . $producto['codigo'] . '. Disponible: ' . number_format((float) $producto['stock'], 3)
                    );
                }

                $datos = [
                    'idproducto' => $idproducto,
                    'idTipoSalidaA' => $idTipoSalidaA,
                    'cantidad' => $cantidad,
                    'descripcion' => $descripcion,
                ];

                $salida->crear($datos);
                $procesados++;
            }

            $this->json([
                'ok' => true,
                'mensaje' => 'Salida registrada correctamente. Se procesaron ' . $procesados . ' producto(s).',
            ]);
        } catch (\Exception $e) {
            $this->json(['ok' => false, 'mensaje' => 'Error al registrar la salida: ' . $e->getMessage()]);
        }
    }
}
