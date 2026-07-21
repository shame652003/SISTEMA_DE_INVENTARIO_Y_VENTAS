<?php

namespace App\Models;

use App\Core\Model;

class Stock extends Model
{
    protected string $table = 'producto';

    public function obtenerTodosPaginado(int $start, int $length, string $search, string $filtro, string $orderBy, string $orderDir): array
    {
        $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
        $allowed = ['codigo', 'nombre', 'tipo_producto', 'stock', 'precio_venta_ves', 'precio_venta_usd'];
        $orderBy = in_array($orderBy, $allowed) ? $orderBy : 'nombre';

        $sql = "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.imgproducto,
                       tp.tipo AS tipo_producto, p.stock, p.stock_minimo,
                       p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves
                FROM {$this->table} p
                INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
                WHERE p.status = 1";

        $params = [];
        $this->agregarFiltroSql($sql, $params, $filtro, $search);

        $sql .= " ORDER BY p.{$orderBy} {$orderDir}";
        if ($length > 0) {
            $sql .= " LIMIT {$start}, {$length}";
        }

        return $this->fetchAll($sql, $params);
    }

    public function contarProductos(string $search, string $filtro): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table} p
                WHERE p.status = 1";

        $params = [];
        $this->agregarFiltroSql($sql, $params, $filtro, $search);

        $row = $this->fetch($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    private function agregarFiltroSql(string &$sql, array &$params, string $filtro, string $search): void
    {
        if ($filtro === 'bajo') {
            $sql .= " AND p.stock > 0 AND p.stock <= p.stock_minimo";
        } elseif ($filtro === 'agotado') {
            $sql .= " AND p.stock = 0";
        } elseif ($filtro === 'disponible') {
            $sql .= " AND p.stock > 0";
        }

        if ($search !== '') {
            $sql .= " AND (p.codigo LIKE ? OR p.nombre LIKE ? OR p.marca LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
    }

    public function buscarProductos(string $q): array
    {
        $q = trim($q);
        $words = array_filter(explode(' ', $q));
        $ftQ = '+' . implode('* +', $words) . '*';

        return $this->fetchAll(
            "SELECT p.idproducto AS id,
                    CONCAT(p.codigo, ' - ', p.nombre,
                           IF(p.marca IS NOT NULL AND p.marca != '', CONCAT(' [', p.marca, ']'), '')) AS text,
                    p.codigo, p.nombre, p.marca, p.imgproducto,
                    p.stock, p.stock_minimo,
                    p.precio_venta_usd, p.precio_venta_ves, p.precio_costo_usd,
                    tp.tipo AS tipo_producto
             FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.status = 1
               AND MATCH(p.codigo, p.nombre, p.marca) AGAINST(? IN BOOLEAN MODE)
             ORDER BY p.nombre
             LIMIT 20",
            [$ftQ]
        );
    }

    public function obtenerProductoInfo(int $id): ?array
    {
        $producto = $this->fetch(
            "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.imgproducto,
                    tp.tipo AS tipo_producto, p.stock, p.stock_minimo,
                    p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves
             FROM {$this->table} p
             INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
             WHERE p.idproducto = ? AND p.status = 1",
            [$id]
        );

        if ($producto) {
            $tasa = $this->obtenerTasaActual();
            $tasaBcv = $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
            $precioVentaVes = (float) ($producto['precio_venta_ves'] ?? 0);
            $producto['precio_bcv'] = $tasaBcv > 0 ? round($precioVentaVes / $tasaBcv, 2) : 0;
            $producto['tasa_bcv'] = $tasaBcv;
        }

        return $producto;
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
}
