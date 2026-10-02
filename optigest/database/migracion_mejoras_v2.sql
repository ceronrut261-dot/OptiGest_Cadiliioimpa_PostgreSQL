-- =====================================================================
-- OptiGest - Mejoras v2 (PostgreSQL 15+)
--   1. Catálogo de servicios con precio estandarizado de mano de obra
--   2. Varios técnicos por ticket, con pago pactado "por trato"
--   3. Gastos adicionales del ticket con comprobante (foto / PDF)
--   4. Cotización con mano de obra, gastos adicionales e IVA opcional
-- Es idempotente: se puede ejecutar más de una vez sin dañar datos.
-- =====================================================================
BEGIN;

-- 1. Catálogo de servicios (mano de obra estandarizada) -----------------
CREATE TABLE IF NOT EXISTS catalogo_servicios (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,            -- SRV-00001
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    categoria VARCHAR(100) NULL,
    unidad VARCHAR(30) NOT NULL DEFAULT 'servicio', -- servicio, hora, punto, metro...
    precio_estandar NUMERIC(10,2) NOT NULL DEFAULT 0,
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- 2. Equipo de técnicos por ticket (pago por trato) ---------------------
CREATE TABLE IF NOT EXISTS ticket_tecnicos (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rol VARCHAR(15) NOT NULL DEFAULT 'apoyo' CHECK (rol IN ('responsable','apoyo')),
    monto_trato NUMERIC(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE (ticket_id, user_id)
);
CREATE INDEX IF NOT EXISTS ticket_tecnicos_user_index ON ticket_tecnicos (user_id);

-- Los tickets existentes conservan a su técnico como responsable
INSERT INTO ticket_tecnicos (ticket_id, user_id, rol, monto_trato, created_at, updated_at)
SELECT t.id, t.tecnico_id, 'responsable', 0, NOW(), NOW()
FROM tickets t
WHERE t.tecnico_id IS NOT NULL
ON CONFLICT (ticket_id, user_id) DO NOTHING;

-- 3. Gastos adicionales del ticket + comprobante ------------------------
CREATE TABLE IF NOT EXISTS cotizacion_servicios (
    id BIGSERIAL PRIMARY KEY,
    cotizacion_id BIGINT NOT NULL REFERENCES cotizaciones(id) ON DELETE CASCADE,
    servicio_id BIGINT NULL REFERENCES catalogo_servicios(id) ON DELETE SET NULL,
    descripcion VARCHAR(255) NOT NULL,
    cantidad NUMERIC(8,2) NOT NULL DEFAULT 1,
    precio_unitario NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(12,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE IF NOT EXISTS ticket_gastos (
    id BIGSERIAL PRIMARY KEY,
    ticket_id BIGINT NOT NULL REFERENCES tickets(id) ON DELETE CASCADE,
    salida_id BIGINT NULL REFERENCES salidas_materiales(id) ON DELETE SET NULL,
    cotizacion_id BIGINT NULL REFERENCES cotizaciones(id) ON DELETE SET NULL, -- cotización que ya lo cobra
    descripcion VARCHAR(255) NOT NULL,
    comercio VARCHAR(255) NULL,
    tipo_documento VARCHAR(20) NOT NULL DEFAULT 'factura'
        CHECK (tipo_documento IN ('factura','recibo','ticket_caja','otro','sin_comprobante')),
    numero_documento VARCHAR(60) NULL,
    monto NUMERIC(12,2) NOT NULL CHECK (monto >= 0),
    fecha_gasto DATE NOT NULL DEFAULT CURRENT_DATE,
    cobrar_al_cliente BOOLEAN NOT NULL DEFAULT TRUE,
    archivo_path VARCHAR(500) NULL,
    archivo_nombre VARCHAR(255) NULL,
    archivo_mime VARCHAR(100) NULL,
    registrado_por BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
CREATE INDEX IF NOT EXISTS ticket_gastos_ticket_index ON ticket_gastos (ticket_id);
CREATE INDEX IF NOT EXISTS ticket_gastos_cotizacion_index ON ticket_gastos (cotizacion_id);

-- 4. Cotizaciones: mano de obra, gastos adicionales e IVA ---------------
ALTER TABLE cotizaciones ADD COLUMN IF NOT EXISTS mano_obra_total NUMERIC(12,2) NOT NULL DEFAULT 0;
ALTER TABLE cotizaciones ADD COLUMN IF NOT EXISTS gastos_adicionales_total NUMERIC(12,2) NOT NULL DEFAULT 0;
ALTER TABLE cotizaciones ADD COLUMN IF NOT EXISTS iva_aplicado BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE cotizaciones ADD COLUMN IF NOT EXISTS iva_monto NUMERIC(12,2) NOT NULL DEFAULT 0;

COMMIT;
