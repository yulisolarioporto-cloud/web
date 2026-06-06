CREATE DATABASE IF NOT EXISTS TESTDELATEL1;

USE TESTDELATEL1;


DROP TABLE IF EXISTS descuentos;
CREATE TABLE descuentos (
  id_descuento INT(11) NOT NULL AUTO_INCREMENT,
  codigo VARCHAR(50) DEFAULT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT DEFAULT NULL,	
  tipo_descuento ENUM('PORCENTAJE','MONTO_FIJO') NOT NULL,
  valor DECIMAL(12,2) NOT NULL,
  estado ENUM('ACTIVO','INACTIVO','AGOTADO') DEFAULT 'ACTIVO',
  fecha_inicio DATE DEFAULT NULL,
  fecha_fin DATE DEFAULT NULL,
  prioridad INT(11) DEFAULT 100,
  acumulable TINYINT(1) DEFAULT 0,
  usos_max_total INT(10) UNSIGNED DEFAULT NULL,
  usos_max_por_venta INT(10) UNSIGNED DEFAULT NULL,
  usos_realizados INT(10) UNSIGNED DEFAULT 0,
  create_at DATETIME DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL ON UPDATE current_timestamp(),
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,

  PRIMARY KEY(id_descuento)
);

DROP TABLE IF EXISTS descuento_compatibilidad;
CREATE TABLE descuento_compatibilidad (
  id_descuento INT(11) NOT NULL,
  id_descuento_compatible INT(11) NOT NULL
);

DROP TABLE IF EXISTS condicion;
CREATE TABLE condicion (
  id_condicion INT(11) NOT NULL AUTO_INCREMENT,
  id_descuento INT(11) NOT NULL,
  id_tipo_condicion INT(11) NOT NULL,
  valor_id INT(10) UNSIGNED DEFAULT NULL,
  valor_texto VARCHAR(255) DEFAULT NULL,
  valor_numerico DECIMAL(12,2) DEFAULT NULL,
  cantidad_min DECIMAL(12,2) DEFAULT NULL,
  create_at DATETIME DEFAULT current_timestamp(),

  PRIMARY KEY(id_condicion)
);

DROP TABLE IF EXISTS oferta;
CREATE TABLE oferta (
  id_oferta INT(11) NOT NULL AUTO_INCREMENT,
  id_descuento INT(11) NOT NULL,
  id_tipo_oferta INT(11) NOT NULL,
  valor VARCHAR(150) DEFAULT NULL,
  descripcion VARCHAR(250) DEFAULT NULL,

  PRIMARY KEY(id_oferta)
);

DROP TABLE IF EXISTS tipo_condicion;
CREATE TABLE tipo_condicion (
  id_tipo_condicion INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(60) NOT NULL,
  descripcion VARCHAR(200) DEFAULT NULL,
  tipo_valor ENUM('ID','TEXTO','NUMERICO','NINGUNO') NOT NULL,
  estado ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  orden INT(11) DEFAULT 100,
  create_at DATETIME DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL ON UPDATE current_timestamp(),

  PRIMARY KEY(id_tipo_condicion)
);

DROP TABLE IF EXISTS tipo_oferta;
CREATE TABLE tipo_oferta (
  id_tipo_oferta INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(60) NOT NULL,
  descripcion VARCHAR(200) DEFAULT NULL,
  requiere_valor TINYINT(1) DEFAULT 0,
  estado ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  create_at DATETIME DEFAULT current_timestamp(),

  PRIMARY KEY(id_tipo_oferta)
);


DROP TABLE IF EXISTS tb_almacen;
CREATE TABLE tb_almacen (
  id_almacen INT(11) NOT NULL AUTO_INCREMENT,
  nombre_almacen VARCHAR(65) NOT NULL,
  ubicacion VARCHAR(120) NOT NULL,
  coordenada VARCHAR(50) NOT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,

  PRIMARY KEY(id_almacen)
);


DROP TABLE IF EXISTS tb_detalle_venta;
CREATE TABLE tb_detalle_venta (
  id_detalle INT(11) NOT NULL AUTO_INCREMENT,
  id_venta INT(11) NOT NULL,
  id_producto INT(11) NOT NULL,
  cantidad INT(11) NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  precio_final DECIMAL(10,2) NOT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,

  PRIMARY KEY(id_detalle)
);


DROP TABLE IF EXISTS tb_kardex;
CREATE TABLE tb_kardex (
  id_kardex INT(11) NOT NULL AUTO_INCREMENT,
  id_producto INT(11) NOT NULL,
  id_almacen INT(11) NOT NULL,
  id_tipooperacion INT(11) NOT NULL,
  fecha DATE NOT NULL,
  cantidad INT(11) UNSIGNED NOT NULL,
  saldo_total INT(11) UNSIGNED NOT NULL,
  valor_unico_historico DECIMAL(7,2) NOT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,

  PRIMARY KEY(id_kardex)
);



DROP TABLE IF EXISTS tb_categorias;
CREATE TABLE tb_categorias(
  id_categoria INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL ON UPDATE current_timestamp(),
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,

  PRIMARY KEY(id_categoria)
);



DROP TABLE IF EXISTS tb_roles;
CREATE TABLE tb_roles (
  id_rol INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  descripcion VARCHAR(150) DEFAULT NULL,
  nivel INT(11) NOT NULL COMMENT '1=mayor jerarquia, mayor numero=menos privilegios',
  PRIMARY KEY(id_rol)
);
 
DROP TABLE IF EXISTS tb_usuarios;
CREATE TABLE tb_usuarios (
  id_usuario INT(11) NOT NULL AUTO_INCREMENT,
  id_rol INT(11) NOT NULL,
  nombre VARCHAR(80) NOT NULL,
  apellido VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL COMMENT 'Usar password_hash()',
  estado ENUM('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL ON UPDATE current_timestamp(),
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) DEFAULT NULL,
  iduser_update INT(11) DEFAULT NULL,
  PRIMARY KEY(id_usuario),
  FOREIGN KEY (id_rol) REFERENCES tb_roles(id_rol)
);
 
DROP TABLE IF EXISTS tb_permisos;
CREATE TABLE tb_permisos (
  id_permiso INT(11) NOT NULL AUTO_INCREMENT,
  id_rol INT(11) NOT NULL,
  modulo VARCHAR(50) NOT NULL COMMENT 'productos, kardex, ventas, historial, usuarios',
  puede_ver TINYINT(1) DEFAULT 0,
  puede_crear TINYINT(1) DEFAULT 0,
  puede_editar TINYINT(1) DEFAULT 0,
  puede_eliminar TINYINT(1) DEFAULT 0,
  PRIMARY KEY(id_permiso),
  FOREIGN KEY (id_rol) REFERENCES tb_roles(id_rol)
);


DROP TABLE IF EXISTS tb_marca;
CREATE TABLE tb_marca (
  id_marca INT(11) NOT NULL AUTO_INCREMENT,
  marca VARCHAR(30) NOT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,

  PRIMARY KEY(id_marca)
);


DROP TABLE IF EXISTS tb_unidad;
CREATE TABLE tb_unidad(
  id_unidad INT(11) NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  tipo_medida VARCHAR(50) NOT NULL,

  PRIMARY KEY(id_unidad)
);



DROP TABLE IF EXISTS tb_productos;
CREATE TABLE tb_productos (
  id_producto INT(11) NOT NULL AUTO_INCREMENT,
  id_marca INT(11) NOT NULL,
  id_tipo INT(11) NOT NULL,
  id_unidad INT(11) NOT NULL,
  modelo VARCHAR(70) NOT NULL,
  precio_actual DECIMAL(7,2) NOT NULL,
  codigo_barra VARCHAR(120) DEFAULT NULL,
  id_categoria INT(11) DEFAULT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  imagen VARCHAR(185) DEFAULT NULL,
  SeVende CHAR(1) DEFAULT '0',
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,

  PRIMARY KEY(id_producto)
);


DROP TABLE IF EXISTS tb_tipooperacion;
CREATE TABLE tb_tipooperacion (
  id_tipooperacion INT(11) NOT NULL AUTO_INCREMENT,
  descripcion VARCHAR(55) NOT NULL,
  movimiento CHAR(1) NOT NULL,

  PRIMARY KEY(id_tipooperacion)
);



DROP TABLE IF EXISTS tb_tipoproducto;
CREATE TABLE tb_tipoproducto (
  id_tipo INT(11) NOT NULL AUTO_INCREMENT,
  tipo_nombre VARCHAR(250) NOT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,

  PRIMARY KEY(id_tipo)
);



DROP TABLE IF EXISTS tb_tipoprogramacion;
CREATE TABLE tb_tipoprogramacion (
  id_tipoprogramacion INT(11) NOT NULL AUTO_INCREMENT,
  descripcion VARCHAR(75) NOT NULL,
  create_at DATETIME NOT NULL DEFAULT current_timestamp(),
  update_at DATETIME DEFAULT NULL,
  inactive_at DATETIME DEFAULT NULL,
  iduser_create INT(11) NOT NULL DEFAULT 1,
  iduser_update INT(11) DEFAULT NULL,
  iduser_inactive INT(11) DEFAULT NULL,
  color VARCHAR(7) DEFAULT NULL,

  PRIMARY KEY(id_tipoprogramacion)
);


DROP TABLE IF EXISTS tb_ventas;
CREATE TABLE tb_ventas (
  id_venta INT(11) NOT NULL AUTO_INCREMENT,
  fecha DATETIME NOT NULL,
  observaciones TEXT DEFAULT NULL,
  titulo VARCHAR(60) DEFAULT NULL,
  total DECIMAL(10,2) DEFAULT 0.00,
  iduser_create INT(11) NOT NULL,
  iduser_update INT(11) DEFAULT NULL,

  PRIMARY KEY(id_venta)
);


DROP TABLE IF EXISTS venta_descuentos;
CREATE TABLE venta_descuentos (
  id_venta INT(11) NOT NULL,
  id_descuento INT(11) NOT NULL,
  codigo_usado VARCHAR(50) DEFAULT NULL,
  monto_aplicado DECIMAL(12,2) NOT NULL,
  porcentaje_efectivo DECIMAL(5,2) DEFAULT NULL,
  orden_aplicacion TINYINT(3) NOT NULL,
  create_at DATETIME DEFAULT current_timestamp()
);

USE TESTDELATEL1;

DROP PROCEDURE IF EXISTS sp_entrada;
DROP PROCEDURE IF EXISTS sp_salida;

DELIMITER //

CREATE PROCEDURE sp_entrada(
    IN p_id_producto INT,
    IN p_id_almacen INT,
    IN p_cantidad INT,
    IN p_valor DECIMAL(7,2)
)
BEGIN
    DECLARE v_saldo INT DEFAULT 0;
    SELECT saldo_total INTO v_saldo
    FROM tb_kardex
    WHERE id_producto = p_id_producto
    ORDER BY id_kardex DESC
    LIMIT 1;
    IF v_saldo IS NULL THEN
        SET v_saldo = 0;
    END IF;
    INSERT INTO tb_kardex (id_producto, id_almacen, id_tipooperacion, fecha, cantidad, saldo_total, valor_unico_historico, iduser_create)
    VALUES (p_id_producto, p_id_almacen, 1, CURDATE(), p_cantidad, v_saldo + p_cantidad, p_valor, 1);
END //

CREATE PROCEDURE sp_salida(
    IN p_id_producto INT,
    IN p_id_almacen INT,
    IN p_cantidad INT,
    IN p_valor DECIMAL(7,2)
)
BEGIN
    DECLARE v_saldo INT DEFAULT 0;
    SELECT saldo_total INTO v_saldo
    FROM tb_kardex
    WHERE id_producto = p_id_producto
    ORDER BY id_kardex DESC
    LIMIT 1;
    IF v_saldo IS NULL THEN
        SET v_saldo = 0;
    END IF;
    IF p_cantidad > v_saldo THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stock insuficiente';
    ELSE
        INSERT INTO tb_kardex (id_producto, id_almacen, id_tipooperacion, fecha, cantidad, saldo_total, valor_unico_historico, iduser_create)
        VALUES (p_id_producto, p_id_almacen, 2, CURDATE(), p_cantidad, v_saldo - p_cantidad, p_valor, 1);
    END IF;
END //

DELIMITER ;

SHOW PROCEDURE STATUS WHERE Db = 'TESTDELATEL1';

SELECT id_producto, saldo_total FROM tb_kardex ORDER BY id_kardex DESC LIMIT 5;
 


-- Marcas
INSERT INTO tb_marca (id_marca, marca, iduser_create) VALUES
(1, 'Samsung', 1),
(2, 'LG', 1),
(3, 'HP', 1),
(4, 'Sony', 1),
(5, 'Lenovo', 1);

-- Categorias
INSERT INTO tb_categorias (id_categoria, nombre, descripcion, iduser_create) VALUES
(1, 'Laptops', 'Equipos portátiles', 1),
(2, 'Televisores', 'Pantallas Smart TV', 1),
(3, 'Electrodomésticos', 'Productos para hogar', 1),
(4, 'Celulares', 'Teléfonos inteligentes', 1),
(5, 'Tablets', 'Dispositivos táctiles', 1);

-- Unidad
INSERT INTO tb_unidad (id_unidad, nombre, tipo_medida) VALUES
(1, 'Unidad', 'UND'),
(2, 'Caja', 'UND'),
(3, 'Paquete', 'UND'),
(4, 'Par', 'UND'),
(5, 'Lote', 'UND');

-- Tipo producto
INSERT INTO tb_tipoproducto (id_tipo, tipo_nombre, iduser_create) VALUES
(1, 'Laptop', 1),
(2, 'Televisor', 1),
(3, 'Refrigeradora', 1),
(4, 'Celular', 1),
(5, 'Tablet', 1);

-- Productos
INSERT INTO tb_productos (id_producto, id_marca, id_tipo, id_unidad, modelo, precio_actual, codigo_barra, id_categoria, descripcion, SeVende, iduser_create) VALUES
(1, 1, 1, 1, 'Samsung Book 4', 3500.00, '779845120001', 1, 'Laptop Core i5 16GB RAM', '1', 1),
(2, 2, 2, 1, 'LG Smart TV 55', 2800.00, '779845120002', 2, 'Televisor 4K UHD', '1', 1),
(3, 3, 1, 1, 'HP Victus', 4200.00, '779845120003', 1, 'Laptop Gamer RTX 4050', '1', 1),
(4, 4, 4, 1, 'Sony Xperia 5', 1800.00, '779845120004', 4, 'Celular 5G OLED', '1', 1),
(5, 5, 1, 1, 'Lenovo IdeaPad 3', 2600.00, '779845120005', 1, 'Laptop Core i3 8GB RAM', '1', 1);

-- Almacen
INSERT INTO tb_almacen (id_almacen, nombre_almacen, ubicacion, coordenada, iduser_create) VALUES
(1, 'Almacén Principal', 'Lima Centro', '-12.0464,-77.0428', 1),
(2, 'Almacén Norte', 'Los Olivos', '-11.9895,-77.0705', 1),
(3, 'Almacén Sur', 'Chorrillos', '-12.1628,-77.0197', 1),
(4, 'Almacén Este', 'Ate Vitarte', '-12.0261,-76.9189', 1),
(5, 'Almacén Oeste', 'Callao', '-12.0565,-77.1194', 1);

-- Tipo operacion
INSERT INTO tb_tipooperacion (id_tipooperacion, descripcion, movimiento) VALUES
(1, 'Entrada de producto', 'E'),
(2, 'Salida de producto', 'S'),
(3, 'Devolución', 'E'),
(4, 'Ajuste positivo', 'E'),
(5, 'Ajuste negativo', 'S');

-- Kardex
INSERT INTO tb_kardex (id_kardex, id_producto, id_almacen, id_tipooperacion, fecha, cantidad, saldo_total, valor_unico_historico, iduser_create) VALUES
(1, 1, 1, 1, CURDATE(), 10, 10, 3500.00, 1),
(2, 2, 1, 1, CURDATE(), 5, 5, 2800.00, 1),
(3, 1, 1, 1, CURDATE(), 8, 18, 3200.00, 1),
(4, 3, 2, 1, CURDATE(), 6, 6, 4200.00, 1),
(5, 4, 3, 1, CURDATE(), 12, 12, 1800.00, 1);

-- Descuentos
INSERT INTO descuentos (id_descuento, codigo, nombre, descripcion, tipo_descuento, valor, estado, iduser_create) VALUES
(1, 'PROMO10', 'Descuento 10%', 'Descuento general', 'PORCENTAJE', 10.00, 'ACTIVO', 1),
(2, 'FIJO50', 'Descuento 50 soles', 'Promoción especial', 'MONTO_FIJO', 50.00, 'ACTIVO', 1),
(3, 'VERANO20', 'Descuento verano', 'Promo verano', 'PORCENTAJE', 20.00, 'ACTIVO', 1),
(4, 'FIJO100', 'Descuento 100 soles', 'Promo navidad', 'MONTO_FIJO', 100.00, 'ACTIVO', 1),
(5, 'CYBER15', 'Cyber descuento', 'Cyber Monday', 'PORCENTAJE', 15.00, 'ACTIVO', 1);






UPDATE tb_productos SET imagen='SamBook.jpg' WHERE id_producto=1;

UPDATE tb_productos SET imagen='LgTV.jpeg' WHERE id_producto=2;

UPDATE tb_productos SET imagen='HpVictus.jpg' WHERE id_producto=3;

UPDATE tb_productos SET imagen='sonyx.jpg' WHERE id_producto=4;

UPDATE tb_productos SET imagen='Lenovo.jpg' WHERE id_producto=5;

UPDATE tb_productos SET imagen='Asus.jpg' WHERE id_producto=6;

UPDATE tb_productos SET imagen='logi.jpg' WHERE id_producto=7;

UPDATE tb_productos SET imagen='S24.jpg' WHERE id_producto=8;

UPDATE tb_productos SET imagen='iphone.jpg' WHERE id_producto=9;

UPDATE tb_productos SET imagen='imac.jpg' WHERE id_producto=10;

UPDATE tb_productos SET imagen='play5.jpg' WHERE id_producto=11;







SELECT * FROM tb_productos

SELECT * FROM tb_ventas

DESCRIBE tb_almacen;
