<?php

namespace App\Controllers;

use App\Core\Controller;

class BitacoraController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('bitacora')) return;
        $this->render('bitacora/bitacora', array_merge([
            'titulo' => 'Bitácora de Auditoría',
            'seccion' => 'bitacora',
        ], $this->datosModulo('bitacora', 'bitacora.js')), 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('bitacora')) return;
        // TODO: Obtener registros de bitácora
        $this->json(['data' => []]);
    }
}
