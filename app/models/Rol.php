<?php

namespace App\Models;

use App\Core\Model;

class Rol extends Model
{
    protected string $table = 'rol';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table} WHERE status = 1 ORDER BY idRol ASC"
        );
    }

    public function obtenerPorId(int $idRol): ?array
    {
        return $this->fetch(
            "SELECT * FROM {$this->table} WHERE idRol = ? AND status = 1",
            [$idRol]
        );
    }

    public function crear(array $datos): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (nombreRol, status) VALUES (?, 1)",
            [$datos['nombreRol']]
        );
    }

    public function actualizar(int $idRol, array $datos): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET nombreRol = ? WHERE idRol = ?",
            [$datos['nombreRol'], $idRol]
        );
    }

    public function eliminar(int $idRol): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 0 WHERE idRol = ?",
            [$idRol]
        );
    }
}
