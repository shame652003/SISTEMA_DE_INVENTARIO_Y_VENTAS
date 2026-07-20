<?php

namespace App\Models;

use App\Core\Model;

class Configuracion extends Model
{
    public function obtenerTasaBcv(): ?array
    {
        return $this->fetch(
            "SELECT idTasa, fecha_tasa, tasa_ves_por_usd, observacion, creado_en
             FROM bcv_tasas WHERE status = 1
             ORDER BY fecha_tasa DESC, idTasa DESC LIMIT 1"
        );
    }

    public function obtenerMargen(string $tipo): ?array
    {
        return $this->fetch(
            "SELECT idMargen, tipo_precio, porcentaje, fecha_inicio, observacion
             FROM margen_ganancia WHERE tipo_precio = ? AND status = 1
             ORDER BY fecha_inicio DESC, idMargen DESC LIMIT 1",
            [$tipo]
        );
    }

    public function guardarTasaBcv(float $tasa, string $fecha, string $observacion): array
    {
        try {
            $this->execute(
                "INSERT INTO bcv_tasas (fecha_tasa, tasa_ves_por_usd, observacion) VALUES (?, ?, ?)",
                [$fecha, $tasa, $observacion ?: null]
            );
            return ['ok' => true, 'mensaje' => 'Tasa BCV actualizada correctamente.'];
        } catch (\Exception $e) {
            return ['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    public function guardarMargen(string $tipo, float $porcentaje, string $observacion): array
    {
        try {
            $this->execute(
                "INSERT INTO margen_ganancia (tipo_precio, porcentaje, observacion) VALUES (?, ?, ?)",
                [$tipo, $porcentaje, $observacion ?: null]
            );
            return ['ok' => true, 'mensaje' => 'Margen de ganancia actualizado correctamente.'];
        } catch (\Exception $e) {
            return ['ok' => false, 'mensaje' => 'Error: ' . $e->getMessage()];
        }
    }

    public function historialTasasBcv(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT idTasa, fecha_tasa, tasa_ves_por_usd, observacion, creado_en
             FROM bcv_tasas WHERE status = 1
             ORDER BY fecha_tasa DESC, idTasa DESC LIMIT ?",
            [$limite]
        );
    }

    public function historialMargenes(int $limite = 10): array
    {
        return $this->fetchAll(
            "SELECT idMargen, tipo_precio, porcentaje, fecha_inicio, observacion, creado_en
             FROM margen_ganancia WHERE status = 1
             ORDER BY fecha_inicio DESC, idMargen DESC LIMIT ?",
            [$limite]
        );
    }
}
