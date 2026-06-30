<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthMiddleware;
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

    public function listar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $venta = new Venta();
        $ventas = $venta->obtenerTodos();
        $this->json(['data' => $ventas ?: []]);
    }

    public function buscarClientes(): void
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

    public function guardarClienteRapido(): void
    {
        if (!$this->verificarPermiso('ventas')) return;

        $cedula = (int) ($_POST['cedula'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');

        if ($cedula <= 0 || empty($nombre) || empty($apellido)) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula, nombre y apellido son obligatorios.']);
            return;
        }

        $venta = new Venta();
        $existe = $this->fetch("SELECT cedula FROM cliente WHERE cedula = ?", [$cedula]);
        if ($existe) {
            $this->json(['ok' => false, 'mensaje' => 'Ya existe un cliente con esa cédula.']);
            return;
        }

        $ok = $venta->crearClienteRapido([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
        ]);

        $this->json([
            'ok' => $ok,
            'mensaje' => $ok ? 'Cliente registrado correctamente.' : 'Error al registrar el cliente.',
        ]);
    }

    public function buscarProductos(): void
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

    public function tiposPago(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $venta = new Venta();
        $tipos = $venta->obtenerTiposPago();
        $this->json(['data' => $tipos ?: []]);
    }

    public function clienteCredito(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $cedula = (int) ($_POST['cedula'] ?? 0);
        if ($cedula <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula inválida.']);
            return;
        }
        $venta = new Venta();
        $credito = $venta->obtenerCreditoCliente($cedula);
        $this->json([
            'ok' => true,
            'saldo' => $credito ? (float) $credito['saldo_deudor_usd'] : 0,
        ]);
    }

    public function tasaBcv(): void
    {
        if (!$this->verificarPermiso('ventas')) return;
        $venta = new Venta();
        $tasa = $venta->obtenerTasaBcv();
        $this->json(['ok' => true, 'tasa' => $tasa]);
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
        $detalle = $venta->obtenerDetalleConPagos($id);
        $this->json(['ok' => !!$detalle, 'data' => $detalle]);
    }

    public function guardar(): void
    {
        if (!$this->verificarPermiso('ventas')) return;

        $cedulaCliente = (int) ($_POST['cedula_cliente'] ?? 0);
        $productosJson = $_POST['productos'] ?? '';
        $pagosJson = $_POST['pagos'] ?? '';

        if ($cedulaCliente <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Debe seleccionar un cliente.']);
            return;
        }

        if (empty($productosJson) || empty($pagosJson)) {
            $this->json(['ok' => false, 'mensaje' => 'Debe agregar productos y pagos.']);
            return;
        }

        $productos = json_decode($productosJson, true);
        $pagos = json_decode($pagosJson, true);

        if (!is_array($productos) || count($productos) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'La lista de productos está vacía.']);
            return;
        }

        if (!is_array($pagos) || count($pagos) === 0) {
            $this->json(['ok' => false, 'mensaje' => 'La lista de pagos está vacía.']);
            return;
        }

        $venta = new Venta();
        $usuario = AuthMiddleware::usuario();
        $cedulaUsuario = $usuario['cedula'] ?? 0;

        if ($cedulaUsuario <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'No se pudo identificar al usuario.']);
            return;
        }

        try {
            // Crear encabezado
            $idVenta = $venta->crearEncabezado([
                'cedula_cliente' => $cedulaCliente,
                'cedula_usuario' => $cedulaUsuario,
            ]);

            // Crear detalles (triggers descuentan stock y actualizan totales)
            foreach ($productos as $prod) {
                $idproducto = (int) ($prod['idproducto'] ?? 0);
                $cantidad = (float) ($prod['cantidad'] ?? 0);

                if ($idproducto <= 0 || $cantidad <= 0) {
                    throw new \Exception('Datos inválidos en un producto.');
                }

                $producto = $venta->obtenerProductoInfo($idproducto);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado.');
                }

                if ($cantidad > (float) $producto['stock']) {
                    throw new \Exception(
                        'Stock insuficiente para ' . $producto['codigo'] . '. Disponible: ' . number_format((float) $producto['stock'], 3)
                    );
                }

                $venta->crearDetalle([
                    'idVenta' => $idVenta,
                    'idproducto' => $idproducto,
                    'cantidad' => $cantidad,
                    'costo_unitario_usd' => 0,
                    'precio_unitario_usd' => 0,
                    'precio_unitario_ves' => 0,
                ]);
            }

            // Crear pagos (trigger registra crédito si aplica)
            foreach ($pagos as $pago) {
                $idtipo = (int) ($pago['idtipo_de_pagos'] ?? 0);
                $moneda = $pago['moneda'] ?? 'USD';
                $monto = (float) ($pago['monto_recibido'] ?? 0);
                $referencia = $pago['referencia'] ?? null;

                if ($idtipo <= 0 || $monto <= 0) {
                    throw new \Exception('Datos inválidos en un pago.');
                }

                $venta->crearPago([
                    'idVenta' => $idVenta,
                    'idtipo_de_pagos' => $idtipo,
                    'moneda' => $moneda,
                    'monto_recibido' => $monto,
                    'referencia' => $referencia,
                ]);
            }

            $this->json([
                'ok' => true,
                'mensaje' => 'Venta registrada correctamente. ID: ' . $idVenta,
                'idVenta' => $idVenta,
            ]);
        } catch (\Exception $e) {
            $this->json(['ok' => false, 'mensaje' => 'Error al registrar la venta: ' . $e->getMessage()]);
        }
    }
}
