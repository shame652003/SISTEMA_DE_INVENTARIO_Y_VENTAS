<?php

namespace App\Controllers;

use App\Core\Controller;

class EntradaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('entradas')) return;
        $this->render('entradas/entradas', [
            'titulo' => 'Entradas de Productos',
            'seccion' => 'entradas',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/entradas.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        // TODO: Obtener entradas de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        // TODO: Registrar nueva entrada de productos
        $this->json(['ok' => true, 'mensaje' => 'Entrada registrada correctamente.']);
    }
}
