
-- BASE DE DATOS: CAFETERIA EXPRESS
-- Estructura inicial del sistema

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    stock_minimo INT NOT NULL DEFAULT 5,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria),

    CHECK (precio >= 0),
    CHECK (stock_actual >= 0),
    CHECK (stock_minimo >= 0)
);

CREATE TABLE IF NOT EXISTS movimientos_inventario (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo ENUM('ENTRADA','SALIDA','AJUSTE') NOT NULL,
    cantidad INT NOT NULL,
    motivo VARCHAR(255),
    fecha_movimiento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto),

    CHECK (cantidad > 0)
);

CREATE TABLE IF NOT EXISTS ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    fecha_venta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    metodo_pago ENUM(
        'EFECTIVO',
        'TARJETA',
        'TRANSFERENCIA'
    ) NOT NULL,
    estado ENUM('COMPLETADA','CANCELADA')
        NOT NULL DEFAULT 'COMPLETADA',

    CHECK (total >= 0)
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_venta)
        REFERENCES ventas(id_venta),

    FOREIGN KEY (id_producto)
        REFERENCES productos(id_producto),

    CHECK (cantidad > 0),
    CHECK (precio_unitario >= 0),
    CHECK (subtotal >= 0)
);

-- Categorías iniciales de ejemplo

INSERT IGNORE INTO categorias (nombre, descripcion)
VALUES
('Café', 'Bebidas preparadas con café'),
('Té', 'Infusiones y bebidas calientes'),
('Bebidas frías', 'Bebidas servidas frías'),
('Panadería', 'Pan y productos horneados'),
('Postres', 'Pasteles y otros postres'),
('Alimentos', 'Comidas y refrigerios');
