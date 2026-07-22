<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;
use App\Models\Venta;

class ReportePagoController extends Controller
{
    public function index(): void
    {
        if (!$this->verificarPermisoVista('reportes_pagos')) return;
        $this->render('reportes_pagos/reportes_pagos', array_merge([
            'titulo' => 'Reportes de Pagos',
            'seccion' => 'reportes_pagos',
        ], $this->datosModulo('reportes_pagos', 'reportes.js')), 'app');
    }

    public function generar(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $inicio = trim($_POST['fecha_inicio'] ?? '');
        $fin = trim($_POST['fecha_fin'] ?? '');
        $metodo = trim($_POST['metodo_pago'] ?? '');
        $cedulaCliente = (int) ($_POST['cedula_cliente'] ?? 0);

        $reporte = new Reporte();
        $pagos = $reporte->pagosDetalle($inicio, $fin, $metodo, $cedulaCliente);
        $this->json(['ok' => true, 'data' => $pagos ?: []]);
    }

    public function creditos(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $reporte = new Reporte();
        $creditos = $reporte->creditosPendientes();
        $this->json(['ok' => true, 'data' => $creditos ?: []]);
    }

    public function clienteDetalle(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $cedula = (int) ($_POST['cedula'] ?? 0);
        if ($cedula <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula inválida.']);
            return;
        }

        $reporte = new Reporte();
        $pagos = $reporte->pagosPorCliente($cedula);
        $this->json(['ok' => true, 'data' => $pagos ?: []]);
    }

    public function ventaDetalle(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID de venta inválido.']);
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

    public function tipoPagos(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $venta = new Venta();
        $tipos = $venta->obtenerTiposPago();
        $this->json(['ok' => true, 'data' => $tipos ?: []]);
    }

    public function clientesBuscar(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $q = trim($_POST['q'] ?? '');
        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }
        $venta = new Venta();
        $clientes = $venta->buscarClientes($q);
        $this->json(['results' => $clientes ?: []]);
    }

    public function creditosDetalle(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $cedula = (int) ($_POST['cedula'] ?? 0);
        if ($cedula <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula inválida.']);
            return;
        }

        $reporte = new Reporte();
        $pendientes = $reporte->creditosDetallePendientes($cedula);
        $this->json(['ok' => true, 'data' => $pendientes ?: []]);
    }

    public function obtenerSaldoCredito(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $cedula = (int) ($_POST['cedula'] ?? 0);
        if ($cedula <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Cédula inválida.']);
            return;
        }

        $reporte = new Reporte();
        $saldo = $reporte->obtenerSaldoCredito($cedula);
        if (!$saldo) {
            $this->json(['ok' => false, 'mensaje' => 'El cliente no tiene créditos pendientes.']);
            return;
        }
        $this->json(['ok' => true, 'data' => $saldo]);
    }

    public function abonarCredito(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $cedula = (int) ($_POST['cedula_cliente'] ?? 0);
        $idTipoPago = (int) ($_POST['idtipo_de_pagos'] ?? 0);
        $moneda = strtoupper($_POST['moneda'] ?? 'USD');
        $montoRecibido = (float) ($_POST['monto_recibido'] ?? 0);
        $montoBcv = (float) ($_POST['monto_bcv'] ?? 0);
        $referencia = trim($_POST['referencia'] ?? '');

        if ($cedula <= 0 || $idTipoPago <= 0 || $montoRecibido <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'Todos los campos obligatorios deben ser completados.']);
            return;
        }

        if (!in_array($moneda, ['USD', 'VES'])) {
            $this->json(['ok' => false, 'mensaje' => 'Moneda no válida.']);
            return;
        }

        $reporte = new Reporte();
        $tipoPago = $reporte->obtenerTipoPago($idTipoPago);

        if (!$tipoPago || $tipoPago['tipoPago'] === 'Credito') {
            $this->json(['ok' => false, 'mensaje' => 'Método de pago no válido.']);
            return;
        }

        $resultado = $reporte->abonarCredito($cedula, [
            'idtipo_de_pagos' => $idTipoPago,
            'moneda' => $moneda,
            'monto_recibido' => $montoRecibido,
            'monto_bcv' => $montoBcv,
            'referencia' => $referencia !== '' ? $referencia : null,
        ]);

        $this->json($resultado);
    }

    public function fechasVentasDelMes(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $reporte = new Reporte();
        $fechas = $reporte->fechasVentasDelMes();

        $nombres = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                     'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        $results = [];
        foreach ($fechas as $f) {
            $partes = explode('-', $f['fecha']);
            $dia = (int)$partes[2];
            $mes = (int)$partes[1];
            $anio = $partes[0];
            $nombre = $dia . ' de ' . $nombres[$mes] . ' de ' . $anio;
            $results[] = [
                'id' => $f['fecha'],
                'text' => $nombre
            ];
        }

        $this->json(['results' => $results]);
    }

    public function abonoDetalle(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID invalido.']);
            return;
        }

        $reporte = new Reporte();
        $data = $reporte->detalleAbono($id);

        if (!$data['encontrado']) {
            $this->json(['ok' => false, 'mensaje' => 'Abono no encontrado.']);
            return;
        }
        $this->json(['ok' => true, 'data' => $data]);
    }
}
