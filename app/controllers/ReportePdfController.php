<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\PdfGenerator;
use App\Models\Reporte;
use App\Models\Venta;

class ReportePdfController extends Controller
{
    public function historial(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $inicio = trim($_POST['fecha_inicio'] ?? '');
        $fin = trim($_POST['fecha_fin'] ?? '');
        $metodo = trim($_POST['metodo_pago'] ?? '');
        $cedulaCliente = (int) ($_POST['cedula_cliente'] ?? 0);

        $reporte = new Reporte();
        $ventas = $reporte->pagosDetalle($inicio, $fin, $metodo, $cedulaCliente);

        $filtros = [];
        if ($metodo !== '') $filtros[] = 'Metodo: ' . $metodo;
        if ($cedulaCliente > 0) $filtros[] = 'Cliente: #' . $cedulaCliente;

        $nombreArchivo = 'historial_pagos';
        if ($inicio !== '') {
            $nombreArchivo .= '_' . $this->fechaArchivo($inicio);
            if ($inicio !== $fin) {
                $nombreArchivo .= '_al_' . $this->fechaArchivo($fin);
            }
        }
        if ($metodo !== '') $nombreArchivo .= '_' . str_replace(' ', '_', $metodo);
        if ($cedulaCliente > 0) $nombreArchivo .= '_cliente_' . $cedulaCliente;
        $nombreArchivo .= '.pdf';

        $pdf = new PdfGenerator();
        $html = $this->buildHistorialHtml($ventas ?: [], $inicio, $fin, $filtros);
        $pdf->generar($html, $nombreArchivo);
    }

    public function creditos(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $reporte = new Reporte();
        $creditos = $reporte->creditosPendientes();

        $pdf = new PdfGenerator();
        $html = $this->buildCreditosHtml($creditos ?: []);
        $pdf->generar($html, 'creditos_pendientes_' . date('Y-m-d') . '.pdf');
    }

    public function venta(): void
    {
        if (!$this->verificarPermiso('reportes_pagos')) return;

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'ID de venta invalido.']);
            return;
        }

        $venta = new Venta();
        $data = $venta->obtenerDetalleVenta($id);
        if (!$data['encabezado']) {
            $this->json(['ok' => false, 'mensaje' => 'Venta no encontrada.']);
            return;
        }

        $cliente = preg_replace('/[^a-zA-Z0-9_]/', '_', $data['encabezado']['cliente']);
        $pdf = new PdfGenerator();
        $html = $this->buildVentaHtml($data);
        $pdf->generar($html, 'venta_' . $id . '_' . $cliente . '.pdf');
    }

    private function buildHistorialHtml(array $ventas, string $inicio, string $fin, array $filtros): string
    {
        $g = new PdfGenerator();
        $css = $g->cssComun();

        $totalVentas = count($ventas);
        $ventasFinalizadas = 0;
        $ventasConCredito = 0;
        $sumaUsd = 0;
        $sumaBcv = 0;
        $sumaVes = 0;
        $sumaPendienteUsd = 0;
        $sumaPendienteBcv = 0;
        $sumaPendienteVes = 0;

        $filas = '';
        foreach ($ventas as $v) {
            $monedasStr = $v['monedas'] ?? '';
            $esVes = strpos($monedasStr, 'VES') !== false;
            $esUsd = strpos($monedasStr, 'USD') !== false;
            $soloUsd = $esUsd && !$esVes;
            $creditoPendiente = ($v['credito_pagado'] === 0);
            $tieneCredito = ($v['credito_pagado'] !== null);

            if ($creditoPendiente) {
                $ventasConCredito++;
            } else {
                $ventasFinalizadas++;
            }

            if ($esVes && $esUsd) {
                $totalBcvCol = '$' . number_format($v['total_bcv'], 2);
                $totalVesCol = 'Bs. ' . number_format($v['total_ves'], 2);
            } elseif ($esVes) {
                $totalBcvCol = '$' . number_format($v['total_bcv'], 2);
                $creditoVes = (float) ($v['credito_monto_ves'] ?? 0);
                if ($tieneCredito && $creditoVes > 0) {
                    $pagadoVes = round((float) $v['total_ves'] - $creditoVes, 2);
                    $totalVesCol = 'Bs. ' . number_format($pagadoVes, 2) . ' pagado<br>'
                        . '<small>+ Bs. ' . number_format($creditoVes, 2) . ' credito ' . ($creditoPendiente ? 'pendiente' : 'pagado') . '</small><br>'
                        . '<small>= Bs. ' . number_format($v['total_ves'], 2) . '</small>';
                } else {
                    $totalVesCol = 'Bs. ' . number_format($v['total_ves'], 2);
                }
            } else {
                $totalBcvCol = '$' . number_format($v['total_usd'], 2);
                $totalVesCol = '-';
            }

            if ($creditoPendiente) {
                if ($soloUsd) {
                    $sumaPendienteUsd += (float) ($v['credito_monto_usd'] ?? 0);
                    $sumaUsd += (float) $v['total_usd'] - (float) ($v['credito_monto_usd'] ?? 0);
                } elseif ($esVes) {
                    $sumaPendienteBcv += (float) ($v['credito_monto_bcv'] ?? 0);
                    $sumaPendienteVes += (float) ($v['credito_monto_ves'] ?? 0);
                    $sumaBcv += (float) $v['total_bcv'] - (float) ($v['credito_monto_bcv'] ?? 0);
                    $sumaVes += round((float) $v['total_ves'] - (float) ($v['credito_monto_ves'] ?? 0), 2);
                }
            } else {
                if ($soloUsd) {
                    $sumaUsd += (float) $v['total_usd'];
                } elseif ($esVes) {
                    if ($v['credito_pagado'] === 1) {
                        $sumaBcv += (float) $v['total_bcv'];
                        $sumaVes += (float) $v['total_ves'];
                    } else {
                        $sumaBcv += (float) $v['total_bcv'] - (float) ($v['credito_monto_bcv'] ?? 0);
                        $sumaVes += round((float) $v['total_ves'] - (float) ($v['credito_monto_ves'] ?? 0), 2);
                    }
                }
            }

            $estado = $creditoPendiente ? '<span class="badge badge-danger">Pendiente</span>' : (($v['credito_pagado'] === 1) ? '<span class="badge badge-success">Pagado</span>' : '-');
            $filas .= '<tr>
                <td>' . $this->fechaEs($v['fecha']) . '</td>
                <td>' . htmlspecialchars($v['cliente']) . '</td>
                <td>' . $v['metodos_pago'] . '</td>
                <td class="right">' . $totalBcvCol . '</td>
                <td class="right">' . $totalVesCol . '</td>
                <td>' . $estado . '</td>
                <td>' . htmlspecialchars($v['vendedor']) . '</td>
            </tr>';
        }

        $filtrosStr = empty($filtros) ? '' : ' | ' . implode(' | ', $filtros);
        $ahora = new \DateTime('now', new \DateTimeZone('America/Caracas'));

        $infoExtra = '<span>Total ventas: <strong>' . $totalVentas . '</strong></span>';
        $infoExtra .= '<span>Ventas finalizadas: <strong>' . $ventasFinalizadas . '</strong></span>';
        if ($ventasConCredito > 0) {
            $infoExtra .= '<span>Con credito pendiente: <strong>' . $ventasConCredito . '</strong></span>';
        }
        if ($sumaUsd > 0) {
            $infoExtra .= '<span>Total USD: <strong>$' . number_format($sumaUsd, 2) . '</strong></span>';
        }
        if ($sumaBcv > 0) {
            $infoExtra .= '<span>Total BCV: <strong>$' . number_format($sumaBcv, 2) . '</strong></span>';
        }
        if ($sumaVes > 0) {
            $infoExtra .= '<span>Total VES: <strong>Bs. ' . number_format($sumaVes, 2) . '</strong></span>';
        }
        $infoExtra .= '<span>Generado: ' . $this->fechaEs($ahora->format('Y-m-d')) . ' ' . $ahora->format('h:i A') . '</span>';

        $pendienteRow = '';
        $pendienteHtml = [];
        if ($sumaPendienteUsd > 0) $pendienteHtml[] = '<span style="margin-left:16px;">- <strong>$' . number_format($sumaPendienteUsd, 2) . ' USD</strong></span>';
        if ($sumaPendienteBcv > 0) $pendienteHtml[] = '<span style="margin-left:16px;">- <strong>$' . number_format($sumaPendienteBcv, 2) . ' BCV</strong></span>';
        if ($sumaPendienteVes > 0) $pendienteHtml[] = '<span style="margin-left:16px;">- <strong>Bs. ' . number_format($sumaPendienteVes, 2) . ' VES</strong></span>';
        if (!empty($pendienteHtml)) {
            $pendienteRow = '<div class="summary" style="margin-bottom:8px;background:#fff0f0;border-left-color:#dc2626;">Creditos pendientes no cobrados:<br>' . implode('<br>', $pendienteHtml) . '</div>';
        }

        $totalGenUsd = $sumaUsd + $sumaPendienteUsd;
        $totalGenBcv = $sumaBcv + $sumaPendienteBcv;
        $totalGenVes = $sumaVes + $sumaPendienteVes;

        $generalHtml = [];
        if ($totalGenUsd > 0) $generalHtml[] = '<span style="margin-left:16px;">- <strong>$' . number_format($totalGenUsd, 2) . ' USD</strong> (cobrado: $' . number_format($sumaUsd, 2) . ' + pendiente: $' . number_format($sumaPendienteUsd, 2) . ')</span>';
        if ($totalGenBcv > 0) $generalHtml[] = '<span style="margin-left:16px;">- <strong>$' . number_format($totalGenBcv, 2) . ' BCV</strong> (cobrado: $' . number_format($sumaBcv, 2) . ' + pendiente: $' . number_format($sumaPendienteBcv, 2) . ')</span>';
        if ($totalGenVes > 0) $generalHtml[] = '<span style="margin-left:16px;">- <strong>Bs. ' . number_format($totalGenVes, 2) . ' VES</strong> (cobrado: Bs. ' . number_format($sumaVes, 2) . ' + pendiente: Bs. ' . number_format($sumaPendienteVes, 2) . ')</span>';

        $generalRow = '';
        if (!empty($generalHtml)) {
            $generalRow = '<div class="summary" style="margin-bottom:12px;">Total General (cobrado + pendiente):<br>' . implode('<br>', $generalHtml) . '</div>';
        }

        $fechaHeader = 'Todas las fechas';
        if ($inicio !== '') {
            $fechaHeader = $this->fechaEs($inicio);
            if ($fin !== '' && $inicio !== $fin) {
                $fechaHeader .= ' - ' . $this->fechaEs($fin);
            }
        }
        $fechaHeader .= $filtrosStr;

        return '<!DOCTYPE html><html><head><meta charset="utf-8">' . $css . '</head><body>
            <div class="header">
                <h1>Historial de Pagos</h1>
                <p>' . $fechaHeader . '</p>
            </div>
            <div class="info-row">' . $infoExtra . '</div>
            ' . $pendienteRow . '
            ' . $generalRow . '
            <table>
                <thead><tr>
                    <th>Fecha</th><th>Cliente</th><th>Metodos</th><th class="right">Total BCV</th><th class="right">Total VES</th><th>Estado</th><th>Vendedor</th>
                </tr></thead>
                <tbody>' . $filas . '</tbody>
            </table>
            <div class="footer">Sistema de Ventas e Inventarios &copy; ' . date('Y') . ' - Pagina generada automaticamente</div>
        </body></html>';
    }

    private function buildCreditosHtml(array $creditos): string
    {
        $g = new PdfGenerator();
        $css = $g->cssComun();

        $totalUsd = array_sum(array_column($creditos, 'saldo_deudor_usd'));
        $totalBcv = array_sum(array_column($creditos, 'saldo_deudor_bcv'));

        $filas = '';
        foreach ($creditos as $c) {
            $filas .= '<tr>
                <td style="font-weight:bold;">' . $c['cedula'] . '</td>
                <td>' . htmlspecialchars($c['cliente']) . '</td>
                <td class="right">$' . number_format($c['saldo_deudor_usd'], 2) . '</td>
                <td class="right">$' . number_format($c['saldo_deudor_bcv'], 2) . '</td>
                <td>' . $c['ultima_actualizacion'] . '</td>
            </tr>';
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8">' . $css . '</head><body>
            <div class="header">
                <h1>Creditos Pendientes</h1>
                <p>Reporte generado el ' . $this->fechaEs(date('Y-m-d')) . '</p>
            </div>
            <div class="info-row">
                <span>Clientes con deuda: <strong>' . count($creditos) . '</strong></span>
                <span>Total USD: <strong>$' . number_format($totalUsd, 2) . '</strong></span>
                <span>Total BCV: <strong>$' . number_format($totalBcv, 2) . '</strong></span>
            </div>
            <table>
                <thead><tr>
                    <th>Cedula</th><th>Cliente</th><th class="right">Deuda USD</th><th class="right">Deuda BCV</th><th>Ultima Actualizacion</th>
                </tr></thead>
                <tbody>' . $filas . '</tbody>
            </table>
            <div class="summary">Total general: <strong>$' . number_format($totalBcv, 2) . ' BCV</strong> en ' . count($creditos) . ' creditos pendientes</div>
            <div class="footer">Sistema de Ventas e Inventarios &copy; ' . date('Y') . ' - Pagina generada automaticamente</div>
        </body></html>';
    }

    private function buildVentaHtml(array $data): string
    {
        $g = new PdfGenerator();
        $css = $g->cssComun();

        $enc = $data['encabezado'];
        $detalles = $data['detalles'] ?: [];
        $pagos = $data['pagos'] ?: [];

        $prodFilas = '';
        foreach ($detalles as $d) {
            $prodFilas .= '<tr>
                <td>' . $d['codigo'] . '</td>
                <td>' . htmlspecialchars($d['producto_nombre']) . '</td>
                <td class="center">' . number_format($d['cantidad'], 0) . '</td>
                <td class="right">$' . number_format($d['precio_unitario_usd'], 2) . '</td>
                <td class="right">Bs. ' . number_format($d['precio_unitario_ves'], 2) . '</td>
                <td class="right">$' . number_format($d['subtotal_usd'], 2) . '</td>
                <td class="right">Bs. ' . number_format($d['subtotal_ves'], 2) . '</td>
            </tr>';
        }

        $pagoFilas = '';
        foreach ($pagos as $pg) {
            $pagoFilas .= '<tr>
                <td>' . $pg['tipoPago'] . '</td>
                <td>' . $pg['moneda'] . '</td>
                <td class="right">' . ($pg['moneda'] === 'VES' ? 'Bs. ' : '$') . number_format($pg['monto_recibido'], 2) . '</td>
                <td>' . ($pg['referencia'] ?: '-') . '</td>
            </tr>';
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8">' . $css . '</head><body>
            <div class="header">
                <h1>Comprobante de Venta #' . $enc['idVenta'] . '</h1>
                <p>' . $this->fechaEs($enc['fecha']) . ' - ' . $enc['hora'] . ' &nbsp;|&nbsp; Cliente: ' . htmlspecialchars($enc['cliente']) . ' &nbsp;|&nbsp; Tasa BCV: ' . $enc['tasa_ves_por_usd'] . '</p>
            </div>
            <div class="info-row">
                <span>Total USD: <strong>$' . number_format($enc['total_usd'], 2) . '</strong></span>
                <span>Total VES: <strong>Bs. ' . number_format($enc['total_ves'], 2) . '</strong></span>
                <span>Total BCV: <strong>$' . number_format($enc['total_bcv'], 2) . '</strong></span>
            </div>
            <div class="section-title">Productos</div>
            <table>
                <thead><tr>
                    <th>Codigo</th><th>Producto</th><th class="center">Cant</th><th class="right">P.U. USD</th><th class="right">P.U. VES</th><th class="right">Sub. USD</th><th class="right">Sub. VES</th>
                </tr></thead>
                <tbody>' . $prodFilas . '</tbody>
            </table>
            <div class="section-title">Pagos recibidos</div>
            <table>
                <thead><tr>
                    <th>Tipo</th><th>Moneda</th><th class="right">Monto</th><th>Referencia</th>
                </tr></thead>
                <tbody>' . $pagoFilas . '</tbody>
            </table>
            <div class="footer">Sistema de Ventas e Inventarios &copy; ' . date('Y') . ' - Comprobante generado el ' . $this->fechaEs(date('Y-m-d')) . '</div>
        </body></html>';
    }

    private function fechaArchivo(string $fecha): string
    {
        if (empty($fecha) || $fecha === '0000-00-00') return $fecha;
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $parts = explode('-', $fecha);
        if (count($parts) !== 3) return $fecha;
        return (int) $parts[2] . '_de_' . $meses[(int) $parts[1]] . '_' . $parts[0];
    }

    private function fechaEs(string $fecha): string
    {
        if (empty($fecha) || $fecha === '0000-00-00') return '-';
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $parts = explode('-', $fecha);
        if (count($parts) !== 3) return $fecha;
        $dia = (int) $parts[2];
        $mes = (int) $parts[1];
        $anio = $parts[0];
        return $dia . ' de ' . $meses[$mes] . ' de ' . $anio;
    }
}
