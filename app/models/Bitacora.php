<?php

namespace App\Models;

use App\Core\Model;

class Bitacora extends Model
{
    protected string $table = 'bitacora';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT b.*, CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre
             FROM {$this->table} b
             INNER JOIN usuario u ON u.cedula = b.cedula
             ORDER BY b.fecha DESC, b.hora DESC
             LIMIT 500"
        );
    }

    public function registrar(int $cedula, string $modulo, string $acciones): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (modulo, acciones, cedula) VALUES (?, ?, ?)",
            [$modulo, $acciones, $cedula]
        );
    }
}
