<?php

namespace App\Models;

use App\Core\Model;

class Producto extends Model
{
    protected string $table = 'producto';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT p.*, tp.tipo AS tipo_producto, pr.nombre AS nombre_proveedor
             FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             LEFT JOIN proveedores pr ON pr.idProveedor = p.idProveedor
             WHERE p.status = 1
             ORDER BY p.idproducto DESC"
        );
    }

    public function obtenerTodosPaginado(int $start, int $length, string $search, string $orderBy, string $orderDir): array
    {
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $allowed = ['codigo', 'nombre', 'tipo_producto', 'marca', 'nombre_proveedor'];
        $orderBy = in_array($orderBy, $allowed) ? $orderBy : 'codigo';

        $sql = "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.imgproducto,
                       tp.tipo AS tipo_producto,
                       pr.nombre AS nombre_proveedor
                FROM {$this->table} p
                INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
                LEFT JOIN proveedores pr ON pr.idProveedor = p.idProveedor
                WHERE p.status = 1";

        $params = [];
        if ($search !== '') {
            $sql .= " AND (p.codigo LIKE ? OR p.nombre LIKE ? OR p.marca LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $sql .= " ORDER BY {$orderBy} {$orderDir}";
        if ($length > 0) {
            $sql .= " LIMIT {$start}, {$length}";
        }

        return $this->fetchAll($sql, $params);
    }

    public function contarProductos(string $search): int
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} p WHERE p.status = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.codigo LIKE ? OR p.nombre LIKE ? OR p.marca LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $row = $this->fetch($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->fetch(
            "SELECT p.*, tp.tipo AS tipo_producto, pr.nombre AS nombre_proveedor
             FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             LEFT JOIN proveedores pr ON pr.idProveedor = p.idProveedor
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

    public function existeCodigo(string $codigo, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} WHERE codigo = ? AND status = 1";
        $params = [$codigo];
        if ($excluirId !== null) {
            $sql .= " AND idproducto != ?";
            $params[] = $excluirId;
        }
        $res = $this->fetch($sql, $params);
        return ($res['total'] ?? 0) > 0;
    }

    /* ===== CRUD tipo_productos ===== */

    public function crearTipo(array $datos): bool
    {
        $campos = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        return $this->execute("INSERT INTO tipo_productos ($campos) VALUES ($placeholders)", array_values($datos));
    }

    public function actualizarTipo(int $id, array $datos): bool
    {
        $sets = implode(', ', array_map(fn($c) => "$c = ?", array_keys($datos)));
        return $this->execute("UPDATE tipo_productos SET $sets WHERE idTipoA = ?", [...array_values($datos), $id]);
    }

    public function eliminarTipo(int $id): bool
    {
        return $this->execute("UPDATE tipo_productos SET status = 0 WHERE idTipoA = ?", [$id]);
    }

    public function obtenerTipoPorId(int $id): ?array
    {
        return $this->fetch("SELECT * FROM tipo_productos WHERE idTipoA = ? AND status = 1", [$id]);
    }

    public function tipoEnUso(int $idTipoA): bool
    {
        $res = $this->fetch("SELECT COUNT(*) AS total FROM {$this->table} WHERE idTipoA = ? AND status = 1", [$idTipoA]);
        return ($res['total'] ?? 0) > 0;
    }

    public function existeTipo(string $tipo): bool
    {
        $res = $this->fetch(
            "SELECT COUNT(*) AS total FROM tipo_productos WHERE LOWER(tipo) = LOWER(?) AND status = 1",
            [$tipo]
        );
        return ($res['total'] ?? 0) > 0;
    }

    /* ===== CRUD proveedores ===== */

    public function obtenerProveedores(): array
    {
        return $this->fetchAll("SELECT * FROM proveedores WHERE status = 1 ORDER BY nombre");
    }

    public function existeProveedor(string $nombre): bool
    {
        $res = $this->fetch(
            "SELECT COUNT(*) AS total FROM proveedores WHERE LOWER(nombre) = LOWER(?) AND status = 1",
            [$nombre]
        );
        return ($res['total'] ?? 0) > 0;
    }

    public function crearProveedor(array $datos): bool
    {
        $campos = implode(', ', array_keys($datos));
        $placeholders = implode(', ', array_fill(0, count($datos), '?'));
        return $this->execute("INSERT INTO proveedores ($campos) VALUES ($placeholders)", array_values($datos));
    }

    public function eliminarProveedor(int $id): bool
    {
        return $this->execute("UPDATE proveedores SET status = 0 WHERE idProveedor = ?", [$id]);
    }

    public function proveedorEnUso(int $idProveedor): bool
    {
        $res = $this->fetch("SELECT COUNT(*) AS total FROM {$this->table} WHERE idProveedor = ? AND status = 1", [$idProveedor]);
        return ($res['total'] ?? 0) > 0;
    }

    public function productoTieneVentas(int $idproducto): bool
    {
        $res = $this->fetch(
            "SELECT COUNT(*) AS total
             FROM ventas_detalle vd
             INNER JOIN ventas_encabezado ve ON ve.idVenta = vd.idVenta
             WHERE vd.idproducto = ? AND ve.status = 1",
            [$idproducto]
        );
        return ($res['total'] ?? 0) > 0;
    }
}
