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

    public function obtenerTodosPaginado(int $start, int $length, string $search, string $orderBy, string $orderDir, ?int $excluirCedula = null): array
    {
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $allowed = ['cedula', 'nombre', 'apellido', 'correo', 'telefono', 'nombreRol', 'status'];
        $orderBy = in_array($orderBy, $allowed) ? $orderBy : 'cedula';

        $sql = "SELECT u.cedula, u.nombre, u.segNombre, u.apellido, u.segApellido,
                       u.correo, u.telefono, u.img, u.idRol, u.status,
                       r.nombreRol
                FROM {$this->table} u
                INNER JOIN rol r ON r.idRol = u.idRol
                WHERE 1=1";

        $params = [];
        if ($excluirCedula !== null) {
            $sql .= " AND u.cedula != ?";
            $params[] = $excluirCedula;
        }
        if ($search !== '') {
            $sql .= " AND (CAST(u.cedula AS CHAR) LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ? OR u.telefono LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $sql .= " ORDER BY u.{$orderBy} {$orderDir}";
        if ($length > 0) {
            $sql .= " LIMIT {$start}, {$length}";
        }

        return $this->fetchAll($sql, $params);
    }

    public function contarUsuarios(string $search, ?int $excluirCedula = null): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} u WHERE 1=1";
        $params = [];

        if ($excluirCedula !== null) {
            $sql .= " AND u.cedula != ?";
            $params[] = $excluirCedula;
        }

        if ($search !== '') {
            $sql .= " AND (CAST(u.cedula AS CHAR) LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR u.correo LIKE ? OR u.telefono LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $row = $this->fetch($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    public function obtenerTodos(?int $excluirCedula = null): array
    {
        $sql = "SELECT u.*, r.nombreRol FROM {$this->table} u
             INNER JOIN rol r ON r.idRol = u.idRol";
        $params = [];
        if ($excluirCedula !== null) {
            $sql .= " WHERE u.cedula != ?";
            $params[] = $excluirCedula;
        }
        $sql .= " ORDER BY u.cedula DESC";
        return $this->fetchAll($sql, $params);
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

    public function existeCorreo(string $correo, ?int $excluirCedula = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE correo = ?";
        $params = [$correo];
        if ($excluirCedula !== null) {
            $sql .= " AND cedula != ?";
            $params[] = $excluirCedula;
        }
        return $this->query($sql, $params)->fetchColumn() > 0;
    }
}
