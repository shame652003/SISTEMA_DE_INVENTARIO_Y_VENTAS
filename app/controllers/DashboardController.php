<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Reporte;
use App\Models\Configuracion;

class DashboardController extends Controller
{
    public function index(): void
    {
        $reporte = new Reporte();
        $config = new Configuracion();

        $resumen = $reporte->resumenDashboard();
        $tasaBcv = $config->obtenerTasaBcv();
        $margenUsd = $config->obtenerMargen('USD');
        $margenVes = $config->obtenerMargen('VES');

        $this->render('dashboard/dashboard', array_merge([
            'titulo' => 'Dashboard',
            'seccion' => 'dashboard',
            'resumen' => $resumen ?: [],
            'tasaBcv' => $tasaBcv ?: [],
            'margenUsd' => $margenUsd ?: [],
            'margenVes' => $margenVes ?: [],
        ], $this->datosModulo('dashboard', 'dashboard.js')), 'app');
    }

    public function actualizarBcv(): void
    {
        $this->verificarPermiso('dashboard');

        $tasa = (float) ($_POST['tasa'] ?? 0);
        $fecha = $_POST['fecha'] ?? '';
        $observacion = $_POST['observacion'] ?? '';

        if ($tasa <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'La tasa debe ser mayor a 0.']);
            return;
        }

        if ($fecha === '') {
            $this->json(['ok' => false, 'mensaje' => 'La fecha es requerida.']);
            return;
        }

        $config = new Configuracion();
        $resultado = $config->guardarTasaBcv($tasa, $fecha, $observacion);

        if ($resultado['ok']) {
            $nuevaTasa = $config->obtenerTasaBcv();
            $resultado['tasa'] = $nuevaTasa;
        }

        $this->json($resultado);
    }

    public function consultarBcvApi(): void
    {
        $this->verificarPermiso('dashboard');

        $ch = curl_init('https://ve.dolarapi.com/v1/dolares/oficial');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            $this->json(['ok' => false, 'mensaje' => 'Error al consultar la API: ' . $error]);
            return;
        }

        if ($httpCode !== 200) {
            $this->json(['ok' => false, 'mensaje' => "API respondió con código HTTP {$httpCode}"]);
            return;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['ok' => false, 'mensaje' => 'Error al decodificar la respuesta de la API.']);
            return;
        }

        $tasa = (float) ($data['promedio'] ?? $data['precio'] ?? 0);
        $fecha = $data['fecha'] ?? date('Y-m-d');

        if ($tasa <= 0) {
            $this->json(['ok' => false, 'mensaje' => 'No se pudo obtener la tasa de la API.']);
            return;
        }

        $this->json([
            'ok' => true,
            'tasa' => $tasa,
            'fecha' => $fecha,
            'mensaje' => 'Tasa consultada exitosamente.',
        ]);
    }

    public function actualizarMargen(): void
    {
        $this->verificarPermiso('dashboard');

        $tipo = strtoupper($_POST['tipo'] ?? '');
        $porcentaje = (float) ($_POST['porcentaje'] ?? 0);
        $observacion = $_POST['observacion'] ?? '';

        if (!in_array($tipo, ['USD', 'VES'])) {
            $this->json(['ok' => false, 'mensaje' => 'Tipo de margen inválido.']);
            return;
        }

        if ($porcentaje < 0) {
            $this->json(['ok' => false, 'mensaje' => 'El porcentaje no puede ser negativo.']);
            return;
        }

        $config = new Configuracion();
        $resultado = $config->guardarMargen($tipo, $porcentaje, $observacion);

        if ($resultado['ok']) {
            $nuevoMargen = $config->obtenerMargen($tipo);
            $resultado['margen'] = $nuevoMargen;
        }

        $this->json($resultado);
    }

    public function graficas(): void
    {
        $this->verificarPermiso('dashboard');

        $tipo = $_POST['tipo'] ?? '';

        $reporte = new Reporte();

        switch ($tipo) {
            case 'ventas_diarias':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->ventasDiariasUltimos7Dias(),
                ]);
                break;

            case 'ventas_mensuales':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->ventasMensualesUltimoAnio(),
                ]);
                break;

            case 'productos_mas_vendidos':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->productosMasVendidosTop(10),
                ]);
                break;

            case 'pagos_por_tipo':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->pagosAgrupadosPorTipo(),
                ]);
                break;

            case 'ventas_por_tipo_producto':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->ventasPorTipoProducto(),
                ]);
                break;

            case 'clientes_mas_frecuentes':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->clientesMasFrecuentes(10),
                ]);
                break;

            case 'creditos_antiguos':
                $this->json([
                    'ok' => true,
                    'data' => $reporte->creditosPorAntiguedad(10),
                ]);
                break;

            default:
                $this->json(['ok' => false, 'mensaje' => 'Tipo de gráfica no especificado.']);
                break;
        }
    }

    public function ultimasVentas(): void
    {
        $this->verificarPermiso('dashboard');

        $reporte = new Reporte();
        $this->json([
            'ok' => true,
            'data' => $reporte->ultimasVentas(10),
        ]);
    }
}
