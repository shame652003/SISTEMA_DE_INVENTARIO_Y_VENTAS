<?php

namespace App\Controllers;

use App\Core\Controller;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->render('usuarios/usuarios', [
            'titulo' => 'Gestión de Usuarios',
            'seccion' => 'usuarios',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/usuarios.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener usuarios de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        // TODO: Insertar o actualizar usuario
        $this->json(['ok' => true, 'mensaje' => 'Usuario guardado correctamente.']);
    }

    public function eliminar(): void
    {
        // TODO: Eliminar usuario
        $this->json(['ok' => true, 'mensaje' => 'Usuario eliminado correctamente.']);
    }
}
