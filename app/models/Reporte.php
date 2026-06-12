<?php

namespace App\Models;

use App\Core\Model;

class Reporte extends Model
{
    public function resumenDashboard(): array
    {
        return $this->fetch("SELECT * FROM vw_resumen_dashboard");
    }

    public function ventasPorPeriodo(string $inicio, string $fin): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_ventas_diarias WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC",
            [$inicio, $fin]
        );
    }

    public function pagosPorPeriodo(string $inicio, string $fin, string $metodo = ''): array
    {
        $sql = "SELECT * FROM vw_pagos_diarios_por_tipo WHERE fecha BETWEEN ? AND ?";
        $params = [$inicio, $fin];

        if ($metodo !== '') {
            $sql .= " AND tipoPago = ?";
            $params[] = $metodo;
        }

        $sql .= " ORDER BY fecha ASC";
        return $this->fetchAll($sql, $params);
    }

    public function movimientosInventario(string $inicio, string $fin): array
    {
        return $this->fetchAll(
            "SELECT * FROM vw_movimientos_inventario WHERE fecha BETWEEN ? AND ? ORDER BY fecha ASC, hora ASC",
            [$inicio, $fin]
        );
    }

    public function stockDisponible(): array
    {
        return $this->fetchAll("SELECT * FROM vw_stock_disponible ORDER BY nombre");
    }

    public function stockBajo(): array
    {
        return $this->fetchAll("SELECT * FROM vw_stock_bajo ORDER BY stock ASC");
    }

    public function productosMasVendidos(): array
    {
        return $this->fetchAll("SELECT * FROM vw_productos_mas_vendidos LIMIT 50");
    }
}
