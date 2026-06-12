<?php

namespace App\Controllers;

use App\Core\Controller;

class ReporteGeneralController extends Controller
{
    public function index(): void
    {
        $this->render('reportes_generales/reportes_generales', [
            'titulo' => 'Reportes Generales',
            'seccion' => 'reportes_generales',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/reportes.js"></script>',
        ], 'app');
    }

    public function generar(): void
    {
        // TODO: Generar reporte general según filtros
        $this->json(['ok' => true, 'data' => []]);
    }
}
