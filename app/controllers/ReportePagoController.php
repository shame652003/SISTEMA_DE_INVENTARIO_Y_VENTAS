<?php

namespace App\Controllers;

use App\Core\Controller;

class ReportePagoController extends Controller
{
    public function index(): void
    {
        $this->render('reportes_pagos/reportes_pagos', array_merge([
            'titulo' => 'Reportes de Pagos',
            'seccion' => 'reportes_pagos',
        ], $this->datosModulo('reportes_pagos', 'reportes.js')), 'app');
    }

    public function generar(): void
    {
        // TODO: Generar reporte de pagos según filtros
        $this->json(['ok' => true, 'data' => []]);
    }
}
