<?php

namespace App\Controllers;

use App\Core\Controller;

class AyudaController extends Controller
{
    public function index(): void
    {
        $this->render('ayuda/ayuda', array_merge([
            'titulo' => 'Centro de Ayuda',
            'seccion' => 'ayuda',
        ], $this->datosModulo('ayuda', 'ayuda.js')), 'app');
    }
}
