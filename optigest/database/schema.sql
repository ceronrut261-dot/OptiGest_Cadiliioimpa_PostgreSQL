-- =====================================================================
-- OptiGest - Esquema de base de datos (PostgreSQL 15+)
-- Sistema Web de Gestion Operativa - Constru Fontaneria Cadiliompa
--
-- Este script es un respaldo de referencia equivalente a las migraciones
-- de Laravel (backend/database/migrations). Para el uso normal del
-- proyecto se recomienda `php artisan migrate --seed`, que ademas crea
-- correctamente las tablas de roles y permisos (Spatie).
-- =====================================================================

CREATE DATABASE optigest WITH ENCODING 'UTF8';
-- \c optigest

-- ---------------------------------------------------------------------
-- Usuarios y autenticacion
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,          -- hash bcrypt, nunca texto plano
    telefono VARCHAR(20) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tokens de recuperacion de contrasena (Password Broker de Laravel)
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

-- ---------------------------------------------------------------------
-- Roles y permisos (Spatie Laravel-Permission)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE (name, guard_name)
);

CREATE TABLE permissions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE (name, guard_name)
);

CREATE TABLE model_has_roles (
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type)
);
CREATE INDEX model_has_roles_model_id_model_type_index ON model_has_roles (model_id, model_type);

CREATE TABLE model_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type)
);

CREATE TABLE role_has_permissions (
    permission_id BIGINT NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    role_id BIGINT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (permission_id, role_id)
);

-- ---------------------------------------------------------------------
-- Proveedores
-- ---------------------------------------------------------------------
CREATE TABLE proveedores (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    nit VARCHAR(20) NULL,
    contacto VARCHAR(255) NULL,
    telefono VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    direccion VARCHAR(255) NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ---------------------------------------------------------------------
-- Materiales (Control de Inventario)
-- ---------------------------------------------------------------------
CREATE TABLE materiales (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,          -- MAT-00001
    nombre VARCHAR(255) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descripcion TEXT NULL,
    precio NUMERIC(10,2) NOT NULL DEFAULT 0,
    stock INTEGER NOT NULL DEFAULT 0,
    stock_minimo INTEGER NOT NULL DEFAULT 10,     -- umbral de alerta
    unidad_medida VARCHAR(30) NOT NULL DEFAULT 'unidad',
    proveedor_id BIGINT NULL REFERENCES proveedores(id) ON DELETE SET NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX materiales_categoria_index ON materiales (categoria);
CREATE INDEX materiales_stock_stock_minimo_index ON materiales (stock, stock_minimo);

-- ---------------------------------------------------------------------
-- Movimientos de inventario (trazabilidad de entradas/salidas)
-- ---------------------------------------------------------------------
CREATE TABLE movimientos_inventario (
    id BIGSERIAL PRIMARY KEY,
    material_id BIGINT NOT NULL REFERENCES materiales(id) ON DELETE CASCADE,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('entrada', 'salida')),
    cantidad INTEGER NOT NULL,
    motivo VARCHAR(255) NULL,
    fecha TIMESTAMP NOT NULL,
    usuario_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    cotizacion_id BIGINT NULL,   -- FK agregada tras crear tabla cotizaciones
    stock_resultante INTEGER NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX movimientos_material_fecha_index ON movimientos_inventario (material_id, fecha);
CREATE INDEX movimientos_tipo_index ON movimientos_inventario (tipo);

-- ---------------------------------------------------------------------
-- Tickets de servicio tecnico
-- ---------------------------------------------------------------------
CREATE TABLE tickets (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,           -- TKT-00001
    cliente VARCHAR(255) NOT NULL,
    telefono_cliente VARCHAR(20) NULL,
    direccion VARCHAR(255) NULL,
    descripcion TEXT NOT NULL,
    prioridad VARCHAR(10) NOT NULL DEFAULT 'media' CHECK (prioridad IN ('baja','media','alta','urgente')),
    estado VARCHAR(15) NOT NULL DEFAULT 'pendiente'
        CHECK (estado IN ('pendiente','asignado','en_proceso','completado','cancelado')),
    tecnico_id BIGINT NULL REFERENCES users(id) ON DELETE SET NULL,
    fecha_programada TIMESTAMP NULL,
    fecha_completado TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX tickets_estado_index ON tickets (estado);

-- ---------------------------------------------------------------------
-- Cotizaciones (integradas con inventario)
-- ---------------------------------------------------------------------
CREATE TABLE cotizaciones (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,           -- COT-00001
    cliente VARCHAR(255) NOT NULL,
    ticket_id BIGINT NULL REFERENCES tickets(id) ON DELETE SET NULL,
    cotizador_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    estado VARCHAR(10) NOT NULL DEFAULT 'borrador'
        CHECK (estado IN ('borrador','enviada','aprobada','rechazada')),
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    total NUMERIC(12,2) NOT NULL DEFAULT 0,
    fecha TIMESTAMP NOT NULL,
    observaciones TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX cotizaciones_estado_index ON cotizaciones (estado);

ALTER TABLE movimientos_inventario
    ADD CONSTRAINT movimientos_cotizacion_fk
    FOREIGN KEY (cotizacion_id) REFERENCES cotizaciones(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------------
-- Detalle de materiales por cotizacion
-- ---------------------------------------------------------------------
CREATE TABLE cotizacion_detalle (
    id BIGSERIAL PRIMARY KEY,
    cotizacion_id BIGINT NOT NULL REFERENCES cotizaciones(id) ON DELETE CASCADE,
    material_id BIGINT NOT NULL REFERENCES materiales(id) ON DELETE RESTRICT,
    cantidad INTEGER NOT NULL,
    precio_unitario NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- ---------------------------------------------------------------------
-- Historial del Asistente IA
-- ---------------------------------------------------------------------
CREATE TABLE conversaciones_ia (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pregunta TEXT NOT NULL,
    respuesta TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
