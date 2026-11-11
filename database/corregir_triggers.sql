-- RESPALDAR AIVEN ANTES DE EJECUTAR.
-- La baja de productos/categorías relacionados es lógica (activo=0).
DELIMITER $$

DROP PROCEDURE IF EXISTS sp_categorias_eliminar$$
CREATE PROCEDURE sp_categorias_eliminar(IN p_id INT)
BEGIN
 IF EXISTS (SELECT 1 FROM productos WHERE id_categoria=p_id LIMIT 1) THEN
  UPDATE categorias SET activo=0 WHERE id_categoria=p_id;
 ELSE
  DELETE FROM categorias WHERE id_categoria=p_id;
 END IF;
END$$

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

DROP PROCEDURE IF EXISTS sp_ventas_eliminar$$
CREATE PROCEDURE sp_ventas_eliminar(IN p_id INT)
BEGIN
 DECLARE EXIT HANDLER FOR SQLEXCEPTION BEGIN ROLLBACK; RESIGNAL; END;
 START TRANSACTION;
 DELETE FROM detalle_ventas WHERE id_venta=p_id;
 DELETE FROM ventas WHERE id_venta=p_id;
 COMMIT;
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
UPDATE ventas v SET total=(SELECT COALESCE(SUM(d.subtotal),0) FROM detalle_ventas d WHERE d.id_venta=v.id_venta);
