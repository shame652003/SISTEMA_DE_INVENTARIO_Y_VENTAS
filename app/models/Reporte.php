<?php

namespace App\Models;

use App\Core\Model;

class Reporte extends Model
{
    public function resumenDashboard(): array
    {
        return $this->fetch("SELECT * FROM vw_resumen_dashboard");
    }

    public function ventasPorPeriodo(string $inicio, string $fin): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_ventas_diarias WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC",
            [$inicio, $fin]
        );
    }

    public function pagosPorPeriodo(string $inicio, string $fin, string $metodo = ''): array
    {
        $sql = "SELECT * FROM vw_pagos_diarios_por_tipo WHERE fecha BETWEEN ? AND ?";
        $params = [$inicio, $fin];

        if ($metodo !== '') {
            $sql .= " AND tipoPago = ?";
            $params[] = $metodo;
        }

        $sql .= " ORDER BY fecha ASC";
        return $this->fetchAll($sql, $params);
    }

    public function movimientosInventario(string $inicio, string $fin): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_movimientos_inventario WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC, hora ASC",
            [$inicio, $fin]
        );
    }

    public function stockDisponible(): array
    {
        return $this->fetchAll("SELECT * FROM vw_stock_disponible ORDER BY nombre");
    }

    public function stockBajo(): array
    {
        return $this->fetchAll("SELECT * FROM vw_stock_bajo ORDER BY stock ASC");
    }

    public function productosMasVendidos(): array
    {
        return $this->fetchAll("SELECT * FROM vw_productos_mas_vendidos LIMIT 50");
    }

    public function obtenerSaldoCredito(int $cedula): ?array
    {
        $credito = $this->fetch(
            "SELECT cr.saldo_deudor_usd, cr.saldo_deudor_bcv, cr.ultima_actualizacion,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente
             FROM creditos cr
             INNER JOIN cliente c ON c.cedula = cr.cedula_cliente
             WHERE cr.cedula_cliente = ? AND (cr.saldo_deudor_usd > 0 OR cr.saldo_deudor_bcv > 0)",
            [$cedula]
        );

        if (!$credito) return null;

        $tasa = $this->fetch(
            "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC, idTasa DESC LIMIT 1"
        );

        $credito['tasa_bcv'] = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
        return $credito;
    }

    public function obtenerTiposPagoSinCredito(): array
    {
        return $this->fetchAll(
            "SELECT * FROM tipo_de_pagos WHERE status = 1 AND tipoPago <> 'Credito' ORDER BY tipoPago"
        );
    }

    public function obtenerTipoPago(int $id): ?array
    {
        return $this->fetch(
            "SELECT * FROM tipo_de_pagos WHERE idtipo_de_pagos = ? AND status = 1",
            [$id]
        );
    }

    public function abonarCredito(int $cedula, array $datos): array
    {
        $credito = $this->fetch(
            "SELECT saldo_deudor_usd, saldo_deudor_bcv FROM creditos WHERE cedula_cliente = ?",
            [$cedula]
        );

        if (!$credito) {
            return ['ok' => false, 'mensaje' => 'El cliente no tiene crédito registrado.'];
        }

        $saldoUsd = (float) $credito['saldo_deudor_usd'];
        $saldoBcv = (float) $credito['saldo_deudor_bcv'];
        $moneda = strtoupper($datos['moneda']);
        $montoRecibido = (float) $datos['monto_recibido'];
        $montoBcv = (float) $datos['monto_bcv'];
        $idTipoPago = (int) $datos['idtipo_de_pagos'];
        $referencia = $datos['referencia'] ?? null;

        if ($montoRecibido <= 0) {
            return ['ok' => false, 'mensaje' => 'El monto debe ser mayor a cero.'];
        }

        if ($moneda === 'USD' && $montoRecibido > $saldoUsd) {
            return ['ok' => false, 'mensaje' => 'El monto en USD supera el saldo deudor ($' . number_format($saldoUsd, 2) . ').'];
        }

        if ($moneda === 'VES' && $montoBcv > $saldoBcv) {
            return ['ok' => false, 'mensaje' => 'El monto en bolívares supera el saldo deudor BCV ($' . number_format($saldoBcv, 2) . ').'];
        }

        $nuevoSaldoUsd = max(0, round($saldoUsd - $montoBcv, 2));
        $nuevoSaldoBcv = max(0, round($saldoBcv - $montoBcv, 2));

        try {
            $this->execute(
                "INSERT INTO pagos (idVenta, idtipo_de_pagos, moneda, monto_recibido, monto_bcv, referencia)
                 VALUES (NULL, ?, ?, ?, ?, ?)",
                [$idTipoPago, $moneda, $montoRecibido, $montoBcv, $referencia]
            );

            $idPago = (int) $this->lastInsertId();

            $this->execute(
                "UPDATE creditos
                 SET saldo_deudor_usd = ?, saldo_deudor_bcv = ?, ultima_actualizacion = NOW()
                 WHERE cedula_cliente = ?",
                [$nuevoSaldoUsd, $nuevoSaldoBcv, $cedula]
            );

            return [
                'ok' => true,
                'mensaje' => 'Abono #' . $idPago . ' registrado correctamente.',
                'saldo_deudor_usd' => $nuevoSaldoUsd,
                'saldo_deudor_bcv' => $nuevoSaldoBcv,
                'idPago' => $idPago,
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'mensaje' => 'Error al registrar el abono: ' . $e->getMessage()];
        }
    }

    public function pagosDetalle(string $inicio, string $fin, string $metodo = '', int $cedulaCliente = 0): array
    {
        $sql = "SELECT p.idPago, p.idVenta, DATE(p.fecha_pago) AS fecha, TIME(p.fecha_pago) AS hora,
                       tp.tipoPago, p.moneda, p.monto_recibido, p.monto_bcv, p.referencia,
                       v.idVenta, CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                       CONCAT(u.nombre, ' ', u.apellido) AS vendedor,
                       v.total_usd, v.total_ves, v.total_bcv
                FROM pagos p
                INNER JOIN ventas_encabezado v ON v.idVenta = p.idVenta AND v.status = 1
                INNER JOIN cliente c ON c.cedula = v.cedula_cliente
                INNER JOIN usuario u ON u.cedula = v.cedula_usuario
                INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
                WHERE DATE(p.fecha_pago) BETWEEN ? AND ?";
        $params = [$inicio, $fin];

        if ($metodo !== '') {
            $sql .= " AND tp.tipoPago = ?";
            $params[] = $metodo;
        }

        if ($cedulaCliente > 0) {
            $sql .= " AND v.cedula_cliente = ?";
            $params[] = $cedulaCliente;
        }

        $sql .= " ORDER BY p.fecha_pago DESC, p.idPago DESC";
        return $this->fetchAll($sql, $params);
    }

    public function creditosPendientes(): array
    {
        return $this->fetchAll("SELECT * FROM vw_creditos_pendientes ORDER BY saldo_deudor_bcv DESC");
    }

    public function pagosPorCliente(int $cedula): array
    {
        $ventas = $this->fetchAll(
            "SELECT p.idPago, p.idVenta, DATE(p.fecha_pago) AS fecha, TIME(p.fecha_pago) AS hora,
                    tp.tipoPago, p.moneda, p.monto_recibido, p.monto_bcv, p.referencia,
                    v.total_usd, v.total_ves, v.total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor
             FROM pagos p
             INNER JOIN ventas_encabezado v ON v.idVenta = p.idVenta AND v.status = 1
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN usuario u ON u.cedula = v.cedula_usuario
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE v.cedula_cliente = ?
             ORDER BY p.fecha_pago DESC, p.idPago DESC",
            [$cedula]
        );

        $abonos = $this->fetchAll(
            "SELECT p.idPago, p.idVenta, DATE(p.fecha_pago) AS fecha, TIME(p.fecha_pago) AS hora,
                    tp.tipoPago, p.moneda, p.monto_recibido, p.monto_bcv, p.referencia,
                    0 AS total_usd, 0 AS total_ves, 0 AS total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                    'Sistema' AS vendedor
             FROM pagos p
             INNER JOIN creditos cr ON cr.cedula_cliente = ?
             INNER JOIN cliente c ON c.cedula = cr.cedula_cliente
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE p.idVenta IS NULL
             ORDER BY p.fecha_pago DESC, p.idPago DESC",
            [$cedula]
        );

        return array_merge($ventas ?: [], $abonos ?: []);
    }
}
