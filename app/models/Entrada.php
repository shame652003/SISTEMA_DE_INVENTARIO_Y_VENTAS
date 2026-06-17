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

    public function obtenerEncabezado(int $idEntrada): ?array
    {
        return $this->fetch(
            "SELECT * FROM {$this->table} WHERE idEntradaA = ? AND status = 1",
            [$idEntrada]
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

    public function buscarProductos(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT p.idproducto AS id, CONCAT(p.codigo, ' - ', p.nombre) AS text,
                    p.codigo, p.nombre, p.marca, p.stock, p.precio_costo_usd,
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

    public function obtenerProductoInfo(int $id): ?array
    {
        return $this->fetch(
            "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.stock,
                    p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves,
                    tp.tipo AS tipo_producto
             FROM producto p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.idproducto = ? AND p.status = 1",
            [$id]
        );
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
}
