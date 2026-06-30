<?php

namespace App\Controllers;

use App\Core\Controller;

class ReporteGeneralController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('reportes_generales')) return;
        $this->render('reportes_generales/reportes_generales', array_merge([
            'titulo' => 'Reportes Generales',
            'seccion' => 'reportes_generales',
        ], $this->datosModulo('reportes_generales', 'reportes.js')), 'app');
    }

    public function generar(): void
    {
        if (!$this->verificarPermiso('reportes_generales')) return;
        // TODO: Generar reporte general según filtros
        $this->json(['ok' => true, 'data' => []]);
    }
}
