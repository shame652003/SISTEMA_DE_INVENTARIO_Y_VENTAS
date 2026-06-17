<?php

namespace App\Models;

use App\Core\Model;

class Cliente extends Model
{
    protected string $table = 'cliente';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT c.*, e.tipo_equipo FROM {$this->table} c
             INNER JOIN equipos_cliente e ON e.idEquipoCliente = c.idEquipoCliente
             WHERE c.status = 1
             ORDER BY c.cedula DESC"
        );
    }

    public function obtenerPorCedula(int $cedula): ?array
    {
        return $this->fetch(
            "SELECT c.*, e.tipo_equipo FROM {$this->table} c
             INNER JOIN equipos_cliente e ON e.idEquipoCliente = c.idEquipoCliente
             WHERE c.cedula = ? AND c.status = 1",
            [$cedula]
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

    public function existeCedula(int $cedula, ?int $excluirCedula = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE cedula = ?";
        $params = [$cedula];
        if ($excluirCedula !== null) {
            $sql .= " AND cedula != ?";
            $params[] = $excluirCedula;
        }
        return $this->query($sql, $params)->fetchColumn() > 0;
    }

    public function contarVentas(int $cedula): int
    {
        return (int) $this->query(
            "SELECT COUNT(*) FROM ventas_encabezado WHERE cedula_cliente = ? AND status = 1",
            [$cedula]
        )->fetchColumn();
    }
}
