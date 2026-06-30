<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
use App\Models\Reporte;

class DashboardController extends Controller
{
    public function index(): void
    {
        $reporte = new Reporte();
        $resumen = $reporte->resumenDashboard();

        $this->render('dashboard/dashboard', array_merge([
            'titulo' => 'Dashboard',
            'seccion' => 'dashboard',
            'resumen' => $resumen ?: [],
        ], $this->datosModulo('dashboard', 'dashboard.js')), 'app');
    }
}
