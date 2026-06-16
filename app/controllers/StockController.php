<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;

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
        $reporte = new Reporte();
        
        $data = [];
        if ($filtro === 'bajo') {
            $data = $reporte->stockBajo();
        } elseif ($filtro === 'agotado') {
            $data = $reporte->fetchAll("SELECT * FROM vw_stock_agotado ORDER BY nombre");
        } else {
            $data = $reporte->stockDisponible();
        }
        
        $this->json(['data' => $data ?: []]);
    }
}
