<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Stock;

class StockController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('stock')) return;
        $this->render('stock/stock', array_merge([
            'titulo' => 'Stock e Inventario',
            'seccion' => 'stock',
        ], $this->datosModulo('stock', 'stock.js')), 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('stock')) return;

        $draw    = (int) ($_POST['draw'] ?? 1);
        $start   = max(0, (int) ($_POST['start'] ?? 0));
        $length  = (int) ($_POST['length'] ?? 10);
        if ($length < 1 && $length !== -1) {
            $length = 10;
        }
        $search  = $_POST['search']['value'] ?? '';
        $filtro  = $_POST['filtro'] ?? 'todos';

        $colMap  = ['', 'codigo', 'nombre', 'tipo_producto', 'stock', 'precio_venta_ves', 'precio_venta_ves', 'precio_venta_usd'];
        $colIdx  = (int) ($_POST['order'][0]['column'] ?? 2);
        $orderBy = $colMap[$colIdx] ?? 'nombre';
        $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'ASC');

        $stock = new Stock();
        $data  = $stock->obtenerTodosPaginado($start, $length, $search, $filtro, $orderBy, $orderDir);
        $totalFiltrado = $stock->contarProductos($search, $filtro);
        $totalSinFiltro = $stock->contarProductos('', 'todos');

        $tasa = $stock->obtenerTasaActual();
        $tasaBcv = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;

        if ($data) {
            foreach ($data as &$p) {
                $precioVes = (float) ($p['precio_venta_ves'] ?? 0);
                $p['precio_bcv'] = $tasaBcv > 0 ? round($precioVes / $tasaBcv, 2) : 0;
            }
        }

        $this->json([
            'draw'            => $draw,
            'recordsTotal'    => $totalSinFiltro,
            'recordsFiltered' => $totalFiltrado,
            'data'            => $data ?: [],
        ]);
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

        $tasa = $stock->obtenerTasaActual();
        $tasaBcv = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;

        if ($productos) {
            foreach ($productos as &$producto) {
                $precioVentaVes = (float) ($producto['precio_venta_ves'] ?? 0);
                $producto['precio_bcv'] = $tasaBcv > 0 ? round($precioVentaVes / $tasaBcv, 2) : 0;
            }
        }

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
