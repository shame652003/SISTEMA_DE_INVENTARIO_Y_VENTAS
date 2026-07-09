SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS sistemainventario CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sistemainventario;

-- ==========================================
-- 1. MODULO DE SEGURIDAD Y ROLES
-- ==========================================
CREATE TABLE rol (
    idRol INT AUTO_INCREMENT PRIMARY KEY,
    nombreRol VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_rol_nombre (nombreRol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO rol (idRol, nombreRol, status) VALUES (1, 'Super Usuario', 1);

CREATE TABLE usuario (
    cedula INT PRIMARY KEY,
    img VARCHAR(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    nombre VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segNombre VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    apellido VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segApellido VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    correo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    telefono VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    clave VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    idRol INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_usuario_correo (correo),
    CONSTRAINT fk_usuario_rol FOREIGN KEY (idRol) REFERENCES rol(idRol)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE bitacora (
    idBitacora INT AUTO_INCREMENT PRIMARY KEY,
    modulo VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    acciones VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    hora TIME NOT NULL DEFAULT (CURRENT_TIME),
    cedula INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_bitacora_usuario FOREIGN KEY (cedula) REFERENCES usuario(cedula)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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

INSERT INTO usuario (cedula, nombre, apellido, correo, telefono, clave, idRol, status)
VALUES (12345678, 'Admin', 'Sistema', 'admin@sistema.com', '04120000000',
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

-- ==========================================
-- 2. CONFIGURACION DE PRECIOS
-- ==========================================
CREATE TABLE bcv_tasas (
    idTasa INT AUTO_INCREMENT PRIMARY KEY,
    fecha_tasa DATE NOT NULL,
    tasa_ves_por_usd DECIMAL(14,4) NOT NULL,
    observacion VARCHAR(255) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_bcv_fecha (fecha_tasa),
    CHECK (tasa_ves_por_usd > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE margen_ganancia (
    idMargen INT AUTO_INCREMENT PRIMARY KEY,
    tipo_precio ENUM('USD', 'VES') NOT NULL,
    porcentaje DECIMAL(8,4) NOT NULL,
    fecha_inicio DATE NOT NULL DEFAULT (CURRENT_DATE),
    observacion VARCHAR(255) NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_margen_tipo_fecha (tipo_precio, fecha_inicio, idMargen),
    CHECK (porcentaje >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO bcv_tasas (fecha_tasa, tasa_ves_por_usd, observacion)
VALUES (CURRENT_DATE, 700.0000, 'Tasa inicial BCV');

INSERT INTO margen_ganancia (tipo_precio, porcentaje, observacion)
VALUES
    ('USD', 50.0000, 'Margen inicial para pagos en divisas'),
    ('VES', 75.0000, 'Margen inicial para pagos en bolivares');

-- ==========================================
-- 3. MODULO DE INVENTARIO
-- ==========================================
    CREATE TABLE tipo_productos (
        idTipoA INT AUTO_INCREMENT PRIMARY KEY,
        tipo VARCHAR(50) NOT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        UNIQUE KEY uk_tipo_productos_tipo (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE producto (
        idproducto INT AUTO_INCREMENT PRIMARY KEY,
        codigo VARCHAR(50) NOT NULL,
        imgproducto VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
        nombre VARCHAR(100) NOT NULL,
        marca VARCHAR(50) NOT NULL,
        stock DECIMAL(12,3) NOT NULL DEFAULT 0.000,
        stock_minimo DECIMAL(12,3) NOT NULL DEFAULT 5.000,
        precio_costo_usd DECIMAL(12,2) NOT NULL,
        precio_venta_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        precio_venta_ves DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        idTipoA INT NOT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        UNIQUE KEY uk_producto_codigo (codigo),
        KEY idx_producto_tipo (idTipoA),
        CONSTRAINT fk_producto_tipo FOREIGN KEY (idTipoA) REFERENCES tipo_productos(idTipoA)
            ON UPDATE CASCADE,
        CHECK (stock >= 0),
        CHECK (stock_minimo >= 0),
        CHECK (precio_costo_usd >= 0),
        CHECK (precio_venta_usd >= 0),
        CHECK (precio_venta_ves >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE producto_precio_historial (
        idHistorialPrecio INT AUTO_INCREMENT PRIMARY KEY,
        idproducto INT NOT NULL,
        idTasa INT NOT NULL,
        idMargenUsd INT NOT NULL,
        idMargenVes INT NOT NULL,
        precio_costo_usd DECIMAL(12,2) NOT NULL,
        precio_venta_usd DECIMAL(12,2) NOT NULL,
        precio_venta_ves DECIMAL(14,2) NOT NULL,
        fecha_calculo DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_historial_producto FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
            ON UPDATE CASCADE ON DELETE CASCADE,
        CONSTRAINT fk_historial_tasa FOREIGN KEY (idTasa) REFERENCES bcv_tasas(idTasa)
            ON UPDATE CASCADE,
        CONSTRAINT fk_historial_margen_usd FOREIGN KEY (idMargenUsd) REFERENCES margen_ganancia(idMargen)
            ON UPDATE CASCADE,
        CONSTRAINT fk_historial_margen_ves FOREIGN KEY (idMargenVes) REFERENCES margen_ganancia(idMargen)
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE entradaproducto (
        idEntradaA INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
        hora TIME NOT NULL DEFAULT (CURRENT_TIME),
        descripcion VARCHAR(300) NOT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

    CREATE TABLE detalleEntradaA (
        idDetalleA INT AUTO_INCREMENT PRIMARY KEY,
        cantidad DECIMAL(12,3) NOT NULL,
        costo_unitario_usd DECIMAL(12,2) NULL,
        idproducto INT NOT NULL,
        idEntradaA INT NOT NULL,
        status TINYINT(1) NOT NULL DEFAULT 1,
        CONSTRAINT fk_detalle_entrada_producto FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
            ON UPDATE CASCADE,
        CONSTRAINT fk_detalle_entrada_encabezado FOREIGN KEY (idEntradaA) REFERENCES entradaproducto(idEntradaA)
            ON UPDATE CASCADE ON DELETE CASCADE,
        CHECK (cantidad > 0),
        CHECK (costo_unitario_usd IS NULL OR costo_unitario_usd >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tipoSalidas (
    idTipoSalidas INT AUTO_INCREMENT PRIMARY KEY,
    tipoSalida VARCHAR(50) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_tipo_salidas_tipo (tipoSalida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tipoSalidas (tipoSalida, status) VALUES ('Ventas', 1), ('Perdidas', 1);

CREATE TABLE salidas_de_productos (
    idSalidaA INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    hora TIME NOT NULL DEFAULT (CURRENT_TIME),
    descripcion VARCHAR(300) NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    idTipoSalidaA INT NOT NULL,
    idproducto INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_salida_producto FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
        ON UPDATE CASCADE,
    CONSTRAINT fk_salida_tipo FOREIGN KEY (idTipoSalidaA) REFERENCES tipoSalidas(idTipoSalidas)
        ON UPDATE CASCADE,
    CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================
-- 4. MODULO DE CLIENTES
-- ==========================================
CREATE TABLE equipos_cliente (
    idEquipoCliente INT AUTO_INCREMENT PRIMARY KEY,
    tipo_equipo VARCHAR(50) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_equipos_cliente_tipo (tipo_equipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO equipos_cliente (tipo_equipo, status) VALUES ('Frecuente', 1);

CREATE TABLE cliente (
    cedula INT PRIMARY KEY,
    nombre VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segNombre VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    apellido VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    segApellido VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    direccion VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    correo VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    telefono VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
    idEquipoCliente INT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_cliente_equipo FOREIGN KEY (idEquipoCliente) REFERENCES equipos_cliente(idEquipoCliente)
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================
-- 5. MODULO DE VENTAS, PAGOS Y CREDITOS
-- ==========================================
CREATE TABLE ventas_encabezado (
    idVenta INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL DEFAULT (CURRENT_DATE),
    hora TIME NOT NULL DEFAULT (CURRENT_TIME),
    cedula_cliente INT NOT NULL,
    cedula_usuario INT NOT NULL,
    idTasa INT NOT NULL DEFAULT 0,
    total_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_ves DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    total_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_venta_cliente FOREIGN KEY (cedula_cliente) REFERENCES cliente(cedula)
        ON UPDATE CASCADE,
    CONSTRAINT fk_venta_usuario FOREIGN KEY (cedula_usuario) REFERENCES usuario(cedula)
        ON UPDATE CASCADE,
    CONSTRAINT fk_venta_tasa FOREIGN KEY (idTasa) REFERENCES bcv_tasas(idTasa)
        ON UPDATE CASCADE,
    CHECK (total_usd >= 0),
    CHECK (total_ves >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE ventas_detalle (
    idDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idVenta INT NOT NULL,
    idproducto INT NOT NULL,
    cantidad DECIMAL(12,3) NOT NULL,
    costo_unitario_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_unitario_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    precio_unitario_ves DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    precio_unitario_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal_costo_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    subtotal_ves DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    subtotal_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_detalle_venta FOREIGN KEY (idVenta) REFERENCES ventas_encabezado(idVenta)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (idproducto) REFERENCES producto(idproducto)
        ON UPDATE CASCADE,
    CHECK (cantidad > 0),
    CHECK (costo_unitario_usd >= 0),
    CHECK (precio_unitario_usd >= 0),
    CHECK (precio_unitario_ves >= 0),
    CHECK (subtotal_costo_usd >= 0),
    CHECK (subtotal_usd >= 0),
    CHECK (subtotal_ves >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE tipo_de_pagos (
    idtipo_de_pagos INT AUTO_INCREMENT PRIMARY KEY,
    tipoPago VARCHAR(50) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_tipo_pago (tipoPago)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tipo_de_pagos (tipoPago, status)
VALUES ('Efectivo', 1), ('Transferencia', 1), ('Punto', 1), ('Biopago', 1), ('Credito', 1), ('Zelle', 1);

CREATE TABLE pagos (
    idPago INT AUTO_INCREMENT PRIMARY KEY,
    idVenta INT NULL,
    cedula_cliente INT NULL,
    idtipo_de_pagos INT NOT NULL,
    moneda ENUM('USD', 'VES') NOT NULL,
    monto_recibido DECIMAL(14,2) NOT NULL,
    monto_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    referencia VARCHAR(100) NULL,
    fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pago_venta FOREIGN KEY (idVenta) REFERENCES ventas_encabezado(idVenta)
        ON UPDATE CASCADE,
    CONSTRAINT fk_pago_cliente FOREIGN KEY (cedula_cliente) REFERENCES cliente(cedula)
        ON UPDATE CASCADE,
    CONSTRAINT fk_pago_tipo FOREIGN KEY (idtipo_de_pagos) REFERENCES tipo_de_pagos(idtipo_de_pagos)
        ON UPDATE CASCADE,
    CHECK (monto_recibido > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE creditos (
    idCredito INT AUTO_INCREMENT PRIMARY KEY,
    cedula_cliente INT NOT NULL UNIQUE,
    saldo_deudor_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo_deudor_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    ultima_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_credito_cliente FOREIGN KEY (cedula_cliente) REFERENCES cliente(cedula)
        ON UPDATE CASCADE,
    CHECK (saldo_deudor_usd >= 0),
    CHECK (saldo_deudor_bcv >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE creditos_detalle (
    idCreditoDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idVenta INT NOT NULL,
    cedula_cliente INT NOT NULL,
    monto_credito_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    monto_credito_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo_pendiente_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    saldo_pendiente_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_cd_venta FOREIGN KEY (idVenta) REFERENCES ventas_encabezado(idVenta)
        ON UPDATE CASCADE,
    CONSTRAINT fk_cd_cliente FOREIGN KEY (cedula_cliente) REFERENCES cliente(cedula)
        ON UPDATE CASCADE,
    CHECK (monto_credito_usd >= 0),
    CHECK (saldo_pendiente_usd >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE abonos_aplicados (
    idAbonoAplicado INT AUTO_INCREMENT PRIMARY KEY,
    idPago INT NOT NULL,
    idCreditoDetalle INT NOT NULL,
    monto_aplicado_usd DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    monto_aplicado_bcv DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_aa_pago FOREIGN KEY (idPago) REFERENCES pagos(idPago)
        ON UPDATE CASCADE,
    CONSTRAINT fk_aa_cd FOREIGN KEY (idCreditoDetalle) REFERENCES creditos_detalle(idCreditoDetalle)
        ON UPDATE CASCADE,
    CHECK (monto_aplicado_usd >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ==========================================
-- 6. INDICES PARA CONSULTAS Y REPORTES
-- ==========================================
CREATE INDEX idx_bitacora_fecha_hora ON bitacora (fecha, hora);
CREATE INDEX idx_bitacora_usuario_fecha ON bitacora (cedula, fecha);
CREATE INDEX idx_bcv_tasas_status_fecha ON bcv_tasas (status, fecha_tasa);
CREATE INDEX idx_margen_status_tipo_fecha ON margen_ganancia (status, tipo_precio, fecha_inicio);
CREATE INDEX idx_producto_status_stock ON producto (status, stock);
CREATE INDEX idx_producto_stock_bajo ON producto (status, stock, stock_minimo);
CREATE INDEX idx_producto_nombre_marca ON producto (nombre, marca);
CREATE INDEX idx_producto_precio_historial_producto_fecha ON producto_precio_historial (idproducto, fecha_calculo);
CREATE INDEX idx_entrada_fecha ON entradaproducto (fecha, hora);
CREATE INDEX idx_detalle_entrada_producto ON detalleEntradaA (idproducto);
CREATE INDEX idx_salida_fecha_tipo ON salidas_de_productos (fecha, idTipoSalidaA);
CREATE INDEX idx_salida_producto_fecha ON salidas_de_productos (idproducto, fecha);
CREATE INDEX idx_cliente_nombre ON cliente (apellido, nombre);
CREATE INDEX idx_venta_fecha ON ventas_encabezado (fecha, hora);
CREATE INDEX idx_venta_cliente_fecha ON ventas_encabezado (cedula_cliente, fecha);
CREATE INDEX idx_venta_usuario_fecha ON ventas_encabezado (cedula_usuario, fecha);
CREATE INDEX idx_detalle_venta_producto ON ventas_detalle (idproducto);
CREATE INDEX idx_pago_fecha_tipo ON pagos (fecha_pago, idtipo_de_pagos);
CREATE INDEX idx_pago_venta_moneda ON pagos (idVenta, moneda);
CREATE INDEX idx_pago_moneda_fecha ON pagos (moneda, fecha_pago);
CREATE INDEX idx_cd_cliente_status ON creditos_detalle (cedula_cliente, status, saldo_pendiente_bcv);
CREATE INDEX idx_cd_venta ON creditos_detalle (idVenta);
CREATE INDEX idx_aa_pago ON abonos_aplicados (idPago);
CREATE INDEX idx_aa_cd ON abonos_aplicados (idCreditoDetalle);

-- ==========================================
-- 7. VISTAS DE REPORTES ESTADISTICOS
-- ==========================================
CREATE VIEW vw_stock_disponible AS
SELECT
    p.idproducto,
    p.codigo,
    p.nombre,
    p.marca,
    tp.tipo AS tipo_producto,
    p.stock,
    p.stock_minimo,
    p.precio_costo_usd,
    p.precio_venta_usd,
    p.precio_venta_ves,
    p.status
FROM producto p
INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
WHERE p.status = 1;

CREATE VIEW vw_stock_bajo AS
SELECT *
FROM vw_stock_disponible
WHERE stock > 0
  AND stock <= stock_minimo;

CREATE VIEW vw_stock_agotado AS
SELECT *
FROM vw_stock_disponible
WHERE stock = 0;

CREATE VIEW vw_ventas_diarias AS
SELECT
    v.fecha,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    COALESCE(SUM(d.cantidad), 0) AS unidades_vendidas,
    COALESCE(SUM(d.subtotal_costo_usd), 0) AS costo_total_usd,
    COALESCE(SUM(d.subtotal_usd), 0) AS venta_total_usd,
    COALESCE(SUM(d.subtotal_ves), 0) AS venta_total_ves,
    COALESCE(SUM(d.subtotal_usd - d.subtotal_costo_usd), 0) AS ganancia_estimada_usd
FROM ventas_encabezado v
LEFT JOIN ventas_detalle d ON d.idVenta = v.idVenta
WHERE v.status = 1
GROUP BY v.fecha;

CREATE VIEW vw_ventas_semanales AS
SELECT
    YEAR(v.fecha) AS anio,
    WEEK(v.fecha, 1) AS semana,
    MIN(v.fecha) AS fecha_inicio,
    MAX(v.fecha) AS fecha_fin,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    COALESCE(SUM(d.cantidad), 0) AS unidades_vendidas,
    COALESCE(SUM(d.subtotal_costo_usd), 0) AS costo_total_usd,
    COALESCE(SUM(d.subtotal_usd), 0) AS venta_total_usd,
    COALESCE(SUM(d.subtotal_ves), 0) AS venta_total_ves,
    COALESCE(SUM(d.subtotal_usd - d.subtotal_costo_usd), 0) AS ganancia_estimada_usd
FROM ventas_encabezado v
LEFT JOIN ventas_detalle d ON d.idVenta = v.idVenta
WHERE v.status = 1
GROUP BY YEAR(v.fecha), WEEK(v.fecha, 1);

CREATE VIEW vw_ventas_mensuales AS
SELECT
    YEAR(v.fecha) AS anio,
    MONTH(v.fecha) AS mes,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    COALESCE(SUM(d.cantidad), 0) AS unidades_vendidas,
    COALESCE(SUM(d.subtotal_costo_usd), 0) AS costo_total_usd,
    COALESCE(SUM(d.subtotal_usd), 0) AS venta_total_usd,
    COALESCE(SUM(d.subtotal_ves), 0) AS venta_total_ves,
    COALESCE(SUM(d.subtotal_usd - d.subtotal_costo_usd), 0) AS ganancia_estimada_usd
FROM ventas_encabezado v
LEFT JOIN ventas_detalle d ON d.idVenta = v.idVenta
WHERE v.status = 1
GROUP BY YEAR(v.fecha), MONTH(v.fecha);

CREATE VIEW vw_ventas_por_producto AS
SELECT
    p.idproducto,
    p.codigo,
    p.nombre,
    p.marca,
    tp.tipo AS tipo_producto,
    COUNT(DISTINCT v.idVenta) AS ventas_con_producto,
    SUM(d.cantidad) AS unidades_vendidas,
    SUM(d.subtotal_costo_usd) AS costo_total_usd,
    SUM(d.subtotal_usd) AS venta_total_usd,
    SUM(d.subtotal_ves) AS venta_total_ves,
    SUM(d.subtotal_usd - d.subtotal_costo_usd) AS ganancia_estimada_usd
FROM ventas_detalle d
INNER JOIN ventas_encabezado v ON v.idVenta = d.idVenta
INNER JOIN producto p ON p.idproducto = d.idproducto
INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
WHERE v.status = 1
GROUP BY p.idproducto, p.codigo, p.nombre, p.marca, tp.tipo;

CREATE VIEW vw_productos_mas_vendidos AS
SELECT *
FROM vw_ventas_por_producto
ORDER BY unidades_vendidas DESC, venta_total_usd DESC;

CREATE VIEW vw_ventas_por_tipo_producto AS
SELECT
    tp.idTipoA,
    tp.tipo AS tipo_producto,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    SUM(d.cantidad) AS unidades_vendidas,
    SUM(d.subtotal_costo_usd) AS costo_total_usd,
    SUM(d.subtotal_usd) AS venta_total_usd,
    SUM(d.subtotal_ves) AS venta_total_ves,
    SUM(d.subtotal_usd - d.subtotal_costo_usd) AS ganancia_estimada_usd
FROM ventas_detalle d
INNER JOIN ventas_encabezado v ON v.idVenta = d.idVenta
INNER JOIN producto p ON p.idproducto = d.idproducto
INNER JOIN tipo_productos tp ON tp.idTipoA = p.idTipoA
WHERE v.status = 1
GROUP BY tp.idTipoA, tp.tipo;

CREATE VIEW vw_pagos_por_tipo AS
SELECT
    tp.idtipo_de_pagos,
    tp.tipoPago,
    COUNT(p.idPago) AS cantidad_pagos,
    SUM(CASE WHEN p.moneda = 'USD' THEN p.monto_recibido ELSE 0 END) AS total_recibido_usd,
    SUM(CASE WHEN p.moneda = 'VES' THEN p.monto_recibido ELSE 0 END) AS total_recibido_ves
FROM pagos p
INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
GROUP BY tp.idtipo_de_pagos, tp.tipoPago;

CREATE VIEW vw_pagos_por_moneda AS
SELECT
    moneda,
    COUNT(idPago) AS cantidad_pagos,
    SUM(monto_recibido) AS total_recibido
FROM pagos
GROUP BY moneda;

CREATE VIEW vw_pagos_diarios_por_tipo AS
SELECT
    DATE(p.fecha_pago) AS fecha,
    tp.tipoPago,
    p.moneda,
    COUNT(p.idPago) AS cantidad_pagos,
    SUM(p.monto_recibido) AS total_recibido
FROM pagos p
INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos
GROUP BY DATE(p.fecha_pago), tp.tipoPago, p.moneda;

CREATE VIEW vw_ventas_por_cliente AS
SELECT
    c.cedula,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    COALESCE(SUM(d.cantidad), 0) AS unidades_compradas,
    COALESCE(SUM(d.subtotal_usd), 0) AS venta_total_usd,
    COALESCE(SUM(d.subtotal_ves), 0) AS venta_total_ves
FROM cliente c
INNER JOIN ventas_encabezado v ON v.cedula_cliente = c.cedula
LEFT JOIN ventas_detalle d ON d.idVenta = v.idVenta
WHERE v.status = 1
GROUP BY c.cedula, c.nombre, c.apellido;

CREATE VIEW vw_ventas_por_usuario AS
SELECT
    u.cedula,
    CONCAT(u.nombre, ' ', u.apellido) AS usuario,
    COUNT(DISTINCT v.idVenta) AS cantidad_ventas,
    COALESCE(SUM(d.cantidad), 0) AS unidades_vendidas,
    COALESCE(SUM(d.subtotal_usd), 0) AS venta_total_usd,
    COALESCE(SUM(d.subtotal_ves), 0) AS venta_total_ves
FROM usuario u
INNER JOIN ventas_encabezado v ON v.cedula_usuario = u.cedula
LEFT JOIN ventas_detalle d ON d.idVenta = v.idVenta
WHERE v.status = 1
GROUP BY u.cedula, u.nombre, u.apellido;

CREATE VIEW vw_movimientos_inventario AS
SELECT
    'ENTRADA' AS tipo_movimiento,
    e.fecha,
    e.hora,
    d.idproducto,
    p.codigo,
    p.nombre,
    d.cantidad AS cantidad_entrada,
    0.000 AS cantidad_salida,
    e.descripcion
FROM detalleEntradaA d
INNER JOIN entradaproducto e ON e.idEntradaA = d.idEntradaA
INNER JOIN producto p ON p.idproducto = d.idproducto
WHERE d.status = 1
UNION ALL
SELECT
    'SALIDA' AS tipo_movimiento,
    s.fecha,
    s.hora,
    s.idproducto,
    p.codigo,
    p.nombre,
    0.000 AS cantidad_entrada,
    s.cantidad AS cantidad_salida,
    s.descripcion
FROM salidas_de_productos s
INNER JOIN producto p ON p.idproducto = s.idproducto
WHERE s.status = 1
UNION ALL
SELECT
    'VENTA' AS tipo_movimiento,
    v.fecha,
    v.hora,
    d.idproducto,
    p.codigo,
    p.nombre,
    0.000 AS cantidad_entrada,
    d.cantidad AS cantidad_salida,
    CONCAT('Venta #', v.idVenta) AS descripcion
FROM ventas_detalle d
INNER JOIN ventas_encabezado v ON v.idVenta = d.idVenta
INNER JOIN producto p ON p.idproducto = d.idproducto
WHERE v.status = 1;

CREATE VIEW vw_creditos_pendientes AS
SELECT
    cr.idCredito,
    c.cedula,
    CONCAT(c.nombre, ' ', c.apellido) AS cliente,
    cr.saldo_deudor_usd,
    cr.saldo_deudor_bcv,
    cr.ultima_actualizacion
FROM creditos cr
INNER JOIN cliente c ON c.cedula = cr.cedula_cliente
WHERE cr.saldo_deudor_bcv > 0;

-- ALTER TABLE para abonos de crédito (idVenta puede ser NULL)
-- Si la DB ya existe, ejecutar:
-- ALTER TABLE pagos MODIFY COLUMN idVenta INT NULL;
-- ALTER TABLE pagos DROP FOREIGN KEY fk_pago_venta;
-- ALTER TABLE pagos ADD CONSTRAINT fk_pago_venta FOREIGN KEY (idVenta) REFERENCES ventas_encabezado(idVenta) ON UPDATE CASCADE;
-- ALTER TABLE pagos ADD COLUMN cedula_cliente INT NULL AFTER idVenta;
-- ALTER TABLE pagos ADD INDEX idx_pago_cliente (cedula_cliente);
-- ALTER TABLE pagos ADD CONSTRAINT fk_pago_cliente FOREIGN KEY (cedula_cliente) REFERENCES cliente(cedula) ON UPDATE CASCADE;
--
-- Migración de tablas nuevas (creditos_detalle + abonos_aplicados):
-- CREATE TABLE creditos_detalle (...)  -- copiar definición completa de arriba
-- CREATE TABLE abonos_aplicados (...)  -- copiar definición completa de arriba
-- CREATE INDEX idx_cd_cliente_status ON creditos_detalle (cedula_cliente, status, saldo_pendiente_bcv);
-- CREATE INDEX idx_cd_venta ON creditos_detalle (idVenta);
-- CREATE INDEX idx_aa_pago ON abonos_aplicados (idPago);
-- CREATE INDEX idx_aa_cd ON abonos_aplicados (idCreditoDetalle);
--
-- Migrar datos existentes de creditos → creditos_detalle:
-- INSERT INTO creditos_detalle (idVenta, cedula_cliente, monto_credito_usd, monto_credito_bcv, saldo_pendiente_usd, saldo_pendiente_bcv)
-- SELECT p.idVenta, cr.cedula_cliente, cr.saldo_deudor_usd, cr.saldo_deudor_bcv, cr.saldo_deudor_usd, cr.saldo_deudor_bcv
-- FROM creditos cr
-- INNER JOIN pagos p ON p.idVenta IS NOT NULL
-- INNER JOIN tipo_de_pagos tp ON tp.idtipo_de_pagos = p.idtipo_de_pagos AND tp.tipoPago = 'Credito'
-- INNER JOIN ventas_encabezado v ON v.idVenta = p.idVenta AND v.cedula_cliente = cr.cedula_cliente
-- LEFT JOIN creditos_detalle cd ON cd.idVenta = p.idVenta
-- WHERE cd.idCreditoDetalle IS NULL;
--
-- DROP TRIGGER IF EXISTS trg_pago_credito;
-- (luego recrear el trigger con la nueva definición que incluye INSERT en creditos_detalle)

CREATE VIEW vw_resumen_dashboard AS
SELECT
    (SELECT COUNT(*) FROM producto WHERE status = 1) AS productos_activos,
    (SELECT COUNT(*) FROM producto WHERE status = 1 AND stock = 0) AS productos_agotados,
    (SELECT COUNT(*) FROM producto WHERE status = 1 AND stock > 0 AND stock <= stock_minimo) AS productos_stock_bajo,
    (SELECT COUNT(*) FROM ventas_encabezado WHERE status = 1 AND fecha = CURRENT_DATE) AS ventas_hoy,
    (SELECT COALESCE(SUM(total_usd), 0) FROM ventas_encabezado WHERE status = 1 AND fecha = CURRENT_DATE) AS total_usd_hoy,
    (SELECT COALESCE(SUM(total_ves), 0) FROM ventas_encabezado WHERE status = 1 AND fecha = CURRENT_DATE) AS total_ves_hoy,
    (SELECT COALESCE(SUM(saldo_deudor_bcv), 0) FROM creditos) AS creditos_pendientes_bcv;

-- ==========================================
-- 8. LOGICA AUTOMATICA
-- ==========================================
DELIMITER //

-- La aplicacion debe ejecutar CALL sp_set_usuario_bitacora(cedula)
-- al iniciar una sesion valida. Los triggers usaran ese usuario.
CREATE PROCEDURE sp_set_usuario_bitacora(IN p_cedula INT)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM usuario
        WHERE cedula = p_cedula
          AND status = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El usuario de bitacora no existe o esta inactivo';
    END IF;

    SET @app_usuario_cedula = p_cedula;
END //

CREATE PROCEDURE sp_limpiar_usuario_bitacora()
BEGIN
    SET @app_usuario_cedula = NULL;
END //

CREATE PROCEDURE sp_registrar_bitacora(
    IN p_modulo VARCHAR(100),
    IN p_acciones VARCHAR(200)
)
BEGIN
    IF COALESCE(@bitacora_silenciar, 0) = 0
       AND @app_usuario_cedula IS NOT NULL THEN
        INSERT INTO bitacora (modulo, acciones, cedula, status)
        VALUES (p_modulo, p_acciones, @app_usuario_cedula, 1);
    END IF;
END //

CREATE PROCEDURE sp_recalcular_precios_productos()
BEGIN
    DECLARE v_tasa DECIMAL(14,4);
    DECLARE v_margen_usd DECIMAL(8,4);
    DECLARE v_margen_ves DECIMAL(8,4);

    SELECT tasa_ves_por_usd
    INTO v_tasa
    FROM bcv_tasas
    WHERE status = 1
    ORDER BY fecha_tasa DESC, idTasa DESC
    LIMIT 1;

    SELECT porcentaje
    INTO v_margen_usd
    FROM margen_ganancia
    WHERE tipo_precio = 'USD' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    SELECT porcentaje
    INTO v_margen_ves
    FROM margen_ganancia
    WHERE tipo_precio = 'VES' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    SET @bitacora_silenciar = 1;

    UPDATE producto
    SET
        precio_venta_usd = ROUND(precio_costo_usd * (1 + (v_margen_usd / 100)), 2),
        precio_venta_ves = ROUND(precio_costo_usd * (1 + (v_margen_ves / 100)) * v_tasa, 2);

    SET @bitacora_silenciar = 0;
END //

CREATE TRIGGER trg_producto_calcular_precios_insert
BEFORE INSERT ON producto
FOR EACH ROW
BEGIN
    DECLARE v_tasa DECIMAL(14,4);
    DECLARE v_margen_usd DECIMAL(8,4);
    DECLARE v_margen_ves DECIMAL(8,4);

    SELECT tasa_ves_por_usd
    INTO v_tasa
    FROM bcv_tasas
    WHERE status = 1
    ORDER BY fecha_tasa DESC, idTasa DESC
    LIMIT 1;

    SELECT porcentaje
    INTO v_margen_usd
    FROM margen_ganancia
    WHERE tipo_precio = 'USD' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    SELECT porcentaje
    INTO v_margen_ves
    FROM margen_ganancia
    WHERE tipo_precio = 'VES' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    SET NEW.precio_venta_usd = ROUND(NEW.precio_costo_usd * (1 + (v_margen_usd / 100)), 2);
    SET NEW.precio_venta_ves = ROUND(NEW.precio_costo_usd * (1 + (v_margen_ves / 100)) * v_tasa, 2);
END //

CREATE TRIGGER trg_producto_calcular_precios_update
BEFORE UPDATE ON producto
FOR EACH ROW
BEGIN
    DECLARE v_tasa DECIMAL(14,4);
    DECLARE v_margen_usd DECIMAL(8,4);
    DECLARE v_margen_ves DECIMAL(8,4);

    IF NEW.precio_costo_usd <> OLD.precio_costo_usd THEN
        SELECT tasa_ves_por_usd
        INTO v_tasa
        FROM bcv_tasas
        WHERE status = 1
        ORDER BY fecha_tasa DESC, idTasa DESC
        LIMIT 1;

        SELECT porcentaje
        INTO v_margen_usd
        FROM margen_ganancia
        WHERE tipo_precio = 'USD' AND status = 1
        ORDER BY fecha_inicio DESC, idMargen DESC
        LIMIT 1;

        SELECT porcentaje
        INTO v_margen_ves
        FROM margen_ganancia
        WHERE tipo_precio = 'VES' AND status = 1
        ORDER BY fecha_inicio DESC, idMargen DESC
        LIMIT 1;

        SET NEW.precio_venta_usd = ROUND(NEW.precio_costo_usd * (1 + (v_margen_usd / 100)), 2);
        SET NEW.precio_venta_ves = ROUND(NEW.precio_costo_usd * (1 + (v_margen_ves / 100)) * v_tasa, 2);
    END IF;
END //

CREATE TRIGGER trg_producto_historial_insert
AFTER INSERT ON producto
FOR EACH ROW
BEGIN
    DECLARE v_id_tasa INT;
    DECLARE v_id_margen_usd INT;
    DECLARE v_id_margen_ves INT;

    SELECT idTasa
    INTO v_id_tasa
    FROM bcv_tasas
    WHERE status = 1
    ORDER BY fecha_tasa DESC, idTasa DESC
    LIMIT 1;

    SELECT idMargen
    INTO v_id_margen_usd
    FROM margen_ganancia
    WHERE tipo_precio = 'USD' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    SELECT idMargen
    INTO v_id_margen_ves
    FROM margen_ganancia
    WHERE tipo_precio = 'VES' AND status = 1
    ORDER BY fecha_inicio DESC, idMargen DESC
    LIMIT 1;

    INSERT INTO producto_precio_historial (
        idproducto,
        idTasa,
        idMargenUsd,
        idMargenVes,
        precio_costo_usd,
        precio_venta_usd,
        precio_venta_ves
    )
    VALUES (
        NEW.idproducto,
        v_id_tasa,
        v_id_margen_usd,
        v_id_margen_ves,
        NEW.precio_costo_usd,
        NEW.precio_venta_usd,
        NEW.precio_venta_ves
    );

    CALL sp_registrar_bitacora(
        'Inventario - Productos',
        CONCAT('Registro de producto: ', NEW.codigo, ' - ', NEW.nombre)
    );
END //

CREATE TRIGGER trg_producto_historial_update
AFTER UPDATE ON producto
FOR EACH ROW
BEGIN
    DECLARE v_id_tasa INT;
    DECLARE v_id_margen_usd INT;
    DECLARE v_id_margen_ves INT;

    IF NEW.precio_costo_usd <> OLD.precio_costo_usd
       OR NEW.precio_venta_usd <> OLD.precio_venta_usd
       OR NEW.precio_venta_ves <> OLD.precio_venta_ves THEN
        SELECT idTasa
        INTO v_id_tasa
        FROM bcv_tasas
        WHERE status = 1
        ORDER BY fecha_tasa DESC, idTasa DESC
        LIMIT 1;

        SELECT idMargen
        INTO v_id_margen_usd
        FROM margen_ganancia
        WHERE tipo_precio = 'USD' AND status = 1
        ORDER BY fecha_inicio DESC, idMargen DESC
        LIMIT 1;

        SELECT idMargen
        INTO v_id_margen_ves
        FROM margen_ganancia
        WHERE tipo_precio = 'VES' AND status = 1
        ORDER BY fecha_inicio DESC, idMargen DESC
        LIMIT 1;

        INSERT INTO producto_precio_historial (
            idproducto,
            idTasa,
            idMargenUsd,
            idMargenVes,
            precio_costo_usd,
            precio_venta_usd,
            precio_venta_ves
        )
        VALUES (
            NEW.idproducto,
            v_id_tasa,
            v_id_margen_usd,
            v_id_margen_ves,
            NEW.precio_costo_usd,
            NEW.precio_venta_usd,
            NEW.precio_venta_ves
        );
    END IF;

    CALL sp_registrar_bitacora(
        'Inventario - Productos',
        CONCAT('Actualizacion de producto: ', NEW.codigo, ' - ', NEW.nombre)
    );
END //

CREATE TRIGGER trg_bcv_tasa_recalcular_precios
AFTER INSERT ON bcv_tasas
FOR EACH ROW
BEGIN
    CALL sp_recalcular_precios_productos();
    CALL sp_registrar_bitacora(
        'Configuracion - Tasa BCV',
        CONCAT('Registro de tasa BCV ', NEW.fecha_tasa, ': ', NEW.tasa_ves_por_usd, ' VES/USD')
    );
END //

CREATE TRIGGER trg_bcv_tasa_recalcular_precios_update
AFTER UPDATE ON bcv_tasas
FOR EACH ROW
BEGIN
    CALL sp_recalcular_precios_productos();
    CALL sp_registrar_bitacora(
        'Configuracion - Tasa BCV',
        CONCAT('Actualizacion de tasa BCV ', NEW.fecha_tasa, ': ', NEW.tasa_ves_por_usd, ' VES/USD')
    );
END //

CREATE TRIGGER trg_margen_recalcular_precios
AFTER INSERT ON margen_ganancia
FOR EACH ROW
BEGIN
    CALL sp_recalcular_precios_productos();
    CALL sp_registrar_bitacora(
        'Configuracion - Margenes',
        CONCAT('Registro de margen ', NEW.tipo_precio, ': ', NEW.porcentaje, '%')
    );
END //

CREATE TRIGGER trg_margen_recalcular_precios_update
AFTER UPDATE ON margen_ganancia
FOR EACH ROW
BEGIN
    CALL sp_recalcular_precios_productos();
    CALL sp_registrar_bitacora(
        'Configuracion - Margenes',
        CONCAT('Actualizacion de margen ', NEW.tipo_precio, ': ', NEW.porcentaje, '%')
    );
END //

CREATE TRIGGER trg_venta_asignar_tasa
BEFORE INSERT ON ventas_encabezado
FOR EACH ROW
BEGIN
    DECLARE v_id_tasa INT;

    IF NEW.idTasa = 0 THEN
        SELECT idTasa
        INTO v_id_tasa
        FROM bcv_tasas
        WHERE status = 1
        ORDER BY fecha_tasa DESC, idTasa DESC
        LIMIT 1;

        SET NEW.idTasa = v_id_tasa;
    END IF;
END //

CREATE TRIGGER trg_usuario_bitacora_insert
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Seguridad - Usuarios',
        CONCAT('Registro de usuario: ', NEW.cedula, ' - ', NEW.nombre, ' ', NEW.apellido)
    );
END //

CREATE TRIGGER trg_usuario_bitacora_update
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Seguridad - Usuarios',
        CONCAT('Actualizacion de usuario: ', NEW.cedula, ' - ', NEW.nombre, ' ', NEW.apellido)
    );
END //

CREATE TRIGGER trg_rol_bitacora_insert
AFTER INSERT ON rol
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Seguridad - Roles',
        CONCAT('Registro de rol: ', NEW.nombreRol)
    );
END //

CREATE TRIGGER trg_rol_bitacora_update
AFTER UPDATE ON rol
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Seguridad - Roles',
        CONCAT('Actualizacion de rol: ', NEW.nombreRol)
    );
END //

CREATE TRIGGER trg_cliente_bitacora_insert
AFTER INSERT ON cliente
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Clientes',
        CONCAT('Registro de cliente: ', NEW.cedula, ' - ', NEW.nombre, ' ', NEW.apellido)
    );
END //

CREATE TRIGGER trg_cliente_bitacora_update
AFTER UPDATE ON cliente
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Clientes',
        CONCAT('Actualizacion de cliente: ', NEW.cedula, ' - ', NEW.nombre, ' ', NEW.apellido)
    );
END //

CREATE TRIGGER trg_tipo_producto_bitacora_insert
AFTER INSERT ON tipo_productos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Tipos de producto',
        CONCAT('Registro de tipo de producto: ', NEW.tipo)
    );
END //

CREATE TRIGGER trg_tipo_producto_bitacora_update
AFTER UPDATE ON tipo_productos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Tipos de producto',
        CONCAT('Actualizacion de tipo de producto: ', NEW.tipo)
    );
END //

CREATE TRIGGER trg_equipo_cliente_bitacora_insert
AFTER INSERT ON equipos_cliente
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Clientes - Equipos',
        CONCAT('Registro de tipo de equipo: ', NEW.tipo_equipo)
    );
END //

CREATE TRIGGER trg_equipo_cliente_bitacora_update
AFTER UPDATE ON equipos_cliente
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Clientes - Equipos',
        CONCAT('Actualizacion de tipo de equipo: ', NEW.tipo_equipo)
    );
END //

CREATE TRIGGER trg_tipo_pago_bitacora_insert
AFTER INSERT ON tipo_de_pagos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Pagos - Tipos',
        CONCAT('Registro de tipo de pago: ', NEW.tipoPago)
    );
END //

CREATE TRIGGER trg_tipo_pago_bitacora_update
AFTER UPDATE ON tipo_de_pagos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Pagos - Tipos',
        CONCAT('Actualizacion de tipo de pago: ', NEW.tipoPago)
    );
END //

CREATE TRIGGER trg_entrada_bitacora_insert
AFTER INSERT ON entradaproducto
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Entradas',
        CONCAT('Registro de entrada #', NEW.idEntradaA)
    );
END //

CREATE TRIGGER trg_entrada_bitacora_update
AFTER UPDATE ON entradaproducto
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Entradas',
        CONCAT('Actualizacion de entrada #', NEW.idEntradaA)
    );
END //

CREATE TRIGGER trg_venta_bitacora_insert
AFTER INSERT ON ventas_encabezado
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Ventas',
        CONCAT('Registro de venta #', NEW.idVenta, ' para cliente ', NEW.cedula_cliente)
    );
END //

CREATE TRIGGER trg_venta_bitacora_update
AFTER UPDATE ON ventas_encabezado
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Ventas',
        CONCAT('Actualizacion de venta #', NEW.idVenta)
    );
END //

CREATE TRIGGER trg_entrada_sumar_stock
AFTER INSERT ON detalleEntradaA
FOR EACH ROW
BEGIN
    SET @bitacora_silenciar = 1;

    UPDATE producto
    SET
        stock = stock + NEW.cantidad,
        precio_costo_usd = COALESCE(NEW.costo_unitario_usd, precio_costo_usd)
    WHERE idproducto = NEW.idproducto;

    SET @bitacora_silenciar = 0;

    CALL sp_registrar_bitacora(
        'Inventario - Entradas',
        CONCAT('Entrada de producto ID ', NEW.idproducto, ', cantidad ', NEW.cantidad)
    );
END //

CREATE TRIGGER trg_detalle_entrada_bitacora_update
AFTER UPDATE ON detalleEntradaA
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Entradas',
        CONCAT('Actualizacion de detalle de entrada #', NEW.idEntradaA, ', producto ID ', NEW.idproducto)
    );
END //

CREATE TRIGGER trg_venta_detalle_preparar_totales
BEFORE INSERT ON ventas_detalle
FOR EACH ROW
BEGIN
    DECLARE v_costo_usd DECIMAL(12,2);
    DECLARE v_precio_usd DECIMAL(12,2);
    DECLARE v_precio_ves DECIMAL(14,2);
    DECLARE v_tasa DECIMAL(14,4);

    IF NEW.costo_unitario_usd = 0 THEN
        SELECT precio_costo_usd
        INTO v_costo_usd
        FROM producto
        WHERE idproducto = NEW.idproducto;

        SET NEW.costo_unitario_usd = v_costo_usd;
    END IF;

    IF NEW.precio_unitario_usd = 0 THEN
        SELECT precio_venta_usd
        INTO v_precio_usd
        FROM producto
        WHERE idproducto = NEW.idproducto;

        SET NEW.precio_unitario_usd = v_precio_usd;
    END IF;

    IF NEW.precio_unitario_ves = 0 THEN
        SELECT precio_venta_ves
        INTO v_precio_ves
        FROM producto
        WHERE idproducto = NEW.idproducto;

        SET NEW.precio_unitario_ves = v_precio_ves;
    END IF;

    SET NEW.subtotal_costo_usd = ROUND(NEW.cantidad * NEW.costo_unitario_usd, 2);
    SET NEW.subtotal_usd = ROUND(NEW.cantidad * NEW.precio_unitario_usd, 2);
    SET NEW.subtotal_ves = ROUND(NEW.cantidad * NEW.precio_unitario_ves, 2);

    IF NEW.precio_unitario_bcv = 0 THEN
        SELECT t.tasa_ves_por_usd
        INTO v_tasa
        FROM bcv_tasas t
        INNER JOIN ventas_encabezado ve ON ve.idTasa = t.idTasa
        WHERE ve.idVenta = NEW.idVenta;

        SET NEW.precio_unitario_bcv = ROUND(NEW.precio_unitario_ves / v_tasa, 2);
    END IF;

    IF NEW.subtotal_bcv = 0 THEN
        SET NEW.subtotal_bcv = ROUND(NEW.cantidad * NEW.precio_unitario_bcv, 2);
    END IF;
END //

CREATE TRIGGER trg_actualizar_stock_venta
AFTER INSERT ON ventas_detalle
FOR EACH ROW
BEGIN
    SET @bitacora_silenciar = 1;

    UPDATE producto
    SET stock = stock - NEW.cantidad
    WHERE idproducto = NEW.idproducto;

    UPDATE ventas_encabezado
    SET
        total_usd = total_usd + NEW.subtotal_usd,
        total_ves = total_ves + NEW.subtotal_ves,
        total_bcv = total_bcv + NEW.subtotal_bcv
    WHERE idVenta = NEW.idVenta;

    SET @bitacora_silenciar = 0;

    CALL sp_registrar_bitacora(
        'Ventas',
        CONCAT('Detalle de venta #', NEW.idVenta, ': producto ID ', NEW.idproducto, ', cantidad ', NEW.cantidad)
    );
END //

CREATE TRIGGER trg_detalle_venta_bitacora_update
AFTER UPDATE ON ventas_detalle
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Ventas',
        CONCAT('Actualizacion de detalle de venta #', NEW.idVenta, ', producto ID ', NEW.idproducto)
    );
END //

CREATE TRIGGER trg_salida_restar_stock
AFTER INSERT ON salidas_de_productos
FOR EACH ROW
BEGIN
    SET @bitacora_silenciar = 1;

    UPDATE producto
    SET stock = stock - NEW.cantidad
    WHERE idproducto = NEW.idproducto;

    SET @bitacora_silenciar = 0;

    CALL sp_registrar_bitacora(
        'Inventario - Salidas',
        CONCAT('Salida de producto ID ', NEW.idproducto, ', cantidad ', NEW.cantidad)
    );
END //

CREATE TRIGGER trg_salida_bitacora_update
AFTER UPDATE ON salidas_de_productos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Inventario - Salidas',
        CONCAT('Actualizacion de salida #', NEW.idSalidaA, ', producto ID ', NEW.idproducto)
    );
END //

CREATE TRIGGER trg_pago_credito
AFTER INSERT ON pagos
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM tipo_de_pagos
        WHERE idtipo_de_pagos = NEW.idtipo_de_pagos
          AND tipoPago = 'Credito'
    ) THEN
        INSERT INTO creditos (cedula_cliente, saldo_deudor_usd, saldo_deudor_bcv)
        SELECT
            v.cedula_cliente,
            ROUND(v.total_usd * (NEW.monto_bcv / v.total_bcv), 2),
            NEW.monto_bcv
        FROM ventas_encabezado v
        WHERE v.idVenta = NEW.idVenta
        ON DUPLICATE KEY UPDATE
            saldo_deudor_usd = saldo_deudor_usd + VALUES(saldo_deudor_usd),
            saldo_deudor_bcv = saldo_deudor_bcv + VALUES(saldo_deudor_bcv);

        INSERT INTO creditos_detalle (idVenta, cedula_cliente, monto_credito_usd, monto_credito_bcv, saldo_pendiente_usd, saldo_pendiente_bcv)
        SELECT
            v.idVenta,
            v.cedula_cliente,
            ROUND(v.total_usd * (NEW.monto_bcv / v.total_bcv), 2),
            NEW.monto_bcv,
            ROUND(v.total_usd * (NEW.monto_bcv / v.total_bcv), 2),
            NEW.monto_bcv
        FROM ventas_encabezado v
        WHERE v.idVenta = NEW.idVenta;
    END IF;

    CALL sp_registrar_bitacora(
        'Pagos',
        CONCAT('Registro de pago para venta #', NEW.idVenta, ', monto ', NEW.monto_recibido, ' ', NEW.moneda)
    );
END //

CREATE TRIGGER trg_pago_bitacora_update
AFTER UPDATE ON pagos
FOR EACH ROW
BEGIN
    CALL sp_registrar_bitacora(
        'Pagos',
        CONCAT('Actualizacion de pago #', NEW.idPago, ' para venta #', NEW.idVenta)
    );
END //

DELIMITER ;

COMMIT;
