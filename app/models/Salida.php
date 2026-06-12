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
}
