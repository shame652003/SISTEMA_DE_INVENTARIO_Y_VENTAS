<?php

namespace App\Controllers;

use App\Core\Controller;

class ProductoController extends Controller
{
    public function index(): void
    {
        $this->render('productos/productos', [
            'titulo' => 'Gestión de Productos',
            'seccion' => 'productos',
            'extraJS' => '<script src="' . BASE_URL . '/assets/js/productos.js"></script>',
        ], 'app');
    }

    public function listar(): void
    {
        // TODO: Obtener productos de la BD
        $this->json(['data' => []]);
    }

    public function guardar(): void
    {
        // TODO: Insertar o actualizar producto
        $this->json(['ok' => true, 'mensaje' => 'Producto guardado correctamente.']);
    }

    public function eliminar(): void
    {
        // TODO: Eliminar producto
        $this->json(['ok' => true, 'mensaje' => 'Producto eliminado correctamente.']);
    }
}
