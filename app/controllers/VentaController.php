<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venta;

class VentaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('ventas')) return;
        $venta = new Venta();
        $this->render('ventas/ventas', array_merge([
            'titulo' => 'Ventas',
            'seccion' => 'ventas',
            'tiposPago' => $venta->obtenerTiposPago(),
        ], $this->datosModulo('ventas', 'ventas.js')), 'app');
    }
}
