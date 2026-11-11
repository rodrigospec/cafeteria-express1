-- Ejecutar una sola vez en la BD cafeteria_express con permisos de creación.
-- El SQL se importa con mysql CLI (soporta DELIMITER).
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_categorias_listar$$
CREATE PROCEDURE sp_categorias_listar() BEGIN SELECT * FROM categorias ORDER BY id_categoria DESC; END$$
DROP PROCEDURE IF EXISTS sp_categorias_obtener$$
CREATE PROCEDURE sp_categorias_obtener(IN p_id INT) BEGIN SELECT * FROM categorias WHERE id_categoria=p_id; END$$
DROP PROCEDURE IF EXISTS sp_categorias_crear$$
CREATE PROCEDURE sp_categorias_crear(IN p_nombre TEXT, IN p_descripcion TEXT, IN p_activo TINYINT) BEGIN INSERT INTO categorias (nombre, descripcion, activo) VALUES (p_nombre, p_descripcion, p_activo); END$$
DROP PROCEDURE IF EXISTS sp_categorias_actualizar$$
CREATE PROCEDURE sp_categorias_actualizar(IN p_id INT, IN p_nombre TEXT, IN p_descripcion TEXT, IN p_activo TINYINT) BEGIN UPDATE categorias SET nombre=p_nombre, descripcion=p_descripcion, activo=p_activo WHERE id_categoria=p_id; END$$
DROP PROCEDURE IF EXISTS sp_categorias_eliminar$$
CREATE PROCEDURE sp_categorias_eliminar(IN p_id INT)
BEGIN
 IF EXISTS (SELECT 1 FROM productos WHERE id_categoria=p_id LIMIT 1) THEN
  UPDATE categorias SET activo=0 WHERE id_categoria=p_id;
 ELSE
  DELETE FROM categorias WHERE id_categoria=p_id;
 END IF;
END$$
DROP PROCEDURE IF EXISTS sp_productos_listar$$
CREATE PROCEDURE sp_productos_listar() BEGIN SELECT * FROM productos ORDER BY id_producto DESC; END$$
DROP PROCEDURE IF EXISTS sp_productos_obtener$$
CREATE PROCEDURE sp_productos_obtener(IN p_id INT) BEGIN SELECT * FROM productos WHERE id_producto=p_id; END$$
DROP PROCEDURE IF EXISTS sp_productos_crear$$
CREATE PROCEDURE sp_productos_crear(IN p_id_categoria INT, IN p_nombre TEXT, IN p_descripcion TEXT, IN p_precio DECIMAL(10,2), IN p_stock_actual INT, IN p_stock_minimo INT, IN p_activo TINYINT) BEGIN INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock_actual, stock_minimo, activo) VALUES (p_id_categoria, p_nombre, p_descripcion, p_precio, p_stock_actual, p_stock_minimo, p_activo); END$$
DROP PROCEDURE IF EXISTS sp_productos_actualizar$$
CREATE PROCEDURE sp_productos_actualizar(IN p_id INT, IN p_id_categoria INT, IN p_nombre TEXT, IN p_descripcion TEXT, IN p_precio DECIMAL(10,2), IN p_stock_actual INT, IN p_stock_minimo INT, IN p_activo TINYINT) BEGIN UPDATE productos SET id_categoria=p_id_categoria, nombre=p_nombre, descripcion=p_descripcion, precio=p_precio, stock_actual=p_stock_actual, stock_minimo=p_stock_minimo, activo=p_activo WHERE id_producto=p_id; END$$
DROP PROCEDURE IF EXISTS sp_productos_eliminar$$
CREATE PROCEDURE sp_productos_eliminar(IN p_id INT)
BEGIN
 DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
 START TRANSACTION;
 IF EXISTS (SELECT 1 FROM detalle_ventas WHERE id_producto=p_id LIMIT 1) THEN
  UPDATE productos SET activo=0 WHERE id_producto=p_id;
 ELSE
  DELETE FROM movimientos_inventario WHERE id_producto=p_id;
  DELETE FROM productos WHERE id_producto=p_id;
 END IF;
 COMMIT;
END$$
DROP PROCEDURE IF EXISTS sp_movimientos_inventario_listar$$
CREATE PROCEDURE sp_movimientos_inventario_listar() BEGIN SELECT * FROM movimientos_inventario ORDER BY id_movimiento DESC; END$$
DROP PROCEDURE IF EXISTS sp_movimientos_inventario_obtener$$
CREATE PROCEDURE sp_movimientos_inventario_obtener(IN p_id INT) BEGIN SELECT * FROM movimientos_inventario WHERE id_movimiento=p_id; END$$
DROP PROCEDURE IF EXISTS sp_movimientos_inventario_crear$$
CREATE PROCEDURE sp_movimientos_inventario_crear(IN p_id_producto INT, IN p_tipo TEXT, IN p_cantidad INT, IN p_motivo TEXT) BEGIN INSERT INTO movimientos_inventario (id_producto, tipo, cantidad, motivo) VALUES (p_id_producto, p_tipo, p_cantidad, p_motivo); END$$
DROP PROCEDURE IF EXISTS sp_movimientos_inventario_actualizar$$
CREATE PROCEDURE sp_movimientos_inventario_actualizar(IN p_id INT, IN p_id_producto INT, IN p_tipo TEXT, IN p_cantidad INT, IN p_motivo TEXT) BEGIN UPDATE movimientos_inventario SET id_producto=p_id_producto, tipo=p_tipo, cantidad=p_cantidad, motivo=p_motivo WHERE id_movimiento=p_id; END$$
DROP PROCEDURE IF EXISTS sp_movimientos_inventario_eliminar$$
CREATE PROCEDURE sp_movimientos_inventario_eliminar(IN p_id INT) BEGIN DELETE FROM movimientos_inventario WHERE id_movimiento=p_id; END$$
DROP PROCEDURE IF EXISTS sp_ventas_listar$$
CREATE PROCEDURE sp_ventas_listar() BEGIN SELECT * FROM ventas ORDER BY id_venta DESC; END$$
DROP PROCEDURE IF EXISTS sp_ventas_obtener$$
CREATE PROCEDURE sp_ventas_obtener(IN p_id INT) BEGIN SELECT * FROM ventas WHERE id_venta=p_id; END$$
DROP PROCEDURE IF EXISTS sp_ventas_crear$$
CREATE PROCEDURE sp_ventas_crear(IN p_metodo_pago TEXT, IN p_estado TEXT) BEGIN INSERT INTO ventas (metodo_pago, estado) VALUES (p_metodo_pago, p_estado); END$$
DROP PROCEDURE IF EXISTS sp_ventas_actualizar$$
CREATE PROCEDURE sp_ventas_actualizar(IN p_id INT, IN p_metodo_pago TEXT, IN p_estado TEXT) BEGIN UPDATE ventas SET metodo_pago=p_metodo_pago, estado=p_estado WHERE id_venta=p_id; END$$
DROP PROCEDURE IF EXISTS sp_ventas_eliminar$$
CREATE PROCEDURE sp_ventas_eliminar(IN p_id INT)
BEGIN
 DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
 START TRANSACTION;
 DELETE FROM detalle_ventas WHERE id_venta=p_id;
 DELETE FROM ventas WHERE id_venta=p_id;
 COMMIT;
END$$
DROP PROCEDURE IF EXISTS sp_detalle_ventas_listar$$
CREATE PROCEDURE sp_detalle_ventas_listar() BEGIN SELECT * FROM detalle_ventas ORDER BY id_detalle DESC; END$$
DROP PROCEDURE IF EXISTS sp_detalle_ventas_obtener$$
CREATE PROCEDURE sp_detalle_ventas_obtener(IN p_id INT) BEGIN SELECT * FROM detalle_ventas WHERE id_detalle=p_id; END$$
DROP PROCEDURE IF EXISTS sp_detalle_ventas_crear$$
CREATE PROCEDURE sp_detalle_ventas_crear(IN p_id_venta INT, IN p_id_producto INT, IN p_cantidad INT, IN p_precio_unitario DECIMAL(10,2)) BEGIN INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario) VALUES (p_id_venta, p_id_producto, p_cantidad, p_precio_unitario); END$$
DROP PROCEDURE IF EXISTS sp_detalle_ventas_actualizar$$
CREATE PROCEDURE sp_detalle_ventas_actualizar(IN p_id INT, IN p_id_venta INT, IN p_id_producto INT, IN p_cantidad INT, IN p_precio_unitario DECIMAL(10,2)) BEGIN UPDATE detalle_ventas SET id_venta=p_id_venta, id_producto=p_id_producto, cantidad=p_cantidad, precio_unitario=p_precio_unitario WHERE id_detalle=p_id; END$$
DROP PROCEDURE IF EXISTS sp_detalle_ventas_eliminar$$
CREATE PROCEDURE sp_detalle_ventas_eliminar(IN p_id INT) BEGIN DELETE FROM detalle_ventas WHERE id_detalle=p_id; END$$
-- AJUSTE se interpreta como incremento positivo de stock.
-- Los CHECK de productos impiden existencias negativas y revierten la operación.
DROP TRIGGER IF EXISTS trg_mov_insert$$
CREATE TRIGGER trg_mov_insert AFTER INSERT ON movimientos_inventario FOR EACH ROW
BEGIN
 UPDATE productos SET stock_actual=stock_actual+(CASE WHEN NEW.tipo='SALIDA' THEN -NEW.cantidad ELSE NEW.cantidad END)
 WHERE id_producto=NEW.id_producto;
END$$
DROP TRIGGER IF EXISTS trg_mov_delete$$
CREATE TRIGGER trg_mov_delete AFTER DELETE ON movimientos_inventario FOR EACH ROW
BEGIN
 UPDATE productos SET stock_actual=stock_actual-(CASE WHEN OLD.tipo='SALIDA' THEN -OLD.cantidad ELSE OLD.cantidad END)
 WHERE id_producto=OLD.id_producto;
END$$
DROP TRIGGER IF EXISTS trg_mov_update$$
CREATE TRIGGER trg_mov_update AFTER UPDATE ON movimientos_inventario FOR EACH ROW
BEGIN
 UPDATE productos SET stock_actual=stock_actual-(CASE WHEN OLD.tipo='SALIDA' THEN -OLD.cantidad ELSE OLD.cantidad END)
 WHERE id_producto=OLD.id_producto;
 UPDATE productos SET stock_actual=stock_actual+(CASE WHEN NEW.tipo='SALIDA' THEN -NEW.cantidad ELSE NEW.cantidad END)
 WHERE id_producto=NEW.id_producto;
END$$
-- Una venta cancelada no descuenta stock. Cambiar estado repone/descuenta
-- todas las líneas existentes. Las líneas de ventas canceladas son editables.
DROP TRIGGER IF EXISTS trg_venta_estado$$
CREATE TRIGGER trg_venta_estado AFTER UPDATE ON ventas FOR EACH ROW
BEGIN
 IF OLD.estado <> NEW.estado THEN
  IF NEW.estado='CANCELADA' THEN
   UPDATE productos p JOIN (
    SELECT id_producto, SUM(cantidad) AS cantidad FROM detalle_ventas
    WHERE id_venta=NEW.id_venta GROUP BY id_producto
   ) d ON p.id_producto=d.id_producto
   SET p.stock_actual=p.stock_actual+d.cantidad;
  ELSE
   UPDATE productos p JOIN (
    SELECT id_producto, SUM(cantidad) AS cantidad FROM detalle_ventas
    WHERE id_venta=NEW.id_venta GROUP BY id_producto
   ) d ON p.id_producto=d.id_producto
   SET p.stock_actual=p.stock_actual-d.cantidad;
  END IF;
 END IF;
END$$
DROP TRIGGER IF EXISTS trg_det_calc_insert$$
CREATE TRIGGER trg_det_calc_insert BEFORE INSERT ON detalle_ventas FOR EACH ROW
BEGIN
 SET NEW.subtotal=ROUND(NEW.cantidad*NEW.precio_unitario,2);
END$$
DROP TRIGGER IF EXISTS trg_det_calc_update$$
CREATE TRIGGER trg_det_calc_update BEFORE UPDATE ON detalle_ventas FOR EACH ROW
BEGIN
 SET NEW.subtotal=ROUND(NEW.cantidad*NEW.precio_unitario,2);
END$$
DROP TRIGGER IF EXISTS trg_det_insert$$
CREATE TRIGGER trg_det_insert AFTER INSERT ON detalle_ventas FOR EACH ROW
BEGIN
 IF (SELECT estado FROM ventas WHERE id_venta=NEW.id_venta)='COMPLETADA' THEN
  UPDATE productos SET stock_actual=stock_actual-NEW.cantidad WHERE id_producto=NEW.id_producto;
 END IF;
 UPDATE ventas SET total=(SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas WHERE id_venta=NEW.id_venta) WHERE id_venta=NEW.id_venta;
END$$
DROP TRIGGER IF EXISTS trg_det_delete$$
CREATE TRIGGER trg_det_delete AFTER DELETE ON detalle_ventas FOR EACH ROW
BEGIN
 IF (SELECT estado FROM ventas WHERE id_venta=OLD.id_venta)='COMPLETADA' THEN
  UPDATE productos SET stock_actual=stock_actual+OLD.cantidad WHERE id_producto=OLD.id_producto;
 END IF;
 UPDATE ventas SET total=(SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas WHERE id_venta=OLD.id_venta) WHERE id_venta=OLD.id_venta;
END$$
DROP TRIGGER IF EXISTS trg_det_update$$
CREATE TRIGGER trg_det_update AFTER UPDATE ON detalle_ventas FOR EACH ROW
BEGIN
 IF (SELECT estado FROM ventas WHERE id_venta=OLD.id_venta)='COMPLETADA' THEN
  UPDATE productos SET stock_actual=stock_actual+OLD.cantidad WHERE id_producto=OLD.id_producto;
 END IF;
 IF (SELECT estado FROM ventas WHERE id_venta=NEW.id_venta)='COMPLETADA' THEN
  UPDATE productos SET stock_actual=stock_actual-NEW.cantidad WHERE id_producto=NEW.id_producto;
 END IF;
 UPDATE ventas SET total=(SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas WHERE id_venta=OLD.id_venta) WHERE id_venta=OLD.id_venta;
 IF OLD.id_venta <> NEW.id_venta THEN
  UPDATE ventas SET total=(SELECT COALESCE(SUM(subtotal),0) FROM detalle_ventas WHERE id_venta=NEW.id_venta) WHERE id_venta=NEW.id_venta;
 END IF;
END$$
DELIMITER ;
