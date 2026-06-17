<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Stock;

class StockController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('stock')) return;
        $this->render('stock/stock', [
            'titulo' => 'Stock e Inventario',
            'seccion' => 'stock',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/stock.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('stock')) return;

        $filtro = $_POST['filtro'] ?? 'todos';
        $stock = new Stock();
        $data = $stock->obtenerTodos($filtro);
        
        // Obtener tasa BCV actual
        $tasa = $stock->obtenerTasaActual();
        $tasaBcv = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
        
        // Calcular precio_bcv para cada producto
        if ($data) {
            foreach ($data as &$producto) {
                $precioVentaVes = (float) ($producto['precio_venta_ves'] ?? 0);
                $producto['precio_bcv'] = $tasaBcv > 0 ? round($precioVentaVes / $tasaBcv, 2) : 0;
            }
        }

        $this->json(['data' => $data ?: [], 'tasa_bcv' => $tasaBcv]);
    }

    public function buscarProductos(): void
    {
        if (!$this->verificarPermiso('stock')) return;
        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }

        $stock = new Stock();
        $productos = $stock->buscarProductos($q);
        $this->json(['results' => $productos ?: []]);
    }

    public function productoInfo(): void
    {
        if (!$this->verificarPermiso('stock')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }

        $stock = new Stock();
        $producto = $stock->obtenerProductoInfo($id);
        $this->json(['ok' => !!$producto, 'data' => $producto]);
    }
}
