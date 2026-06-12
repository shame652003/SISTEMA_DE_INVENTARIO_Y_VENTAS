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
}
