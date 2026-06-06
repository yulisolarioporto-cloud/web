USE TESTDELATEL1;

INSERT INTO tb_roles (id_rol, nombre, descripcion, nivel)
VALUES
(1, 'Administrador', 'Acceso total', 1),
(2, 'Vendedor', 'Acceso ventas', 2),
(3, 'Almacenero', 'Acceso inventario', 3)
ON DUPLICATE KEY UPDATE
nombre = VALUES(nombre),
descripcion = VALUES(descripcion),
nivel = VALUES(nivel);

INSERT INTO tb_usuarios (id_rol, nombre, apellido, email, usuario, password, estado)
VALUES (1, 'Yuliana', 'Solari', 'yuliana@gmail.com', 'yuliana', '123', 'ACTIVO')
ON DUPLICATE KEY UPDATE id_rol = 1, password = '123';

INSERT INTO tb_usuarios (id_rol, nombre, apellido, email, usuario, password, estado)
VALUES (1, 'Brayan', 'Administrador', 'brayan@gmail.com', 'brayan', '123', 'ACTIVO')
ON DUPLICATE KEY UPDATE id_rol = 1, password = '123';

INSERT INTO tb_usuarios (id_rol, nombre, apellido, email, usuario, password, estado)
VALUES (2, 'Sandro', 'Vendedor', 'sandro@gmail.com', 'sandro', '123', 'ACTIVO')
ON DUPLICATE KEY UPDATE id_rol = 2, password = '123';

INSERT INTO tb_usuarios (id_rol, nombre, apellido, email, usuario, password, estado)
VALUES (3, 'Daniel', 'Almacen', 'daniel@gmail.com', 'daniel', '123', 'ACTIVO')
ON DUPLICATE KEY UPDATE id_rol = 3, password = '123';

DELETE FROM tb_permisos;

INSERT INTO tb_permisos (id_rol, modulo, puede_ver, puede_crear, puede_editar, puede_eliminar)
VALUES
(1, 'productos', 1, 1, 1, 1),
(1, 'ventas',    1, 1, 1, 1),
(1, 'kardex',    1, 1, 1, 1),
(1, 'historial', 1, 1, 1, 1),
(1, 'usuarios',  1, 1, 1, 1),

(2, 'productos', 1, 0, 0, 0),
(2, 'ventas',    1, 1, 0, 0),
(2, 'historial', 1, 0, 0, 0),

(3, 'productos', 1, 1, 1, 0),
(3, 'kardex',    1, 1, 1, 0),
(3, 'historial', 1, 0, 0, 0);

-- Datos de almacenes (requeridos para el Kardex)
INSERT INTO tb_almacen (id_almacen, nombre_almacen, ubicacion, coordenada, iduser_create)
VALUES
(1, 'Almacén Principal', 'Lima Centro',  '-12.0464,-77.0428', 1),
(2, 'Almacén Norte',     'Los Olivos',   '-11.9895,-77.0705', 1),
(3, 'Almacén Sur',       'Chorrillos',   '-12.1628,-77.0197', 1),
(4, 'Almacén Este',      'Ate Vitarte',  '-12.0261,-76.9189', 1),
(5, 'Almacén Oeste',     'Callao',       '-12.0565,-77.1194', 1)
ON DUPLICATE KEY UPDATE nombre_almacen = VALUES(nombre_almacen);
