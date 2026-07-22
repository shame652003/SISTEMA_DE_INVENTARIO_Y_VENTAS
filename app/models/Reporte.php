<?php

namespace App\Models;

use App\Core\Model;

class Reporte extends Model
{
    public function resumenDashboard(): array
    {
        $row = $this->fetch("SELECT * FROM vw_resumen_dashboard");
        if ($row) {
            $row['total_bcv_hoy'] = $row['total_bcv_hoy'] ?? 0;
            $row['total_ves_hoy'] = $row['total_ves_hoy'] ?? 0;
            $row['creditos_pendientes_bcv'] = $row['creditos_pendientes_bcv'] ?? 0;
        }
        return $row;
    }

    public function ventasDiariasUltimos7Dias(): array
    {
        return $this->fetchAll(
            "SELECT fecha, cantidad_ventas, unidades_vendidas, venta_total_usd, venta_total_ves, ganancia_estimada_usd
             FROM vw_ventas_diarias
             WHERE fecha >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
             ORDER BY fecha ASC"
        );
    }

    public function ventasMensualesUltimoAnio(): array
    {
        return $this->fetchAll(
            "SELECT anio, mes, cantidad_ventas, unidades_vendidas, venta_total_usd, venta_total_ves, ganancia_estimada_usd
             FROM vw_ventas_mensuales
             WHERE (anio = YEAR(CURRENT_DATE) AND mes <= MONTH(CURRENT_DATE))
                OR (anio = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)) AND mes > MONTH(CURRENT_DATE))
             ORDER BY anio ASC, mes ASC
             LIMIT 12"
        );
    }

    public function pagosAgrupadosPorTipo(): array
    {
        return $this->fetchAll("SELECT * FROM vw_pagos_por_tipo ORDER BY cantidad_pagos DESC");
    }

    public function ventasPorTipoProducto(): array
    {
        return $this->fetchAll("SELECT * FROM vw_ventas_por_tipo_producto ORDER BY venta_total_usd DESC");
    }

    public function productosMasVendidosTop(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_productos_mas_vendidos LIMIT ?",
            [$limite]
        );
    }

    public function ultimasVentas(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT v.idVenta, v.fecha, v.hora, v.total_usd, v.total_ves, v.total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor
             FROM ventas_encabezado v
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN usuario u ON u.cedula = v.cedula_usuario
             WHERE v.status = 1
             ORDER BY v.fecha DESC, v.hora DESC, v.idVenta DESC
             LIMIT ?",
            [$limite]
        );
    }

    public function clientesMasFrecuentes(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_ventas_por_cliente
             ORDER BY cantidad_ventas DESC, venta_total_usd DESC
             LIMIT ?",
            [$limite]
        );
    }

    public function creditosPorAntiguedad(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT cd.idCreditoDetalle, cd.idVenta, cd.monto_credito_bcv, cd.saldo_pendiente_bcv,
                    cd.fecha_creacion, v.fecha AS fecha_venta,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    c.cedula
             FROM creditos_detalle cd
             INNER JOIN cliente c ON c.cedula = cd.cedula_cliente
             INNER JOIN ventas_encabezado v ON v.idVenta = cd.idVenta
             WHERE cd.saldo_pendiente_bcv > 0 AND cd.status = 1
             ORDER BY cd.fecha_creacion ASC
             LIMIT ?",
            [$limite]
        );
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

    public function creditosDetallePendientes(int $cedula): array
    {
        return $this->fetchAll(
            "SELECT cd.idCreditoDetalle, cd.idVenta, cd.monto_credito_usd, cd.monto_credito_bcv,
                    cd.saldo_pendiente_usd, cd.saldo_pendiente_bcv, cd.fecha_creacion,
                    v.fecha AS fecha_venta,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente
             FROM creditos_detalle cd
             INNER JOIN ventas_encabezado v ON v.idVenta = cd.idVenta
             INNER JOIN cliente c ON c.cedula = cd.cedula_cliente
             WHERE cd.cedula_cliente = ? AND cd.saldo_pendiente_bcv > 0 AND cd.status = 1
             ORDER BY cd.fecha_creacion ASC, cd.idCreditoDetalle ASC",
            [$cedula]
        );
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
        $pendientes = $this->fetchAll(
            "SELECT idCreditoDetalle, idVenta, monto_credito_usd, monto_credito_bcv,
                    saldo_pendiente_usd, saldo_pendiente_bcv
             FROM creditos_detalle
             WHERE cedula_cliente = ? AND saldo_pendiente_bcv > 0 AND status = 1
             ORDER BY fecha_creacion ASC, idCreditoDetalle ASC",
            [$cedula]
        );

        if (empty($pendientes)) {
            return ['ok' => false, 'mensaje' => 'El cliente no tiene créditos pendientes.'];
        }

        $moneda = strtoupper($datos['moneda']);
        $montoRecibido = (float) $datos['monto_recibido'];
        $montoBcv = (float) $datos['monto_bcv'];
        $idTipoPago = (int) $datos['idtipo_de_pagos'];
        $referencia = $datos['referencia'] ?? null;

        if ($montoRecibido <= 0) {
            return ['ok' => false, 'mensaje' => 'El monto debe ser mayor a cero.'];
        }

        $totalPendienteBcv = 0;
        $totalPendienteUsd = 0;
        foreach ($pendientes as $c) {
            $totalPendienteBcv += (float) $c['saldo_pendiente_bcv'];
            $totalPendienteUsd += (float) $c['saldo_pendiente_usd'];
        }

        if ($montoBcv > $totalPendienteBcv + 0.01) {
            return ['ok' => false, 'mensaje' => 'El monto supera el total pendiente ($' . number_format($totalPendienteBcv, 2) . ' BCV).'];
        }

        try {
            $this->execute(
                "INSERT INTO pagos (idVenta, cedula_cliente, idtipo_de_pagos, moneda, monto_recibido, monto_bcv, referencia)
                 VALUES (NULL, ?, ?, ?, ?, ?, ?)",
                [$cedula, $idTipoPago, $moneda, $montoRecibido, $montoBcv, $referencia !== '' ? $referencia : null]
            );

            $idPago = (int) $this->lastInsertId();

            $restanteBcv = $montoBcv;
            $ventasPagadas = [];

            foreach ($pendientes as $cred) {
                if ($restanteBcv <= 0.001) break;

                $saldoBcv = (float) $cred['saldo_pendiente_bcv'];
                $saldoUsd = (float) $cred['saldo_pendiente_usd'];
                $aplicadoBcv = min($restanteBcv, $saldoBcv);
                $ratio = $saldoBcv > 0 ? ($aplicadoBcv / $saldoBcv) : 0;
                $aplicadoUsd = round($saldoUsd * $ratio, 2);

                $this->execute(
                    "INSERT INTO abonos_aplicados (idPago, idCreditoDetalle, monto_aplicado_usd, monto_aplicado_bcv)
                     VALUES (?, ?, ?, ?)",
                    [$idPago, $cred['idCreditoDetalle'], $aplicadoUsd, $aplicadoBcv]
                );

                $nuevoSaldoUsd = max(0, round($saldoUsd - $aplicadoUsd, 2));
                $nuevoSaldoBcv = max(0, round($saldoBcv - $aplicadoBcv, 2));

                $this->execute(
                    "UPDATE creditos_detalle SET saldo_pendiente_usd = ?, saldo_pendiente_bcv = ?
                     WHERE idCreditoDetalle = ?",
                    [$nuevoSaldoUsd, $nuevoSaldoBcv, $cred['idCreditoDetalle']]
                );

                $restanteBcv -= $aplicadoBcv;

                if ($nuevoSaldoBcv < 0.01) {
                    $ventasPagadas[] = (int) $cred['idVenta'];
                }
            }

            $totales = $this->fetch(
                "SELECT COALESCE(SUM(saldo_pendiente_usd), 0) AS total_usd,
                        COALESCE(SUM(saldo_pendiente_bcv), 0) AS total_bcv
                 FROM creditos_detalle WHERE cedula_cliente = ? AND status = 1",
                [$cedula]
            );

            $nuevoTotalUsd = (float) $totales['total_usd'];
            $nuevoTotalBcv = (float) $totales['total_bcv'];

            if ($nuevoTotalBcv > 0) {
                $this->execute(
                    "UPDATE creditos SET saldo_deudor_usd = ?, saldo_deudor_bcv = ?, ultima_actualizacion = NOW()
                     WHERE cedula_cliente = ?",
                    [$nuevoTotalUsd, $nuevoTotalBcv, $cedula]
                );
            } else {
                $this->execute("DELETE FROM creditos WHERE cedula_cliente = ?", [$cedula]);
            }

            $mensaje = 'Abono #' . $idPago . ' registrado correctamente.';
            if (!empty($ventasPagadas)) {
                $mensaje .= ' Ventas liquidadas: #' . implode(', #', $ventasPagadas) . '.';
            }

            return [
                'ok' => true,
                'mensaje' => $mensaje,
                'saldo_deudor_usd' => $nuevoTotalUsd,
                'saldo_deudor_bcv' => $nuevoTotalBcv,
                'idPago' => $idPago,
                'ventas_pagadas' => $ventasPagadas,
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'mensaje' => 'Error al registrar el abono: ' . $e->getMessage()];
        }
    }

    public function pagosDetalle(string $inicio, string $fin, string $metodo = '', int $cedulaCliente = 0): array
    {
        $params = [];
        $where = '';
        if ($inicio !== '' && $fin !== '') {
            $where .= " AND DATE(p.fecha_pago) BETWEEN ? AND ?";
            $params[] = $inicio;
            $params[] = $fin;
        }

        $ventas = $this->fetchAll(
            "SELECT 'venta' AS tipo_fila, p.idVenta, v.fecha, v.hora,
                    v.total_usd, v.total_ves, v.total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor,
                    GROUP_CONCAT(DISTINCT tp.tipoPago ORDER BY tp.tipoPago SEPARATOR ', ') AS metodos_pago,
                    GROUP_CONCAT(DISTINCT p.moneda ORDER BY p.moneda SEPARATOR ', ') AS monedas,
                    COALESCE(
                        (SELECT CASE WHEN cd.saldo_pendiente_bcv > 0 THEN 0 ELSE 1 END
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), NULL
                    ) AS credito_pagado,
                    COALESCE(
                        (SELECT cd.saldo_pendiente_bcv
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), NULL
                    ) AS credito_pendiente_bcv,
                    COALESCE(
                        (SELECT cd.monto_credito_usd
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), 0
                    ) AS credito_monto_usd,
                    COALESCE(
                        (SELECT cd.monto_credito_bcv
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), 0
                    ) AS credito_monto_bcv,
                    COALESCE(
                        (SELECT SUM(pg.monto_recibido)
                         FROM pagos pg
                         INNER JOIN tipo_de_pagos tp2 ON tp2.idtipo_de_pagos = pg.idtipo_de_pagos
                         WHERE pg.idVenta = p.idVenta AND tp2.tipoPago = 'Credito' AND pg.moneda = 'VES'), 0
                    ) AS credito_monto_ves,
                    NULL AS monto_recibido, NULL AS referencia
             FROM pagos p
             INNER JOIN ventas_encabezado v ON v.idVenta = p.idVenta AND v.status = 1
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN usuario u ON u.cedula = v.cedula_usuario
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE 1=1" . $where . $this->buildFiltroVentas($metodo, $cedulaCliente, $params) . "
             GROUP BY p.idVenta",
            $params
        ) ?: [];

        $abonoParams = $params;
        $abonoWhere = str_replace('p.fecha_pago', 'p2.fecha_pago', $where);
        if ($metodo !== '') {
            $abonoWhere .= " AND tp2.tipoPago = ?";
            $abonoParams[] = $metodo;
        }
        if ($cedulaCliente > 0) {
            $abonoWhere .= " AND p2.cedula_cliente = ?";
            $abonoParams[] = $cedulaCliente;
        }

        $abonos = $this->fetchAll(
            "SELECT 'abono' AS tipo_fila, p2.idPago AS idVenta, DATE(p2.fecha_pago) AS fecha, TIME(p2.fecha_pago) AS hora,
                    0 AS total_usd, 0 AS total_ves, p2.monto_bcv AS total_bcv,
                    CONCAT(c2.nombre, ' ', c2.apellido) AS cliente, c2.cedula AS cedula_cliente,
                    'Sistema' AS vendedor,
                    tp2.tipoPago AS metodos_pago, p2.moneda AS monedas,
                    NULL AS credito_pagado, NULL AS credito_pendiente_bcv,
                    0 AS credito_monto_usd, 0 AS credito_monto_bcv, 0 AS credito_monto_ves,
                    p2.monto_recibido, p2.referencia
             FROM pagos p2
             INNER JOIN cliente c2 ON c2.cedula = p2.cedula_cliente
             INNER JOIN tipo_de_pagos tp2 ON tp2.idtipo_de_pagos = p2.idtipo_de_pagos
             WHERE p2.idVenta IS NULL" . $abonoWhere . "
             ORDER BY p2.fecha_pago DESC, p2.idPago DESC",
            $abonoParams
        ) ?: [];

        return array_merge($ventas, $abonos);
    }

    private function buildFiltroVentas(string $metodo, int $cedulaCliente, array &$params): string
    {
        $sql = '';
        if ($metodo !== '') {
            $sql .= " AND EXISTS (
                SELECT 1 FROM pagos p3
                INNER JOIN tipo_de_pagos tp3 ON tp3.idtipo_de_pagos = p3.idtipo_de_pagos
                WHERE p3.idVenta = p.idVenta AND tp3.tipoPago = ?
            )";
            $params[] = $metodo;
        }
        if ($cedulaCliente > 0) {
            $sql .= " AND v.cedula_cliente = ?";
            $params[] = $cedulaCliente;
        }
        return $sql;
    }

    public function creditosPendientes(): array
    {
        return $this->fetchAll("SELECT * FROM vw_creditos_pendientes ORDER BY saldo_deudor_bcv DESC");
    }

    public function detalleAbono(int $idPago): array
    {
        $abono = $this->fetch(
            "SELECT p.idPago, p.idtipo_de_pagos, p.moneda, p.monto_recibido, p.monto_bcv, p.referencia,
                    DATE(p.fecha_pago) AS fecha, TIME(p.fecha_pago) AS hora,
                    tp.tipoPago,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente
             FROM pagos p
             INNER JOIN cliente c ON c.cedula = p.cedula_cliente
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE p.idPago = ? AND p.idVenta IS NULL",
            [$idPago]
        );

        if (!$abono) return ['encontrado' => false];

        $aplicados = $this->fetchAll(
            "SELECT aa.monto_aplicado_usd, aa.monto_aplicado_bcv,
                    cd.idCreditoDetalle, cd.idVenta, cd.monto_credito_usd, cd.monto_credito_bcv,
                    cd.saldo_pendiente_usd, cd.saldo_pendiente_bcv, cd.fecha_creacion,
                    v.fecha AS fecha_venta,
                    CONCAT(cl.nombre, ' ', cl.apellido) AS cliente_venta
             FROM abonos_aplicados aa
             INNER JOIN creditos_detalle cd ON cd.idCreditoDetalle = aa.idCreditoDetalle
             INNER JOIN ventas_encabezado v ON v.idVenta = cd.idVenta
             INNER JOIN cliente cl ON cl.cedula = cd.cedula_cliente
             WHERE aa.idPago = ?
             ORDER BY aa.idAbonoAplicado ASC",
            [$idPago]
        );

        $tasa = $this->fetch(
            "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC, idTasa DESC LIMIT 1"
        );

        return [
            'encontrado' => true,
            'abono' => $abono,
            'aplicados' => $aplicados ?: [],
            'tasa_bcv' => $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0,
        ];
    }

    public function fechasVentasDelMes(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT fecha
             FROM ventas_encabezado
             WHERE status = 1
               AND YEAR(fecha) = YEAR(CURRENT_DATE)
               AND MONTH(fecha) = MONTH(CURRENT_DATE)
             ORDER BY fecha DESC"
        );
    }

    public function pagosPorCliente(int $cedula): array
    {
        $ventas = $this->fetchAll(
            "SELECT p.idVenta, v.fecha, v.hora,
                    v.total_usd, v.total_ves, v.total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor,
                    GROUP_CONCAT(DISTINCT tp.tipoPago ORDER BY tp.tipoPago SEPARATOR ', ') AS metodos_pago,
                    GROUP_CONCAT(DISTINCT p.moneda ORDER BY p.moneda SEPARATOR ', ') AS monedas,
                    COALESCE(
                        (SELECT CASE WHEN cd.saldo_pendiente_bcv > 0 THEN 0 ELSE 1 END
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), NULL
                    ) AS credito_pagado,
                    COALESCE(
                        (SELECT cd.saldo_pendiente_bcv
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), NULL
                    ) AS credito_pendiente_bcv,
                    COALESCE(
                        (SELECT cd.monto_credito_usd
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), 0
                    ) AS credito_monto_usd,
                    COALESCE(
                        (SELECT cd.monto_credito_bcv
                         FROM creditos_detalle cd
                         WHERE cd.idVenta = p.idVenta AND cd.status = 1
                         LIMIT 1), 0
                    ) AS credito_monto_bcv,
                    COALESCE(
                        (SELECT SUM(pg.monto_recibido)
                         FROM pagos pg
                         INNER JOIN tipo_de_pagos tp2 ON tp2.idtipo_de_pagos = pg.idtipo_de_pagos
                         WHERE pg.idVenta = p.idVenta AND tp2.tipoPago = 'Credito' AND pg.moneda = 'VES'), 0
                    ) AS credito_monto_ves
             FROM pagos p
             INNER JOIN ventas_encabezado v ON v.idVenta = p.idVenta AND v.status = 1
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN usuario u ON u.cedula = v.cedula_usuario
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE v.cedula_cliente = ?
             GROUP BY p.idVenta
             ORDER BY v.fecha DESC, p.idVenta DESC",
            [$cedula]
        );

        $abonos = $this->fetchAll(
            "SELECT p.idPago, p.idVenta, DATE(p.fecha_pago) AS fecha, TIME(p.fecha_pago) AS hora,
                    tp.tipoPago, p.moneda, p.monto_recibido, p.monto_bcv, p.referencia,
                    0 AS total_usd, 0 AS total_ves, 0 AS total_bcv,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente, c.cedula AS cedula_cliente,
                    'Sistema' AS vendedor
             FROM pagos p
             INNER JOIN cliente c ON c.cedula = p.cedula_cliente
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE p.idVenta IS NULL AND p.cedula_cliente = ?
             ORDER BY p.fecha_pago DESC, p.idPago DESC",
            [$cedula]
        );

        return array_merge($ventas ?: [], $abonos ?: []);
    }
}
