<?php

namespace App\Models;

use App\Core\Model;

class Venta extends Model
{
    protected string $table = 'ventas_encabezado';
    protected string $detalleTable = 'ventas_detalle';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                    CONCAT(u.nombre, ' ', u.apellido) AS vendedor_nombre
             FROM {$this->table} v
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             INNER JOIN usuario u ON u.cedula = v.cedula_usuario
             WHERE v.status = 1
             ORDER BY v.fecha DESC, v.hora DESC"
        );
    }

    public function obtenerDetalle(int $idVenta): array
    {
        return $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre
             FROM {$this->detalleTable} d
             INNER JOIN producto p ON p.idproducto = d.idproducto
             WHERE d.idVenta = ?",
            [$idVenta]
        );
    }

    public function crearEncabezado(array $datos): int
    {
        $this->execute(
            "INSERT INTO {$this->table} (cedula_cliente, cedula_usuario) VALUES (?, ?)",
            [$datos['cedula_cliente'], $datos['cedula_usuario']]
        );
        return (int) $this->lastInsertId();
    }

    public function crearDetalle(array $datos): bool
    {
        return $this->execute(
            "INSERT INTO {$this->detalleTable} (idVenta, idproducto, cantidad, costo_unitario_usd, precio_unitario_usd, precio_unitario_ves) VALUES (?, ?, ?, ?, ?, ?)",
            [
                $datos['idVenta'],
                $datos['idproducto'],
                $datos['cantidad'],
                $datos['costo_unitario_usd'] ?? 0,
                $datos['precio_unitario_usd'] ?? 0,
                $datos['precio_unitario_ves'] ?? 0,
            ]
        );
    }

    public function buscarClientes(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT c.cedula AS id, CONCAT(c.cedula, ' - ', c.nombre, ' ', c.apellido) AS text,
                    c.cedula, c.nombre, c.apellido, c.telefono, c.correo
             FROM cliente c
             WHERE c.status = 1
               AND (CAST(c.cedula AS CHAR) LIKE ? OR c.nombre LIKE ? OR c.apellido LIKE ?)
             ORDER BY c.apellido, c.nombre
             LIMIT 20",
            [$q, $q, $q]
        );
    }

    public function crearClienteRapido(array $datos): bool
    {
        return $this->execute(
            "INSERT INTO cliente (cedula, nombre, apellido, idEquipoCliente, status) VALUES (?, ?, ?, 1, 1)",
            [$datos['cedula'], $datos['nombre'], $datos['apellido']]
        );
    }

    public function buscarProductos(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT p.idproducto AS id, CONCAT(p.codigo, ' - ', p.nombre) AS text,
                    p.codigo, p.nombre, p.marca, p.stock, p.precio_costo_usd,
                    p.precio_venta_usd, p.precio_venta_ves, tp.tipo AS tipo_producto
             FROM producto p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.status = 1
               AND (p.codigo LIKE ? OR p.nombre LIKE ?)
             ORDER BY p.nombre
             LIMIT 20",
            [$q, $q]
        );
    }

    public function obtenerProductoInfo(int $id): ?array
    {
        $producto = $this->fetch(
            "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.stock,
                    p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves,
                    tp.tipo AS tipo_producto
             FROM producto p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.idproducto = ? AND p.status = 1",
            [$id]
        );

        if ($producto) {
            $tasa = $this->fetch(
                "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC, idTasa DESC LIMIT 1"
            );
            $tasaBcv = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
            $precioVentaVes = (float) ($producto['precio_venta_ves'] ?? 0);
            $producto['precio_bcv'] = $tasaBcv > 0 ? round($precioVentaVes / $tasaBcv, 2) : 0;
        }

        return $producto;
    }

    public function obtenerTiposPago(): array
    {
        return $this->fetchAll("SELECT * FROM tipo_de_pagos WHERE status = 1 ORDER BY tipoPago");
    }

    public function obtenerCreditoCliente(int $cedula): ?array
    {
        return $this->fetch(
            "SELECT * FROM creditos WHERE cedula_cliente = ?",
            [$cedula]
        );
    }

    public function obtenerTasaBcv(): float
    {
        // Intentar DolarAPI primero
        $json = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial');
        if ($json) {
            $data = json_decode($json, true);
            $tasa = $data['promedio'] ?? 0;
            if ($tasa > 0) return (float) $tasa;
        }
        // Fallback a base de datos
        $tasa = $this->fetch(
            "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC LIMIT 1"
        );
        return $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
    }

    public function crearPago(array $datos): bool
    {
        return $this->execute(
            "INSERT INTO pagos (idVenta, idtipo_de_pagos, moneda, monto_recibido, referencia) VALUES (?, ?, ?, ?, ?)",
            [
                $datos['idVenta'],
                $datos['idtipo_de_pagos'],
                $datos['moneda'],
                $datos['monto_recibido'],
                $datos['referencia'] ?? null,
            ]
        );
    }

    public function obtenerDetalleConPagos(int $idVenta): ?array
    {
        $venta = $this->fetch(
            "SELECT v.*, CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre
             FROM {$this->table} v
             INNER JOIN cliente c ON c.cedula = v.cedula_cliente
             WHERE v.idVenta = ? AND v.status = 1",
            [$idVenta]
        );

        if (!$venta) return null;

        $venta['detalles'] = $this->obtenerDetalle($idVenta);
        $venta['pagos'] = $this->fetchAll(
            "SELECT p.*, tp.tipoPago
             FROM pagos p
             INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
             WHERE p.idVenta = ?",
            [$idVenta]
        );

        return $venta;
    }
}
