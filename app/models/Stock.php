<?php

namespace App\Models;

use App\Core\Model;

class Stock extends Model
{
    protected string $table = 'producto';

    public function obtenerTodos(string $filtro = 'todos'): array
    {
        $sql = "SELECT p.idproducto, p.codigo, p.nombre, p.marca, p.imgproducto,
                       tp.tipo AS tipo_producto, p.stock, p.stock_minimo,
                       p.precio_costo_usd, p.precio_venta_usd, p.precio_venta_ves
                FROM {$this->table} p
                INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
                WHERE p.status = 1";

        if ($filtro === 'bajo') {
            $sql .= " AND p.stock > 0 AND p.stock <= p.stock_minimo";
        } elseif ($filtro === 'agotado') {
            $sql .= " AND p.stock = 0";
        }

        $sql .= " ORDER BY p.nombre";

        return $this->fetchAll($sql);
    }

    public function buscarProductos(string $q): array
    {
        $q = '%' . $q . '%';
        return $this->fetchAll(
            "SELECT p.idproducto AS id, CONCAT(p.codigo, ' - ', p.nombre) AS text,
                    p.codigo, p.nombre, p.marca, p.imgproducto, p.stock,
                    tp.tipo AS tipo_producto
             FROM {$this->table} p
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
