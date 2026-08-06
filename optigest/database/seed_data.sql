-- =====================================================================
-- OptiGest - Datos iniciales (PostgreSQL)
-- Roles y usuarios de prueba. Las contrasenas se insertan ya cifradas
-- con bcrypt equivalentes a "password" (usar solo para pruebas/demo).
--
-- Recomendado: ejecutar mejor `php artisan migrate --seed`, que genera
-- los hashes bcrypt de forma segura en tiempo de ejecucion. Este script
-- es solo un respaldo de referencia.
-- =====================================================================

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
    ('administrador', 'web', NOW(), NOW()),
    ('tecnico', 'web', NOW(), NOW()),
    ('cotizador', 'web', NOW(), NOW());

-- Hash bcrypt de ejemplo para la contrasena "password"
-- (generar uno nuevo en produccion: nunca reutilizar este hash)
INSERT INTO users (name, email, password, email_verified_at, activo, created_at, updated_at) VALUES
    ('Administrador OptiGest', 'admin@cadiliompa.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ==placeholder', NOW(), TRUE, NOW(), NOW()),
    ('Tecnico de Campo', 'tecnico@cadiliompa.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ==placeholder', NOW(), TRUE, NOW(), NOW()),
    ('Cotizador', 'cotizador@cadiliompa.com', '$2y$12$eImiTXuWVxfM37uY4JANjQ==placeholder', NOW(), TRUE, NOW(), NOW());

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\Models\User', u.id FROM roles r, users u
WHERE r.name = 'administrador' AND u.email = 'admin@cadiliompa.com';

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\Models\User', u.id FROM roles r, users u
WHERE r.name = 'tecnico' AND u.email = 'tecnico@cadiliompa.com';

INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT r.id, 'App\Models\User', u.id FROM roles r, users u
WHERE r.name = 'cotizador' AND u.email = 'cotizador@cadiliompa.com';

-- Proveedores de ejemplo
INSERT INTO proveedores (nombre, nit, contacto, telefono, email, direccion, created_at, updated_at) VALUES
    ('Ferreteria El Tornillo, S.A.', '1234567-8', 'Carlos Lopez', '5511-2233', 'ventas@eltornillo.gt', 'Zona 1, Ciudad de Guatemala', NOW(), NOW()),
    ('Tuberias y Conexiones de Guatemala', '9876543-2', 'Maria Sosa', '4422-9988', 'contacto@tuconexiones.gt', 'Mixco, Guatemala', NOW(), NOW());

-- Materiales de ejemplo
INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00001', 'Tuberia PVC 1/2" x 6m', 'Tuberia', 35.00, 80, 15, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala';

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00002', 'Codo PVC 90 grados 1/2"', 'Accesorios', 2.50, 200, 30, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala';

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00003', 'Llave de paso 1/2"', 'Valvulas', 45.00, 25, 10, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Ferreteria El Tornillo, S.A.';

INSERT INTO materiales (codigo, nombre, categoria, precio, stock, stock_minimo, unidad_medida, proveedor_id, created_at, updated_at)
SELECT 'MAT-00004', 'Bomba de agua 1/2 HP', 'Bombeo', 950.00, 3, 4, 'unidad', p.id, NOW(), NOW()
FROM proveedores p WHERE p.nombre = 'Tuberias y Conexiones de Guatemala';
