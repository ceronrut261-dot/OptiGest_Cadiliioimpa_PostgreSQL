-- =====================================================================
-- OptiGest - Datos iniciales (PostgreSQL)
-- Crea los roles y datos de demostracion (proveedores y materiales).
-- No crea usuarios: el administrador se crea con una contrasena propia
-- (ver instrucciones de instalacion). Se puede ejecutar mas de una vez.
-- =====================================================================

INSERT INTO roles (name, guard_name, created_at, updated_at)
SELECT v.n, 'web', NOW(), NOW()
FROM (VALUES ('administrador'), ('tecnico'), ('cotizador')) AS v(n)
WHERE NOT EXISTS (SELECT 1 FROM roles r WHERE r.name = v.n AND r.guard_name = 'web');

INSERT INTO proveedores (nombre, nit, contacto, telefono, email, direccion, created_at, updated_at)
SELECT v.nombre, v.nit, v.contacto, v.telefono, v.email, v.direccion, NOW(), NOW()
FROM (VALUES
    ('Ferreteria El Tornillo, S.A.', '1234567-8', 'Carlos Lopez', '5511-2233', 'ventas@eltornillo.gt', 'Zona 1, Ciudad de Guatemala'),
    ('Tuberias y Conexiones de Guatemala', '9876543-2', 'Maria Sosa', '4422-9988', 'contacto@tuconexiones.gt', 'Mixco, Guatemala')
) AS v(nombre, nit, contacto, telefono, email, direccion)
WHERE NOT EXISTS (SELECT 1 FROM proveedores p WHERE p.nombre = v.nombre);

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00001', 'Tuberia PVC 1/2" x 6m', 'Tuberia', 35.00, 80, 15, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala'
AND NOT EXISTS (SELECT 1 FROM materiales m WHERE m.codigo = 'MAT-00001');

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00002', 'Codo PVC 90 grados 1/2"', 'Accesorios', 2.50, 200, 30, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala'
AND NOT EXISTS (SELECT 1 FROM materiales m WHERE m.codigo = 'MAT-00002');

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00003', 'Llave de paso 1/2"', 'Valvulas', 45.00, 25, 10, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Ferreteria El Tornillo, S.A.'
AND NOT EXISTS (SELECT 1 FROM materiales m WHERE m.codigo = 'MAT-00003');

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00004', 'Bomba de agua 1/2 HP', 'Bombeo', 950.00, 3, 4, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala'
AND NOT EXISTS (SELECT 1 FROM materiales m WHERE m.codigo = 'MAT-00004');