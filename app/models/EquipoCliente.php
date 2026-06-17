<?php

namespace App\Models;

use App\Core\Model;

class EquipoCliente extends Model
{
    protected string $table = 'equipos_cliente';

    public function obtenerTodos(): array
    {
        return $this->fetchAll(
            "SELECT idEquipoCliente, tipo_equipo FROM {$this->table} WHERE status = 1 ORDER BY idEquipoCliente ASC"
        );
    }
}
