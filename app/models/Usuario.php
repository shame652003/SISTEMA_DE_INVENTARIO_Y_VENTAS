<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuario';

    public function obtenerPorCorreo(string $correo): ?array
    {
        return $this->fetch(
            "SELECT u.*, r.nombreRol FROM {$this->table} u
             INNER JOIN rol r ON r.idRol = u.idRol
             WHERE u.correo = ? AND u.status = 1",
            [$correo]
        );
    }

    public function obtenerPorCedula(int $cedula): ?array
    {
        return $this->fetch(
            "SELECT u.*, r.nombreRol FROM {$this->table} u
             INNER JOIN rol r ON r.idRol = u.idRol
             WHERE u.cedula = ?",
            [$cedula]
        );
    }

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT u.*, r.nombreRol FROM {$this->table} u
             INNER JOIN rol r ON r.idRol = u.idRol
             ORDER BY u.cedula DESC"
        );
    }

    public function crear(array $datos): bool
    {
        $campos = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        return $this->execute("INSERT INTO {$this->table} ($campos) VALUES ($placeholders)", array_values($datos));
    }

    public function actualizar(int $cedula, array $datos): bool
    {
        $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($datos)));
        return $this->execute("UPDATE {$this->table} SET $sets WHERE cedula = ?", [...array_values($datos), $cedula]);
    }

    public function eliminar(int $cedula): bool
    {
        return $this->execute("UPDATE {$this->table} SET status = 0 WHERE cedula = ?", [$cedula]);
    }
}
