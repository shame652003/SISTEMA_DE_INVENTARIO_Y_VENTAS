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
        $filtro = trim($_POST['filtro'] ?? 'todas');

        $reporte = new Reporte();
        $ventas = $reporte->pagosDetalle($inicio, $fin, $metodo, $cedulaCliente);

        // Aplicar filtro rápido
        if ($filtro === 'abonos') {
            $ventas = array_filter($ventas, fn($v) => ($v['tipo_fila'] ?? 'venta') === 'abono');
        } elseif ($filtro === 'creditos') {
            $ventas = array_filter($ventas, fn($v) => ($v['credito_pagado'] ?? null) !== null);
        } elseif ($filtro === 'finalizadas') {
            $ventas = array_filter($ventas, fn($v) => ($v['credito_pagado'] ?? 1) !== 0);
        }
        $ventas = array_values($ventas);

        // Marcar abonos del mismo día (no sumar a totales)
        $abonoIds = [];
        foreach ($ventas as $v) {
            if (($v['tipo_fila'] ?? 'venta') === 'abono') {
                $abonoIds[] = (int) $v['idVenta'];
            }
        }

        if (!empty($abonoIds)) {
            $fechas = (new Reporte())->obtenerFechasVentaAbonos($abonoIds);
            $mapa = [];
            foreach ($fechas as $f) {
                $mapa[(int)$f['idPago']] = $f['fecha_venta'];
            }
            foreach ($ventas as &$v) {
                if (($v['tipo_fila'] ?? 'venta') === 'abono') {
                    $idPago = (int) $v['idVenta'];
                    $fv = $mapa[$idPago] ?? null;
                    $v['mismo_dia'] = ($fv !== null && $fv === $v['fecha']);
                } else {
                    $v['mismo_dia'] = false;
                }
            }
            unset($v);
        }

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
        $html = $this->buildHistorialHtml($ventas ?: [], $inicio, $fin, $filtros, $filtro);
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

    private function buildHistorialHtml(array $ventas, string $inicio, string $fin, array $filtros, string $filtroActivo = 'todas'): string
    {
        $g = new PdfGenerator();
        $css = $g->cssComun();

        $filtroTitulo = ' — Todas las ventas';
        if ($filtroActivo === 'finalizadas') {
            $filtroTitulo = ' — Solo Finalizadas';
        } elseif ($filtroActivo === 'creditos') {
            $filtroTitulo = ' — Solo Créditos';
        } elseif ($filtroActivo === 'abonos') {
            $filtroTitulo = ' — Solo Abonos';
        }

        $totalVentas = count($ventas);
        $ventasFinalizadas = 0;
        $ventasConCredito = 0;
        $sumaUsd = 0;
        $sumaBcv = 0;
        $sumaVes = 0;
        $sumaPendienteUsd = 0;
        $sumaPendienteBcv = 0;
        $sumaPendienteVes = 0;

        $filasVentas = '';
        $filasAbonos = '';
        $countAbonos = 0;

        foreach ($ventas as $v) {
            $esAbono = ($v['tipo_fila'] ?? 'venta') === 'abono';

            if ($esAbono) {
                $countAbonos++;
                $mismoDia = !empty($v['mismo_dia']);
                $vesAbono = ($v['monedas'] ?? '') === 'VES' ? (float) ($v['monto_recibido'] ?? 0) : 0;
                $esAbonoUsd = ($v['monedas'] ?? '') === 'USD';

                $filasAbonos .= '<tr>
                    <td>' . $this->fechaEs($v['fecha']) . ($mismoDia ? ' <span style="font-size:7px;color:#999;">(mismo dia)</span>' : '') . '</td>
                    <td class="td-cliente">' . htmlspecialchars($v['cliente']) . '</td>
                    <td class="right">$' . number_format($v['total_bcv'], 2) . '</td>
                    <td class="right">' . ($vesAbono > 0 ? 'Bs. ' . number_format($vesAbono, 2) : ($esAbonoUsd ? '$' . number_format($v['monto_recibido'] ?? 0, 2) . ' USD' : '—')) . '</td>
                    <td class="td-metodo">' . $v['metodos_pago'] . '</td>
                </tr>';

                if (!$mismoDia) {
                    if ($esAbonoUsd) {
                        $sumaUsd += (float) ($v['total_bcv'] ?? 0);
                    } else {
                        $sumaBcv += (float) $v['total_bcv'];
                        $sumaVes += round($vesAbono, 2);
                    }
                }
                continue;
            }

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

            // Columnas simplificadas
            if ($soloUsd) {
                $totalBcvCol = '$' . number_format($v['total_usd'], 2) . ' <span style="font-size:7px;color:#999;">USD</span>';
                $pagadoVesCol = '—';
                $saldoVesCol = '—';
            } else {
                $totalBcvCol = '$' . number_format($v['total_bcv'], 2);
                $creditoVes = (float) ($v['credito_monto_ves'] ?? 0);
                $pagadoVes = round((float) $v['total_ves'] - $creditoVes, 2);
                $pagadoVesCol = 'Bs. ' . number_format($pagadoVes, 2);
                $saldoVesCol = ($creditoVes > 0) ? 'Bs. ' . number_format($creditoVes, 2) . ' <span class="badge badge-pendiente">Pendiente</span>' : '—';
            }

            $estado = $creditoPendiente
                ? '<span class="badge badge-pendiente">PENDIENTE</span>'
                : (($v['credito_pagado'] === 1)
                    ? '<span class="badge badge-pagado">PAGADO</span>'
                    : '<span class="badge badge-pagado">PAGADO</span>');

            $filasVentas .= '<tr>
                <td>' . $this->fechaEs($v['fecha']) . '</td>
                <td class="td-cliente">' . htmlspecialchars($v['cliente']) . '</td>
                <td class="td-metodo">' . $v['metodos_pago'] . '</td>
                <td class="right">' . $totalBcvCol . '</td>
                <td class="right">' . $pagadoVesCol . '</td>
                <td class="right">' . $saldoVesCol . '</td>
                <td>' . $estado . '</td>
                <td class="td-vendedor">' . htmlspecialchars($v['vendedor']) . '</td>
            </tr>';

            if ($creditoPendiente) {
                if ($soloUsd) {
                    $sumaPendienteUsd += (float) ($v['credito_pendiente_usd'] ?? 0);
                    $sumaUsd += (float) $v['total_usd'] - (float) ($v['credito_pendiente_usd'] ?? 0);
                } elseif ($esVes) {
                    $sumaPendienteBcv += (float) ($v['credito_pendiente_bcv'] ?? 0);
                    $sumaPendienteVes += (float) ($v['credito_monto_ves'] ?? 0);
                    $sumaBcv += (float) $v['total_bcv'] - (float) ($v['credito_pendiente_bcv'] ?? 0);
                    $sumaVes += round((float) $v['total_ves'] - (float) ($v['credito_monto_ves'] ?? 0), 2);
                }
            } else {
                if ($soloUsd) {
                    if ($v['credito_pagado'] === 1) {
                        $sumaUsd += (float) $v['total_usd'] - (float) ($v['credito_monto_usd'] ?? 0);
                    } else {
                        $sumaUsd += (float) $v['total_usd'];
                    }
                } elseif ($esVes) {
                    if ($v['credito_pagado'] === 1) {
                        $sumaBcv += (float) $v['total_bcv'] - (float) ($v['credito_monto_bcv'] ?? 0);
                        $sumaVes += round((float) $v['total_ves'] - (float) ($v['credito_monto_ves'] ?? 0), 2);
                    } else {
                        $sumaBcv += (float) $v['total_bcv'] - (float) ($v['credito_pendiente_bcv'] ?? 0);
                        $sumaVes += round((float) $v['total_ves'] - (float) ($v['credito_monto_ves'] ?? 0), 2);
                    }
                }
            }
        }

        $filtrosStr = empty($filtros) ? '' : ' | ' . implode(' | ', $filtros);
        $ahora = new \DateTime('now', new \DateTimeZone('America/Caracas'));
        $countVentas = $totalVentas - $countAbonos;

        $fechaHeader = ($inicio !== '') ? $this->fechaEs($inicio) : 'Todas las fechas';
        $prefijo = ($inicio !== '') ? 'Reporte del ' : 'Reporte: ';
        if ($inicio !== '' && $fin !== '' && $inicio !== $fin) {
            $fechaHeader .= ' — ' . $this->fechaEs($fin);
        }
        $fechaHeader .= $filtrosStr;

        // Dashboard cards
        $cards = '<table class="dash-table"><tr>
            <td><div class="dash-card orange">
                <div class="label">Resumen Operativo</div>
                <div class="value">' . $countVentas . ' ventas</div>
                <div class="sub">' . $ventasFinalizadas . ' finalizadas | ' . $ventasConCredito . ' con credito' . ($countAbonos > 0 ? ' | ' . $countAbonos . ' abonos' : '') . '</div>
            </div></td>
            <td><div class="dash-card green">
                <div class="label">Ingresos Reales (Cobrado)</div>
                <div class="value">$' . number_format($sumaBcv, 2) . ' BCV</div>
                <div class="ves-big">Bs. ' . number_format($sumaVes, 2) . ' VES</div>
                ' . ($sumaUsd > 0 ? '<div class="value">$' . number_format($sumaUsd, 2) . ' USD</div>' : '') . '
            </div></td>
            <td><div class="dash-card red">
                <div class="label">Cuentas por Cobrar</div>
                ' . ($sumaPendienteUsd > 0 ? '<div class="value" style="color:#b91c1c;">$' . number_format($sumaPendienteUsd, 2) . ' USD <span style="font-size:8px;color:#999;">(Divisas)</span></div>' : '') . '
                <div class="value">$' . number_format($sumaPendienteBcv, 2) . ' BCV</div>
                <div class="ves-big">Bs. ' . number_format($sumaPendienteVes, 2) . ' VES</div>
            </div></td>
        </tr></table>';

        $generado = '<div style="text-align:right;font-size:9px;color:#999;margin-bottom:10px;">Generado: ' . $this->fechaEs($ahora->format('Y-m-d')) . ' ' . $ahora->format('h:i A') . '</div>';

        // Tabla A: Ventas
        $tablaVentas = '';
        if ($countVentas > 0) {
            $tablaVentas = '<div class="section-title">Historial de Ventas</div>
            <table>
                <thead><tr>
                    <th>Fecha</th><th>Cliente</th><th>Metodos</th><th class="right">Total BCV</th><th class="right">Pagado VES</th><th class="right">Saldo Pend.</th><th>Estado</th><th>Vendedor</th>
                </tr></thead>
                <tbody>' . $filasVentas . '</tbody>
            </table>';
        }

        // Tabla B: Abonos
        $tablaAbonos = '';
        if ($countAbonos > 0) {
            $tablaAbonos = '<div class="section-title">Historial de Abonos / Cobranza</div>
            <table>
                <thead><tr>
                    <th>Fecha</th><th>Cliente</th><th class="right">Monto BCV</th><th class="right">Monto VES</th><th>Metodo</th>
                </tr></thead>
                <tbody>' . $filasAbonos . '</tbody>
            </table>';
        }

        return '<!DOCTYPE html><html><head><meta charset="utf-8">' . $css . '</head><body>
            <div class="header">
                <h1>Historial de Pagos' . $filtroTitulo . '</h1>
                <p>' . $prefijo . $fechaHeader . '</p>
            </div>
            ' . $cards . '
            ' . $generado . '
            ' . $tablaVentas . '
            ' . $tablaAbonos . '
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
