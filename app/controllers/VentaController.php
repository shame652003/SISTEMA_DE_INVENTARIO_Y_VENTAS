<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Venta;

class VentaController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('ventas')) return;
        $venta = new Venta();
        $this->render('ventas/ventas', array_merge([
            'titulo' => 'Ventas',
            'seccion' => 'ventas',
            'tiposPago' => $venta->obtenerTiposPago(),
        ], $this->datosModulo('ventas', 'ventas.js')), 'app');
    }

    public function configInicial(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $venta = new Venta();
        $tasa = $venta->obtenerTasaActual();
        $margenUsd = $venta->obtenerMargenActual('USD');
        $margenVes = $venta->obtenerMargenActual('VES');

        $this->json([
            'ok' => true,
            'tasa_bcv' => $venta->obtenerTasaBcv(),
            'tasa' => $tasa,
            'tiposPago' => $venta->obtenerTiposPago(),
            'equiposCliente' => $venta->obtenerEquiposCliente(),
            'margenUsd' => $margenUsd,
            'margenVes' => $margenVes,
        ]);
    }

    public function clientesBuscar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }
        $venta = new Venta();
        $clientes = $venta->buscarClientes($q);
        $this->json(['results' => $clientes ?: []]);
    }

    public function clienteInfo(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $cedula = (int) ($_POST['cedula'] ?? 0);
        if ($cedula <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula inválida.']);
            return;
        }
        $venta = new Venta();
        $cliente = $venta->obtenerClienteInfo($cedula);
        $this->json(['ok' => !!$cliente, 'data' => $cliente]);
    }

    public function clienteRegistrarRapido(): void
    {
        if (!$this->verificarPermiso('ventas')) return;

        $cedula = (int) ($_POST['cedula'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $idEquipoCliente = (int) ($_POST['idEquipoCliente'] ?? 0);

        if ($cedula <= 0 || $nombre === '' || $apellido === '' || $idEquipoCliente <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos son obligatorios.']);
            return;
        }

        $venta = new Venta();
        $cliente = $venta->registrarClienteRapido([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'idEquipoCliente' => $idEquipoCliente,
        ]);

        if (!$cliente) {
            $this->json(['ok' => false, 'mensaje' => 'Ya existe un cliente con esa cédula.']);
            return;
        }

        $this->json(['ok' => true, 'data' => $cliente, 'mensaje' => 'Cliente registrado correctamente.']);
    }

    public function productosBuscar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }
        $venta = new Venta();
        $productos = $venta->buscarProductos($q);
        $this->json(['results' => $productos ?: []]);
    }

    public function productoInfo(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $venta = new Venta();
        $producto = $venta->obtenerProductoInfo($id);
        $this->json(['ok' => !!$producto, 'data' => $producto]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;

        $usuario = $this->auth();
        $cedulaUsuario = (int) ($usuario['cedula'] ?? 0);
        if ($cedulaUsuario <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Usuario no autenticado.'], 401);
            return;
        }

        $cedulaCliente = (int) ($_POST['cedula_cliente'] ?? 0);
        $itemsJson = $_POST['items'] ?? '';
        $pagosJson = $_POST['pagos'] ?? '';
        $totalUsd = (float) ($_POST['total_usd'] ?? 0);
        $totalVes = (float) ($_POST['total_ves'] ?? 0);
        $idTasa = (int) ($_POST['idTasa'] ?? 0);

        if ($cedulaCliente <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Debe seleccionar un cliente.']);
            return;
        }

        $items = json_decode($itemsJson, true);
        if (!is_array($items) || count($items) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'El carrito está vacío.']);
            return;
        }

        $pagos = json_decode($pagosJson, true);
        if (!is_array($pagos) || count($pagos) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'Debe agregar al menos un método de pago.']);
            return;
        }

        $venta = new Venta();

        try {
            $idVenta = $venta->crearVenta([
                'cedula_cliente' => $cedulaCliente,
                'cedula_usuario' => $cedulaUsuario,
                'idTasa' => $idTasa,
                'total_usd' => 0,
                'total_ves' => 0,
            ]);

            foreach ($items as $item) {
                $idproducto = (int) ($item['idproducto'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 0);

                if ($idproducto <= 0 || $cantidad <= 0) {
                    throw new \Exception('Datos inválidos en un producto del carrito.');
                }

                $producto = $venta->obtenerProductoInfo($idproducto);
                if (!$producto) {
                    throw new \Exception('Producto ID ' . $idproducto . ' no encontrado.');
                }
                if ((float) $producto['stock'] < $cantidad) {
                    throw new \Exception('Stock insuficiente para "' . $producto['nombre'] . '". Disponible: ' . (float) $producto['stock']);
                }

                $venta->crearDetalle($idVenta, [
                    'idproducto' => $idproducto,
                    'cantidad' => $cantidad,
                    'costo_unitario_usd' => $item['costo_unitario_usd'] ?? 0,
                    'precio_unitario_usd' => $item['precio_unitario_usd'] ?? 0,
                    'precio_unitario_ves' => $item['precio_unitario_ves'] ?? 0,
                    'precio_unitario_bcv' => $item['precio_unitario_bcv'] ?? 0,
                    'subtotal_costo_usd' => 0,
                    'subtotal_usd' => 0,
                    'subtotal_ves' => 0,
                    'subtotal_bcv' => 0,
                ]);
            }

            foreach ($pagos as $pago) {
                $idTipoPago = (int) ($pago['idtipo_de_pagos'] ?? 0);
                $moneda = strtoupper($pago['moneda'] ?? 'USD');
                $monto = (float) ($pago['monto_recibido'] ?? 0);

                if ($idTipoPago <= 0 || $monto <= 0) {
                    throw new \Exception('Datos inválidos en un método de pago.');
                }
                if (!in_array($moneda, ['USD', 'VES'])) {
                    $moneda = 'USD';
                }

                $venta->crearPago($idVenta, [
                    'idtipo_de_pagos' => $idTipoPago,
                    'moneda' => $moneda,
                    'monto_recibido' => $monto,
                    'monto_bcv' => $pago['monto_bcv'] ?? 0,
                    'referencia' => $pago['referencia'] ?? null,
                ]);
            }

            $this->json([
                'ok' => true,
                'mensaje' => 'Venta #' . $idVenta . ' procesada correctamente.',
                'idVenta' => $idVenta,
            ]);
        } catch (\Exception $e) {
            $this->json(['ok' => false, 'mensaje' => 'Error al procesar la venta: ' . $e->getMessage()]);
        }
    }

    public function listar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $venta = new Venta();
        $ventas = $venta->obtenerVentasTodas();
        $this->json(['data' => $ventas ?: []]);
    }

    public function detalle(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID inválido.']);
            return;
        }
        $venta = new Venta();
        $data = $venta->obtenerDetalleVenta($id);
        if (!$data['encabezado']) {
            $this->json(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
            return;
        }
        $this->json(['ok' => true, 'data' => $data]);
    }
}
