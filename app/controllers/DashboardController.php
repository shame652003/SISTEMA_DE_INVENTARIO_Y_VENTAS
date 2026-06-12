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

        $this->render('dashboard/dashboard', [
            'titulo' => 'Dashboard',
            'seccion' => 'dashboard',
            'resumen' => $resumen ?: [],
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/dashboard.js"></script>',
        ], 'app');
    }
}
