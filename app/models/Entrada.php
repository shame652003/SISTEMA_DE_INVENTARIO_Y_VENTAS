<?php

namespace App\Models;

use App\Core\Model;

class Entrada extends Model
{
    protected string $table = 'entradaproducto';
    protected string $detalleTable = 'detalleEntradaA';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT e.*, COUNT(d.idDetalleA) AS items, SUM(d.cantidad) AS total_cantidad
             FROM {$this->table} e
             LEFT JOIN {$this->detalleTable} d ON d.idEntradaA = e.idEntradaA
             WHERE e.status = 1
             GROUP BY e.idEntradaA
             ORDER BY e.fecha DESC, e.hora DESC"
        );
    }

    public function obtenerDetalle(int $idEntrada): array
    {
        return $this->fetchAll(
            "SELECT d.*, p.codigo, p.nombre AS producto_nombre
             FROM {$this->detalleTable} d
             INNER JOIN producto p ON p.idproducto = d.idproducto
             WHERE d.idEntradaA = ?",
            [$idEntrada]
        );
    }

    public function crearEncabezado(array $datos): int
    {
        $this->execute(
            "INSERT INTO {$this->table} (descripcion) VALUES (?)",
            [$datos['descripcion'] ?? 'Entrada de productos']
        );
        return (int) $this->lastInsertId();
    }

    public function crearDetalle(int $idEntrada, int $idproducto, float $cantidad, ?float $costoUnitario): bool
    {
        return $this->execute(
            "INSERT INTO {$this->detalleTable} (idEntradaA, idproducto, cantidad, costo_unitario_usd) VALUES (?, ?, ?, ?)",
            [$idEntrada, $idproducto, $cantidad, $costoUnitario]
        );
    }
}
