<?php

namespace App\Controllers;

use App\Core\Controller;

class BitacoraController extends Controller
{
    public function index(): void
    {
        $this->render('bitacora/bitacora', [
            'titulo' => 'Bitácora de Auditoría',
            'seccion' => 'bitacora',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/bitacora.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener registros de bitácora
        $this->json(['data' => []]);
    }
}
