<?php

namespace App\Controllers;

use App\Core\Controller;

class VentaController extends Controller
{
    public function index(): void
    {
        $this->render('ventas/ventas', [
            'titulo' => 'Gestión de Ventas',
            'seccion' => 'ventas',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/ventas.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener ventas de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        // TODO: Registrar nueva venta
        $this->json(['ok' => true, 'mensaje' => 'Venta registrada correctamente.']);
    }
}
