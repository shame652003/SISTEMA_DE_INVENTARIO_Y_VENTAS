<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entrada;

class EntradaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('entradas')) return;

        $entrada = new Entrada();
        $tasa = $entrada->obtenerTasaActual();
        $margenUsd = $entrada->obtenerMargenActual('USD');
        $margenVes = $entrada->obtenerMargenActual('VES');

        $this->render('entradas/entradas', array_merge([
            'titulo' => 'Entradas de Productos',
            'seccion' => 'entradas',
            'tasa' => $tasa,
            'margenUsd' => $margenUsd,
            'margenVes' => $margenVes,
        ], $this->datosModulo('entradas', 'entradas.js')), 'app');
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        $entrada = new Entrada();
        $entradas = $entrada->obtenerTodos();
        $this->json(['data' => $entradas ?: []]);
    }

    public function buscarProductos(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }
        $entrada = new Entrada();
        $productos = $entrada->buscarProductos($q);
        $this->json(['results' => $productos ?: []]);
    }

    public function productoInfo(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $entrada = new Entrada();
        $producto = $entrada->obtenerProductoInfo($id);
        $this->json(['ok' => !!$producto, 'data' => $producto]);
    }

    public function configPrecios(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        $entrada = new Entrada();
        $tasa = $entrada->obtenerTasaActual();
        $margenUsd = $entrada->obtenerMargenActual('USD');
        $margenVes = $entrada->obtenerMargenActual('VES');

        $this->json([
            'ok' => true,
            'tasa' => $tasa,
            'margenUsd' => $margenUsd,
            'margenVes' => $margenVes,
        ]);
    }

    public function detalle(): void
    {
        if (!$this->verificarPermiso('entradas')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $entrada = new Entrada();
        $encabezado = $entrada->obtenerEncabezado($id);
        if (!$encabezado) {
            $this->json(['ok' => false, 'mensaje' => 'Entrada no encontrada.']);
            return;
        }
        $detalles = $entrada->obtenerDetalle($id);
        $encabezado['detalles'] = $detalles;
        $this->json(['ok' => true, 'data' => $encabezado]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('entradas')) return;

        $descripcion = trim($_POST['descripcion'] ?? '');
        $itemsJson = $_POST['items'] ?? '';

        if (empty($itemsJson)) {
            $this->json(['ok' => false, 'mensaje' => 'No hay productos en la lista.']);
            return;
        }

        $items = json_decode($itemsJson, true);
        if (!is_array($items) || count($items) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'La lista de productos está vacía.']);
            return;
        }

        if (empty($descripcion)) {
            $descripcion = 'Entrada de ' . count($items) . ' producto(s) - ' . date('d/m/Y H:i');
        }

        $entrada = new Entrada();

        $idsVistos = [];
        foreach ($items as $i => $item) {
            $idproducto = (int) ($item['idproducto'] ?? 0);
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $costo = isset($item['costo']) ? (float) $item['costo'] : null;

            if ($idproducto <= 0 || $cantidad < 0) {
                $this->json(['ok' => false, 'mensaje' => 'Datos inválidos en el producto #' . ($i + 1) . '.']);
                return;
            }

            if (in_array($idproducto, $idsVistos)) {
                $this->json(['ok' => false, 'mensaje' => 'Producto ID ' . $idproducto . ' duplicado en la lista.']);
                return;
            }
            $idsVistos[] = $idproducto;

            if ($costo !== null && $costo < 0) {
                $this->json(['ok' => false, 'mensaje' => 'El costo del producto #' . ($i + 1) . ' no puede ser negativo.']);
                return;
            }

            $producto = $entrada->obtenerProductoInfo($idproducto);
            if (!$producto) {
                $this->json(['ok' => false, 'mensaje' => 'Producto ID ' . $idproducto . ' no encontrado o inactivo.']);
                return;
            }
        }

        try {
            $idEntrada = $entrada->crearEncabezado(['descripcion' => $descripcion]);

            foreach ($items as $item) {
                $idproducto = (int) ($item['idproducto'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $costo = isset($item['costo']) ? (float) $item['costo'] : null;

                $entrada->crearDetalle($idEntrada, $idproducto, $cantidad, $costo);
            }

            $this->json([
                'ok' => true,
                'mensaje' => 'Entrada registrada correctamente. Se procesaron ' . count($items) . ' producto(s).',
                'idEntrada' => $idEntrada,
            ]);
        } catch (\Exception $e) {
            $this->json(['ok' => false, 'mensaje' => 'Error al registrar la entrada: ' . $e->getMessage()]);
        }
    }
}
