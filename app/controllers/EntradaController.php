<?php

namespace App\Controllers;

use App\Core\Controller;

class EntradaController extends Controller
{
    public function index(): void
    {
        $this->render('entradas/entradas', [
            'titulo' => 'Entradas de Productos',
            'seccion' => 'entradas',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/entradas.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener entradas de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        // TODO: Registrar nueva entrada de productos
        $this->json(['ok' => true, 'mensaje' => 'Entrada registrada correctamente.']);
    }
}
