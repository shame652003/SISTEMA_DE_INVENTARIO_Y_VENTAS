<?php

namespace App\Controllers;

use App\Core\Controller;

class AyudaController extends Controller
{
    public function index(): void
    {
        $this->render('ayuda/ayuda', [
            'titulo' => 'Centro de Ayuda',
            'seccion' => 'ayuda',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/ayuda.js"></script>',
        ], 'app');
    }
}
