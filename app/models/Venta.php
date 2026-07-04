<?php

namespace App\Models;

use App\Core\Model;

class Venta extends Model
{
    protected string $table = 'ventas_encabezado';
    protected string $detalleTable = 'ventas_detalle';

    public function obtenerTiposPago(): array
    {
        return $this->fetchAll("SELECT * FROM tipo_de_pagos WHERE status = 1 ORDER BY tipoPago");
    }

    public function obtenerTasaBcv(): float
    {
        $json = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial');
        if ($json) {
            $data = json_decode($json, true);
            $tasa = $data['promedio'] ?? 0;
            if ($tasa > 0) return (float) $tasa;
        }
        $tasa = $this->fetch(
            "SELECT tasa_ves_por_usd FROM bcv_tasas WHERE status = 1 ORDER BY fecha_tasa DESC LIMIT 1"
        );
        return $tasa ? (float) $tasa['tasa_ves_por_usd'] : 0;
    }
}
