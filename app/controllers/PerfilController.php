<?php

namespace App\Controllers;

use App\Core\Controller;

class PerfilController extends Controller
{
    public function index(): void
    {
        $this->render('perfil/perfil', [
            'titulo' => 'Mi Perfil',
            'seccion' => 'perfil',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/perfil.js"></script>',
        ], 'app');
    }

    public function actualizar(): void
    {
        // TODO: Actualizar datos del perfil
        $this->json(['ok' => true, 'mensaje' => 'Perfil actualizado correctamente.']);
    }
}
