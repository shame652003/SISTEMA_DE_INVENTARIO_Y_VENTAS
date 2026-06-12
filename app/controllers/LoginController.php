<?php

namespace App\Controllers;

use App\Core\Controller;

class LoginController extends Controller
{
    public function index(): void
    {
        $this->render('login/login', [
            'titulo' => 'Iniciar Sesión',
            'extraCSS' => '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/login.css">',
            'extraJS'  => '<script src="' . BASE_URL . '/assets/js/login.js"></script>',
        ], 'auth');
    }

    public function recuperar(): void
    {
        $this->render('login/login', [
            'titulo' => 'Recuperar Contraseña',
            'extraCSS' => '<link rel="stylesheet" href="' . BASE_URL . '/assets/css/login.css">',
            'extraJS'  => '<script src="' . BASE_URL . '/assets/js/login.js"></script>',
            'modoRecuperacion' => true,
        ], 'auth');
    }

    public function enviarRecuperacion(): void
    {
        $email = $_POST['email'] ?? '';

        // TODO: Lógica de envío de correo de recuperación

        $this->json(['ok' => true, 'mensaje' => 'Si el correo existe, recibirás instrucciones.']);
    }
}
