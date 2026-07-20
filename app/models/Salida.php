<?php

namespace App\Models;

use App\Core\Model;

class Salida extends Model
{
    protected string $table = 'salidas_de_productos';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT s.*, p.codigo, p.nombre AS producto_nombre, ts.tipoSalida
             FROM {$this->table} s
             INNER JOIN producto p ON p.idproducto = s.idproducto
             INNER JOIN tipoSalidas ts ON ts.idTipoSalidas = s.idTipoSalidaA
             WHERE s.status = 1
             ORDER BY s.fecha DESC, s.hora DESC"
        );
    }

    public function crear(array $datos): bool
    {
        $campos = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        return $this->execute("INSERT INTO {$this->table} ($campos) VALUES ($placeholders)", array_values($datos));
    }

    public function obtenerTipos(): array
    {
        return $this->fetchAll("SELECT * FROM tipoSalidas WHERE status = 1 ORDER BY tipoSalida");
    }

    public function buscarProductos(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT p.idproducto AS id,
                    CONCAT(p.codigo, ' - ', p.nombre,
                           IF(p.marca IS NOT NULL AND p.marca != '', CONCAT(' [', p.marca, ']'), '')) AS text,
                    p.codigo, p.nombre, p.marca, p.stock, p.precio_costo_usd,
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
}
