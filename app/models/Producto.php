<?php

namespace App\Models;

use App\Core\Model;

class Producto extends Model
{
    protected string $table = 'producto';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT p.*, tp.tipo AS tipo_producto FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.status = 1
             ORDER BY p.idproducto DESC"
        );
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->fetch(
            "SELECT p.*, tp.tipo AS tipo_producto FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.idproducto = ? AND p.status = 1",
            [$id]
        );
    }

    public function crear(array $datos): bool
    {
        $campos = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        return $this->execute("INSERT INTO {$this->table} ($campos) VALUES ($placeholders)", array_values($datos));
    }

    public function actualizar(int $id, array $datos): bool
    {
        $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($datos)));
        return $this->execute("UPDATE {$this->table} SET $sets WHERE idproducto = ?", [...array_values($datos), $id]);
    }

    public function eliminar(int $id): bool
    {
        return $this->execute("UPDATE {$this->table} SET status = 0 WHERE idproducto = ?", [$id]);
    }

    public function actualizarStock(int $idproducto, float $cantidad): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET stock = stock + ? WHERE idproducto = ?",
            [$cantidad, $idproducto]
        );
    }

    public function obtenerTipos(): array
    {
        return $this->fetchAll("SELECT * FROM tipo_productos WHERE status = 1 ORDER BY tipo");
    }
}
