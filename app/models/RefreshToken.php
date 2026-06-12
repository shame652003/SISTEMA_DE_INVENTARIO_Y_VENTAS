<?php

namespace App\Models;

use App\Core\Model;

class RefreshToken extends Model
{
    protected string $table = 'refresh_tokens';

    public function guardar(int $cedula, string $tokenHash, string $expiresAt): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (cedula, token_hash, expires_at) VALUES (?, ?, ?)",
            [$cedula, $tokenHash, $expiresAt]
        );
    }

    public function validar(string $tokenHash): ?array
    {
        return $this->fetch(
            "SELECT * FROM {$this->table} WHERE token_hash = ? AND expires_at > NOW()",
            [$tokenHash]
        );
    }

    public function eliminar(string $tokenHash): bool
    {
        return $this->execute("DELETE FROM {$this->table} WHERE token_hash = ?", [$tokenHash]);
    }

    public function eliminarPorUsuario(int $cedula): bool
    {
        return $this->execute("DELETE FROM {$this->table} WHERE cedula = ?", [$cedula]);
    }

    public function limpiarExpirados(): bool
    {
        return $this->execute("DELETE FROM {$this->table} WHERE expires_at <= NOW()");
    }
}
