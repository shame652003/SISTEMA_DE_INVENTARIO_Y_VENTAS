<?php

namespace App\Controllers;

use App\Core\Controller;

class SalidaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('salidas')) return;
        $this->render('salidas/salidas', [
            'titulo' => 'Salidas de Productos',
            'seccion' => 'salidas',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/salidas.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('salidas')) return;
        // TODO: Obtener salidas de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('salidas')) return;
        // TODO: Registrar nueva salida de productos
        $this->json(['ok' => true, 'mensaje' => 'Salida registrada correctamente.']);
    }
}
