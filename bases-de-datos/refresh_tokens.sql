-- Tabla para almacenar refresh tokens (JWT)
-- Ejecutar después de haber importado inventario-sistema.sql

CREATE TABLE IF NOT EXISTS refresh_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_cedula (cedula),
    INDEX idx_expires (expires_at),
    CONSTRAINT fk_refresh_usuario FOREIGN KEY (cedula) REFERENCES usuario(cedula) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
