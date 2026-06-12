<?php

namespace App\Controllers;

use App\Core\Controller;

class ReportePagoController extends Controller
{
    public function index(): void
    {
        $this->render('reportes_pagos/reportes_pagos', [
            'titulo' => 'Reportes de Pagos',
            'seccion' => 'reportes_pagos',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/reportes.js"></script>',
        ], 'app');
    }

    public function generar(): void
    {
        // TODO: Generar reporte de pagos según filtros
        $this->json(['ok' => true, 'data' => []]);
    }
}
