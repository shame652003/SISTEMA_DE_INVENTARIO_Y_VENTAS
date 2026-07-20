<?php

namespace App\Models;

use App\Core\Model;

class Venta extends Model
{
    protected string $table = 'ventas_encabezado';
    protected string $detalleTable = 'ventas_detalle';

    public function obtenerTiposPago(): array
    {
        return $this->fetchAll("SELECT * FROM tipo_de_pagos WHERE status = 1 ORDER BY tipoPago");
    }

    public function obtenerTasaBcv(): float
    {
        $json = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial');
        if ($json) {
            $data = json_decode($json, true);
            $tasa = $data['promedio'] ?? 0;
            if ($tasa > 0) return (float) $tasa;
        }
        $tasa = $this->fetch(
            "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC LIMIT 1"
        );
        return $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
    }

    public function obtenerTasaActual(): ?array
    {
        return $this->fetch(
            "SELECT idTasa, tasa_ves_por_usd, fecha_tasa
             FROM bcv_tasas
             WHERE status = 1
             ORDER BY fecha_tasa DESC, idTasa DESC
             LIMIT 1"
        );
    }

    public function obtenerMargenActual(string $tipo): ?array
    {
        return $this->fetch(
            "SELECT idMargen, porcentaje, fecha_inicio
             FROM margen_ganancia
             WHERE tipo_precio = ? AND status = 1
             ORDER BY fecha_inicio DESC, idMargen DESC
             LIMIT 1",
            [$tipo]
        );
    }

    public function buscarClientes(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT c.cedula AS id,
                    CONCAT(c.cedula, ' - ', c.nombre, ' ', COALESCE(c.apellido, '')) AS text,
                    c.cedula, c.nombre, c.apellido, c.telefono, c.correo, c.direccion
             FROM cliente c
             WHERE c.status = 1
               AND (c.cedula LIKE ? OR CONCAT(c.nombre, ' ', COALESCE(c.apellido, '')) LIKE ?)
             ORDER BY c.nombre
             LIMIT 20",
            [$q, $q]
        );
    }

    public function obtenerClienteInfo(int $cedula): ?array
    {
        return $this->fetch(
            "SELECT c.*, e.tipo_equipo,
                    COALESCE(cr.saldo_deudor_usd, 0) AS saldo_deudor_usd,
                    COALESCE(cr.saldo_deudor_bcv, 0) AS saldo_deudor_bcv
             FROM cliente c
             LEFT JOIN equipos_cliente e ON e.idEquipoCliente = c.idEquipoCliente
             LEFT JOIN creditos cr ON cr.cedula_cliente = c.cedula
             WHERE c.cedula = ? AND c.status = 1",
            [$cedula]
        );
    }

    public function buscarProductos(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT p.idproducto AS id,
                    CONCAT(p.codigo, ' - ', p.nombre,
                           IF(p.marca IS NOT NULL AND p.marca != '', CONCAT(' [', p.marca, ']'), '')) AS text,
                    p.codigo, p.nombre, p.marca, p.stock, p.stock_minimo,
                    p.precio_venta_usd, p.precio_venta_ves, p.precio_costo_usd,
                    p.imgproducto,
                    tp.tipo AS tipo_producto
             FROM producto p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.status = 1
               AND (p.codigo LIKE ? OR p.nombre LIKE ?)
             ORDER BY p.nombre
             LIMIT 20",
            [$q, $q]
        );
    }

    public function obtenerEquiposCliente(): array
    {
        return $this->fetchAll(
            "SELECT idEquipoCliente, tipo_equipo
             FROM equipos_cliente
             WHERE status = 1
             ORDER BY tipo_equipo"
        );
    }

    public function registrarClienteRapido(array $datos): ?array
    {
        $existe = $this->fetch(
            "SELECT cedula FROM cliente WHERE cedula = ?",
            [$datos['cedula']]
        );
        if ($existe) return null;

        $this->execute(
            "INSERT INTO cliente (cedula, nombre, apellido, idEquipoCliente)
             VALUES (?, ?, ?, ?)",
            [
                $datos['cedula'],
                $datos['nombre'],
                $datos['apellido'],
                $datos['idEquipoCliente']
            ]
        );

        return $this->obtenerClienteInfo($datos['cedula']);
    }

    public function obtenerProductoInfo(int $id): ?array
    {
        return $this->fetch(
            "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.stock, p.stock_minimo,
                    p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves,
                    p.imgproducto,
                    tp.tipo AS tipo_producto
             FROM producto p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.idproducto = ? AND p.status = 1",
            [$id]
        );
    }

    public function crearVenta(array $datos): int
    {
        $this->execute(
            "INSERT INTO {$this->table} (cedula_cliente, cedula_usuario, idTasa, total_usd, total_ves)
             VALUES (?, ?, ?, ?, ?)",
            [
                $datos['cedula_cliente'],
                $datos['cedula_usuario'],
                $datos['idTasa'],
                $datos['total_usd'],
                $datos['total_ves']
            ]
        );
        return (int) $this->lastInsertId();
    }

    public function crearDetalle(int $idVenta, array $item): bool
    {
        return $this->execute(
            "INSERT INTO {$this->detalleTable}
                (idVenta, idproducto, cantidad,
                 costo_unitario_usd, precio_unitario_usd, precio_unitario_ves, precio_unitario_bcv,
                 subtotal_costo_usd, subtotal_usd, subtotal_ves, subtotal_bcv)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $idVenta,
                $item['idproducto'],
                $item['cantidad'],
                $item['costo_unitario_usd'] ?? 0,
                $item['precio_unitario_usd'] ?? 0,
                $item['precio_unitario_ves'] ?? 0,
                $item['precio_unitario_bcv'] ?? 0,
                $item['subtotal_costo_usd'] ?? 0,
                $item['subtotal_usd'] ?? 0,
                $item['subtotal_ves'] ?? 0,
                $item['subtotal_bcv'] ?? 0
            ]
        );
    }

    public function crearPago(int $idVenta, array $pago): bool
    {
        return $this->execute(
            "INSERT INTO pagos (idVenta, idtipo_de_pagos, moneda, monto_recibido, monto_bcv, referencia)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $idVenta,
                $pago['idtipo_de_pagos'],
                $pago['moneda'],
                $pago['monto_recibido'],
                $pago['monto_bcv'] ?? 0,
                $pago['referencia'] ?? null
            ]
        );
    }

    public function obtenerVentasTodas(): array
    {
        return $this->fetchAll(
            "SELECT v.idVenta, v.fecha, v.hora,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    v.total_usd, v.total_ves,
                    t.tasa_ves_por_usd,
                    COUNT(d.idDetalle) AS items,
                    SUM(d.cantidad) AS total_cantidad
             FROM {$this->table} v
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN bcv_tasas t ON t.idTasa = v.idTasa
             LEFT JOIN {$this->detalleTable} d ON d.idVenta = v.idVenta
             WHERE v.status = 1
             GROUP BY v.idVenta
             ORDER BY v.fecha DESC, v.hora DESC"
        );
    }

    public function obtenerDetalleVenta(int $idVenta): array
    {
        $encabezado = $this->fetch(
            "SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente,
                    t.tasa_ves_por_usd, t.fecha_tasa
             FROM {$this->table} v
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN bcv_tasas t ON t.idTasa = v.idTasa
             WHERE v.idVenta = ? AND v.status = 1",
            [$idVenta]
        );

        $detalles = $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre
             FROM {$this->detalleTable} d
             INNER JOIN producto p ON p.idproducto = d.idproducto
             WHERE d.idVenta = ?",
            [$idVenta]
        );

        $pagos = $this->fetchAll(
            "SELECT pg.*, tp.tipoPago
             FROM pagos pg
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = pg.idtipo_de_pagos
             WHERE pg.idVenta = ?",
            [$idVenta]
        );

        return [
            'encabezado' => $encabezado,
            'detalles' => $detalles ?: [],
            'pagos' => $pagos ?: [],
        ];
    }
}
