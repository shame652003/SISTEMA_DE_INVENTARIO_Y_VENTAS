<?php

namespace App\Controllers;

use App\Core\Controller;

class ProductoController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('productos')) return;
        $this->render('productos/productos', [
            'titulo' => 'Gestión de Productos',
            'seccion' => 'productos',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/productos.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        // TODO: Obtener productos de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        // TODO: Insertar o actualizar producto
        $this->json(['ok' => true, 'mensaje' => 'Producto guardado correctamente.']);
    }

    public function eliminar(): void
    {
        if (!$this->verificarPermiso('productos')) return;
        // TODO: Eliminar producto
        $this->json(['ok' => true, 'mensaje' => 'Producto eliminado correctamente.']);
    }
}
