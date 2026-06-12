<?php

namespace App\Controllers;

use App\Core\Controller;

class ClienteController extends Controller
{
    public function index(): void
    {
        $this->render('clientes/clientes', [
            'titulo' => 'Gestión de Clientes',
            'seccion' => 'clientes',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/clientes.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener clientes de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        // TODO: Insertar o actualizar cliente
        $this->json(['ok' => true, 'mensaje' => 'Cliente guardado correctamente.']);
    }

    public function eliminar(): void
    {
        // TODO: Eliminar cliente
        $this->json(['ok' => true, 'mensaje' => 'Cliente eliminado correctamente.']);
    }
}
