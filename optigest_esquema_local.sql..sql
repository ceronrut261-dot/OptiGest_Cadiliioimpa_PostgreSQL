--
-- PostgreSQL database dump
--

\restrict tbwC4CndMexlFL5HTr14HohkKOVaftL6X2V8vIVcGNKcYKLnuGbWvIkS8xFsOkK

-- Dumped from database version 18.4
-- Dumped by pg_dump version 18.4

-- Started on 2026-10-31 14:02:26

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 267 (class 1259 OID 26103)
-- Name: auditoria_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.auditoria_logs (
    id bigint NOT NULL,
    user_id bigint,
    accion character varying(50) NOT NULL,
    modulo character varying(100) NOT NULL,
    registro_tipo character varying(150),
    registro_id bigint,
    descripcion text,
    valores_anteriores jsonb,
    valores_nuevos jsonb,
    ip_address character varying(45),
    user_agent text,
    created_at timestamp(0) with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.auditoria_logs OWNER TO postgres;

--
-- TOC entry 266 (class 1259 OID 26102)
-- Name: auditoria_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.auditoria_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.auditoria_logs_id_seq OWNER TO postgres;

--
-- TOC entry 5236 (class 0 OID 0)
-- Dependencies: 266
-- Name: auditoria_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.auditoria_logs_id_seq OWNED BY public.auditoria_logs.id;


--
-- TOC entry 219 (class 1259 OID 18553)
-- Name: catalogo_servicios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.catalogo_servicios (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(255) NOT NULL,
    descripcion text,
    categoria character varying(100),
    unidad character varying(30) DEFAULT 'servicio'::character varying NOT NULL,
    precio_estandar numeric(10,2) DEFAULT 0 NOT NULL,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.catalogo_servicios OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 18567)
-- Name: catalogo_servicios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.catalogo_servicios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.catalogo_servicios_id_seq OWNER TO postgres;

--
-- TOC entry 5237 (class 0 OID 0)
-- Dependencies: 220
-- Name: catalogo_servicios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.catalogo_servicios_id_seq OWNED BY public.catalogo_servicios.id;


--
-- TOC entry 221 (class 1259 OID 18568)
-- Name: clientes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clientes (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    telefono character varying(20),
    direccion character varying(255),
    email character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    activo boolean DEFAULT true NOT NULL
);


ALTER TABLE public.clientes OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 18577)
-- Name: clientes_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clientes_id_seq OWNER TO postgres;

--
-- TOC entry 5238 (class 0 OID 0)
-- Dependencies: 222
-- Name: clientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clientes_id_seq OWNED BY public.clientes.id;


--
-- TOC entry 223 (class 1259 OID 18578)
-- Name: conversaciones_ia; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.conversaciones_ia (
    id bigint NOT NULL,
    usuario_id bigint NOT NULL,
    pregunta text NOT NULL,
    respuesta text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.conversaciones_ia OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 18586)
-- Name: conversaciones_ia_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.conversaciones_ia_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.conversaciones_ia_id_seq OWNER TO postgres;

--
-- TOC entry 5239 (class 0 OID 0)
-- Dependencies: 224
-- Name: conversaciones_ia_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.conversaciones_ia_id_seq OWNED BY public.conversaciones_ia.id;


--
-- TOC entry 225 (class 1259 OID 18587)
-- Name: cotizacion_detalle; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cotizacion_detalle (
    id bigint NOT NULL,
    cotizacion_id bigint NOT NULL,
    material_id bigint NOT NULL,
    cantidad integer NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cotizacion_detalle OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 18596)
-- Name: cotizacion_detalle_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cotizacion_detalle_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cotizacion_detalle_id_seq OWNER TO postgres;

--
-- TOC entry 5240 (class 0 OID 0)
-- Dependencies: 226
-- Name: cotizacion_detalle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cotizacion_detalle_id_seq OWNED BY public.cotizacion_detalle.id;


--
-- TOC entry 227 (class 1259 OID 18597)
-- Name: cotizacion_servicios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cotizacion_servicios (
    id bigint NOT NULL,
    cotizacion_id bigint NOT NULL,
    servicio_id bigint,
    descripcion character varying(255) NOT NULL,
    cantidad numeric(8,2) DEFAULT 1 NOT NULL,
    precio_unitario numeric(10,2) NOT NULL,
    subtotal numeric(12,2) NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.cotizacion_servicios OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 18607)
-- Name: cotizacion_servicios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cotizacion_servicios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cotizacion_servicios_id_seq OWNER TO postgres;

--
-- TOC entry 5241 (class 0 OID 0)
-- Dependencies: 228
-- Name: cotizacion_servicios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cotizacion_servicios_id_seq OWNED BY public.cotizacion_servicios.id;


--
-- TOC entry 229 (class 1259 OID 18608)
-- Name: cotizaciones; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cotizaciones (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    ticket_id bigint,
    cotizador_id bigint NOT NULL,
    estado character varying(255) DEFAULT 'borrador'::character varying NOT NULL,
    subtotal numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    total numeric(12,2) DEFAULT '0'::numeric NOT NULL,
    fecha timestamp(0) without time zone NOT NULL,
    observaciones text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cliente_id bigint NOT NULL,
    mano_obra_total numeric(12,2) DEFAULT 0 NOT NULL,
    gastos_adicionales_total numeric(12,2) DEFAULT 0 NOT NULL,
    iva_aplicado boolean DEFAULT false NOT NULL,
    iva_monto numeric(12,2) DEFAULT 0 NOT NULL,
    CONSTRAINT cotizaciones_estado_check CHECK (((estado)::text = ANY (ARRAY[('borrador'::character varying)::text, ('enviada'::character varying)::text, ('aprobada'::character varying)::text, ('rechazada'::character varying)::text])))
);


ALTER TABLE public.cotizaciones OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 18633)
-- Name: cotizaciones_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cotizaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.cotizaciones_id_seq OWNER TO postgres;

--
-- TOC entry 5242 (class 0 OID 0)
-- Dependencies: 230
-- Name: cotizaciones_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cotizaciones_id_seq OWNED BY public.cotizaciones.id;


--
-- TOC entry 231 (class 1259 OID 18634)
-- Name: historial_precios_materiales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.historial_precios_materiales (
    id bigint NOT NULL,
    material_id bigint NOT NULL,
    precio_anterior numeric(10,2) NOT NULL,
    precio_nuevo numeric(10,2) NOT NULL,
    proveedor_id bigint,
    usuario_id bigint,
    motivo character varying(255),
    fecha timestamp without time zone DEFAULT now() NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.historial_precios_materiales OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 18643)
-- Name: historial_precios_materiales_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.historial_precios_materiales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.historial_precios_materiales_id_seq OWNER TO postgres;

--
-- TOC entry 5243 (class 0 OID 0)
-- Dependencies: 232
-- Name: historial_precios_materiales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.historial_precios_materiales_id_seq OWNED BY public.historial_precios_materiales.id;


--
-- TOC entry 233 (class 1259 OID 18644)
-- Name: materiales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.materiales (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(255) NOT NULL,
    categoria character varying(100) NOT NULL,
    descripcion text,
    precio numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    stock integer DEFAULT 0 NOT NULL,
    stock_minimo integer DEFAULT 10 NOT NULL,
    unidad_medida character varying(30) DEFAULT 'unidad'::character varying NOT NULL,
    proveedor_id bigint,
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    en_bodega boolean DEFAULT true NOT NULL
);


ALTER TABLE public.materiales OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 18665)
-- Name: materiales_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.materiales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.materiales_id_seq OWNER TO postgres;

--
-- TOC entry 5244 (class 0 OID 0)
-- Dependencies: 234
-- Name: materiales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.materiales_id_seq OWNED BY public.materiales.id;


--
-- TOC entry 235 (class 1259 OID 18666)
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 18672)
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- TOC entry 5245 (class 0 OID 0)
-- Dependencies: 236
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- TOC entry 237 (class 1259 OID 18673)
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 18679)
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 18685)
-- Name: movimientos_inventario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.movimientos_inventario (
    id bigint NOT NULL,
    material_id bigint NOT NULL,
    tipo character varying(255) NOT NULL,
    cantidad integer NOT NULL,
    motivo character varying(255),
    fecha timestamp(0) without time zone NOT NULL,
    usuario_id bigint NOT NULL,
    cotizacion_id bigint,
    stock_resultante integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    salida_id bigint,
    CONSTRAINT movimientos_inventario_tipo_check CHECK (((tipo)::text = ANY (ARRAY[('entrada'::character varying)::text, ('salida'::character varying)::text])))
);


ALTER TABLE public.movimientos_inventario OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 18698)
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.movimientos_inventario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.movimientos_inventario_id_seq OWNER TO postgres;

--
-- TOC entry 5246 (class 0 OID 0)
-- Dependencies: 240
-- Name: movimientos_inventario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.movimientos_inventario_id_seq OWNED BY public.movimientos_inventario.id;


--
-- TOC entry 241 (class 1259 OID 18699)
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 18706)
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- TOC entry 243 (class 1259 OID 18714)
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- TOC entry 5247 (class 0 OID 0)
-- Dependencies: 243
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- TOC entry 244 (class 1259 OID 18715)
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- TOC entry 245 (class 1259 OID 18725)
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- TOC entry 5248 (class 0 OID 0)
-- Dependencies: 245
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- TOC entry 246 (class 1259 OID 18726)
-- Name: precios_proveedor_material; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.precios_proveedor_material (
    id bigint NOT NULL,
    material_id bigint NOT NULL,
    proveedor_id bigint NOT NULL,
    precio numeric(10,2) NOT NULL,
    actualizado_en timestamp without time zone DEFAULT now() NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.precios_proveedor_material OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 18735)
-- Name: precios_proveedor_material_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.precios_proveedor_material_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.precios_proveedor_material_id_seq OWNER TO postgres;

--
-- TOC entry 5249 (class 0 OID 0)
-- Dependencies: 247
-- Name: precios_proveedor_material_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.precios_proveedor_material_id_seq OWNED BY public.precios_proveedor_material.id;


--
-- TOC entry 248 (class 1259 OID 18736)
-- Name: proveedores; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.proveedores (
    id bigint NOT NULL,
    nombre character varying(255) NOT NULL,
    nit character varying(20),
    contacto character varying(255),
    telefono character varying(20),
    email character varying(255),
    direccion character varying(255),
    activo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    descripcion text,
    nombre_contacto character varying(255)
);


ALTER TABLE public.proveedores OWNER TO postgres;

--
-- TOC entry 249 (class 1259 OID 18745)
-- Name: proveedores_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.proveedores_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.proveedores_id_seq OWNER TO postgres;

--
-- TOC entry 5250 (class 0 OID 0)
-- Dependencies: 249
-- Name: proveedores_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.proveedores_id_seq OWNED BY public.proveedores.id;


--
-- TOC entry 250 (class 1259 OID 18746)
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- TOC entry 251 (class 1259 OID 18751)
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- TOC entry 252 (class 1259 OID 18759)
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- TOC entry 5251 (class 0 OID 0)
-- Dependencies: 252
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- TOC entry 253 (class 1259 OID 18760)
-- Name: salida_material_detalle; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.salida_material_detalle (
    id bigint NOT NULL,
    salida_id bigint NOT NULL,
    material_id bigint NOT NULL,
    descripcion character varying(255) NOT NULL,
    cantidad integer NOT NULL,
    valor_unitario numeric(10,2) NOT NULL,
    total numeric(12,2) NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    cantidad_entregada integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.salida_material_detalle OWNER TO postgres;

--
-- TOC entry 254 (class 1259 OID 18770)
-- Name: salida_material_detalle_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.salida_material_detalle_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.salida_material_detalle_id_seq OWNER TO postgres;

--
-- TOC entry 5252 (class 0 OID 0)
-- Dependencies: 254
-- Name: salida_material_detalle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.salida_material_detalle_id_seq OWNED BY public.salida_material_detalle.id;


--
-- TOC entry 255 (class 1259 OID 18771)
-- Name: salidas_materiales; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.salidas_materiales (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    usuario_id bigint NOT NULL,
    proyecto character varying(255) NOT NULL,
    persona_recibe character varying(255) NOT NULL,
    ticket_id bigint,
    fecha timestamp without time zone NOT NULL,
    observaciones text,
    falta_comprar text,
    total numeric(12,2) DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    estado_entrega character varying(20) DEFAULT 'pendiente'::character varying NOT NULL,
    confirmado_por bigint,
    confirmado_en timestamp without time zone,
    observaciones_entrega text,
    salida_original_id bigint
);


ALTER TABLE public.salidas_materiales OWNER TO postgres;

--
-- TOC entry 256 (class 1259 OID 18784)
-- Name: salidas_materiales_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.salidas_materiales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.salidas_materiales_id_seq OWNER TO postgres;

--
-- TOC entry 5253 (class 0 OID 0)
-- Dependencies: 256
-- Name: salidas_materiales_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.salidas_materiales_id_seq OWNED BY public.salidas_materiales.id;


--
-- TOC entry 257 (class 1259 OID 18785)
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- TOC entry 258 (class 1259 OID 18793)
-- Name: ticket_gastos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ticket_gastos (
    id bigint NOT NULL,
    ticket_id bigint NOT NULL,
    salida_id bigint,
    cotizacion_id bigint,
    descripcion character varying(255) NOT NULL,
    comercio character varying(255),
    tipo_documento character varying(20) DEFAULT 'factura'::character varying NOT NULL,
    numero_documento character varying(60),
    monto numeric(12,2) NOT NULL,
    fecha_gasto date DEFAULT CURRENT_DATE NOT NULL,
    cobrar_al_cliente boolean DEFAULT true NOT NULL,
    archivo_path character varying(500),
    archivo_nombre character varying(255),
    archivo_mime character varying(100),
    registrado_por bigint NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT ticket_gastos_monto_check CHECK ((monto >= (0)::numeric)),
    CONSTRAINT ticket_gastos_tipo_documento_check CHECK (((tipo_documento)::text = ANY (ARRAY[('factura'::character varying)::text, ('recibo'::character varying)::text, ('ticket_caja'::character varying)::text, ('otro'::character varying)::text, ('sin_comprobante'::character varying)::text])))
);


ALTER TABLE public.ticket_gastos OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 18811)
-- Name: ticket_gastos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ticket_gastos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ticket_gastos_id_seq OWNER TO postgres;

--
-- TOC entry 5254 (class 0 OID 0)
-- Dependencies: 259
-- Name: ticket_gastos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ticket_gastos_id_seq OWNED BY public.ticket_gastos.id;


--
-- TOC entry 260 (class 1259 OID 18812)
-- Name: ticket_tecnicos; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ticket_tecnicos (
    id bigint NOT NULL,
    ticket_id bigint NOT NULL,
    user_id bigint NOT NULL,
    rol character varying(15) DEFAULT 'apoyo'::character varying NOT NULL,
    monto_trato numeric(10,2) DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    CONSTRAINT ticket_tecnicos_rol_check CHECK (((rol)::text = ANY (ARRAY[('responsable'::character varying)::text, ('apoyo'::character varying)::text])))
);


ALTER TABLE public.ticket_tecnicos OWNER TO postgres;

--
-- TOC entry 261 (class 1259 OID 18823)
-- Name: ticket_tecnicos_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ticket_tecnicos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ticket_tecnicos_id_seq OWNER TO postgres;

--
-- TOC entry 5255 (class 0 OID 0)
-- Dependencies: 261
-- Name: ticket_tecnicos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ticket_tecnicos_id_seq OWNED BY public.ticket_tecnicos.id;


--
-- TOC entry 262 (class 1259 OID 18824)
-- Name: tickets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tickets (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    descripcion text NOT NULL,
    prioridad character varying(255) DEFAULT 'media'::character varying NOT NULL,
    estado character varying(255) DEFAULT 'pendiente'::character varying NOT NULL,
    tecnico_id bigint,
    fecha_programada timestamp(0) without time zone,
    fecha_completado timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cliente_id bigint NOT NULL,
    CONSTRAINT tickets_estado_check CHECK (((estado)::text = ANY (ARRAY[('pendiente'::character varying)::text, ('asignado'::character varying)::text, ('en_proceso'::character varying)::text, ('completado'::character varying)::text, ('cancelado'::character varying)::text]))),
    CONSTRAINT tickets_prioridad_check CHECK (((prioridad)::text = ANY (ARRAY[('baja'::character varying)::text, ('media'::character varying)::text, ('alta'::character varying)::text, ('urgente'::character varying)::text])))
);


ALTER TABLE public.tickets OWNER TO postgres;

--
-- TOC entry 263 (class 1259 OID 18839)
-- Name: tickets_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tickets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tickets_id_seq OWNER TO postgres;

--
-- TOC entry 5256 (class 0 OID 0)
-- Dependencies: 263
-- Name: tickets_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tickets_id_seq OWNED BY public.tickets.id;


--
-- TOC entry 264 (class 1259 OID 18840)
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    telefono character varying(20),
    activo boolean DEFAULT true NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- TOC entry 265 (class 1259 OID 18851)
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- TOC entry 5257 (class 0 OID 0)
-- Dependencies: 265
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- TOC entry 4933 (class 2604 OID 26106)
-- Name: auditoria_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_logs ALTER COLUMN id SET DEFAULT nextval('public.auditoria_logs_id_seq'::regclass);


--
-- TOC entry 4880 (class 2604 OID 18852)
-- Name: catalogo_servicios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.catalogo_servicios ALTER COLUMN id SET DEFAULT nextval('public.catalogo_servicios_id_seq'::regclass);


--
-- TOC entry 4884 (class 2604 OID 18853)
-- Name: clientes id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes ALTER COLUMN id SET DEFAULT nextval('public.clientes_id_seq'::regclass);


--
-- TOC entry 4886 (class 2604 OID 18854)
-- Name: conversaciones_ia id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversaciones_ia ALTER COLUMN id SET DEFAULT nextval('public.conversaciones_ia_id_seq'::regclass);


--
-- TOC entry 4887 (class 2604 OID 18855)
-- Name: cotizacion_detalle id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_detalle ALTER COLUMN id SET DEFAULT nextval('public.cotizacion_detalle_id_seq'::regclass);


--
-- TOC entry 4888 (class 2604 OID 18856)
-- Name: cotizacion_servicios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_servicios ALTER COLUMN id SET DEFAULT nextval('public.cotizacion_servicios_id_seq'::regclass);


--
-- TOC entry 4890 (class 2604 OID 18857)
-- Name: cotizaciones id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones ALTER COLUMN id SET DEFAULT nextval('public.cotizaciones_id_seq'::regclass);


--
-- TOC entry 4898 (class 2604 OID 18858)
-- Name: historial_precios_materiales id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_precios_materiales ALTER COLUMN id SET DEFAULT nextval('public.historial_precios_materiales_id_seq'::regclass);


--
-- TOC entry 4900 (class 2604 OID 18859)
-- Name: materiales id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materiales ALTER COLUMN id SET DEFAULT nextval('public.materiales_id_seq'::regclass);


--
-- TOC entry 4907 (class 2604 OID 18860)
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- TOC entry 4908 (class 2604 OID 18861)
-- Name: movimientos_inventario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario ALTER COLUMN id SET DEFAULT nextval('public.movimientos_inventario_id_seq'::regclass);


--
-- TOC entry 4909 (class 2604 OID 18862)
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- TOC entry 4910 (class 2604 OID 18863)
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- TOC entry 4911 (class 2604 OID 18864)
-- Name: precios_proveedor_material id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.precios_proveedor_material ALTER COLUMN id SET DEFAULT nextval('public.precios_proveedor_material_id_seq'::regclass);


--
-- TOC entry 4913 (class 2604 OID 18865)
-- Name: proveedores id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedores ALTER COLUMN id SET DEFAULT nextval('public.proveedores_id_seq'::regclass);


--
-- TOC entry 4915 (class 2604 OID 18866)
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- TOC entry 4916 (class 2604 OID 18867)
-- Name: salida_material_detalle id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salida_material_detalle ALTER COLUMN id SET DEFAULT nextval('public.salida_material_detalle_id_seq'::regclass);


--
-- TOC entry 4918 (class 2604 OID 18868)
-- Name: salidas_materiales id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales ALTER COLUMN id SET DEFAULT nextval('public.salidas_materiales_id_seq'::regclass);


--
-- TOC entry 4921 (class 2604 OID 18869)
-- Name: ticket_gastos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos ALTER COLUMN id SET DEFAULT nextval('public.ticket_gastos_id_seq'::regclass);


--
-- TOC entry 4925 (class 2604 OID 18870)
-- Name: ticket_tecnicos id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_tecnicos ALTER COLUMN id SET DEFAULT nextval('public.ticket_tecnicos_id_seq'::regclass);


--
-- TOC entry 4928 (class 2604 OID 18871)
-- Name: tickets id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tickets ALTER COLUMN id SET DEFAULT nextval('public.tickets_id_seq'::regclass);


--
-- TOC entry 4931 (class 2604 OID 18872)
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- TOC entry 5044 (class 2606 OID 26115)
-- Name: auditoria_logs auditoria_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_logs
    ADD CONSTRAINT auditoria_logs_pkey PRIMARY KEY (id);


--
-- TOC entry 4943 (class 2606 OID 18874)
-- Name: catalogo_servicios catalogo_servicios_codigo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.catalogo_servicios
    ADD CONSTRAINT catalogo_servicios_codigo_key UNIQUE (codigo);


--
-- TOC entry 4945 (class 2606 OID 18876)
-- Name: catalogo_servicios catalogo_servicios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.catalogo_servicios
    ADD CONSTRAINT catalogo_servicios_pkey PRIMARY KEY (id);


--
-- TOC entry 4948 (class 2606 OID 18878)
-- Name: clientes clientes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clientes
    ADD CONSTRAINT clientes_pkey PRIMARY KEY (id);


--
-- TOC entry 4950 (class 2606 OID 18880)
-- Name: conversaciones_ia conversaciones_ia_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversaciones_ia
    ADD CONSTRAINT conversaciones_ia_pkey PRIMARY KEY (id);


--
-- TOC entry 4954 (class 2606 OID 18882)
-- Name: cotizacion_detalle cotizacion_detalle_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_detalle
    ADD CONSTRAINT cotizacion_detalle_pkey PRIMARY KEY (id);


--
-- TOC entry 4956 (class 2606 OID 18884)
-- Name: cotizacion_servicios cotizacion_servicios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_servicios
    ADD CONSTRAINT cotizacion_servicios_pkey PRIMARY KEY (id);


--
-- TOC entry 4958 (class 2606 OID 18886)
-- Name: cotizaciones cotizaciones_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones
    ADD CONSTRAINT cotizaciones_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4962 (class 2606 OID 18888)
-- Name: cotizaciones cotizaciones_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones
    ADD CONSTRAINT cotizaciones_pkey PRIMARY KEY (id);


--
-- TOC entry 4966 (class 2606 OID 18890)
-- Name: historial_precios_materiales historial_precios_materiales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_precios_materiales
    ADD CONSTRAINT historial_precios_materiales_pkey PRIMARY KEY (id);


--
-- TOC entry 4969 (class 2606 OID 18892)
-- Name: materiales materiales_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materiales
    ADD CONSTRAINT materiales_codigo_unique UNIQUE (codigo);


--
-- TOC entry 4971 (class 2606 OID 18894)
-- Name: materiales materiales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materiales
    ADD CONSTRAINT materiales_pkey PRIMARY KEY (id);


--
-- TOC entry 4975 (class 2606 OID 18896)
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- TOC entry 4978 (class 2606 OID 18898)
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- TOC entry 4981 (class 2606 OID 18900)
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- TOC entry 4985 (class 2606 OID 18902)
-- Name: movimientos_inventario movimientos_inventario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_pkey PRIMARY KEY (id);


--
-- TOC entry 4989 (class 2606 OID 18904)
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- TOC entry 4991 (class 2606 OID 18906)
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 4993 (class 2606 OID 18908)
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- TOC entry 4996 (class 2606 OID 18910)
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- TOC entry 4998 (class 2606 OID 18912)
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- TOC entry 5001 (class 2606 OID 18914)
-- Name: precios_proveedor_material precios_proveedor_material_material_id_proveedor_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.precios_proveedor_material
    ADD CONSTRAINT precios_proveedor_material_material_id_proveedor_id_key UNIQUE (material_id, proveedor_id);


--
-- TOC entry 5003 (class 2606 OID 18916)
-- Name: precios_proveedor_material precios_proveedor_material_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.precios_proveedor_material
    ADD CONSTRAINT precios_proveedor_material_pkey PRIMARY KEY (id);


--
-- TOC entry 5005 (class 2606 OID 18918)
-- Name: proveedores proveedores_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.proveedores
    ADD CONSTRAINT proveedores_pkey PRIMARY KEY (id);


--
-- TOC entry 5007 (class 2606 OID 18920)
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- TOC entry 5009 (class 2606 OID 18922)
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- TOC entry 5011 (class 2606 OID 18924)
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- TOC entry 5013 (class 2606 OID 18926)
-- Name: salida_material_detalle salida_material_detalle_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salida_material_detalle
    ADD CONSTRAINT salida_material_detalle_pkey PRIMARY KEY (id);


--
-- TOC entry 5015 (class 2606 OID 18928)
-- Name: salidas_materiales salidas_materiales_codigo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_codigo_key UNIQUE (codigo);


--
-- TOC entry 5017 (class 2606 OID 18930)
-- Name: salidas_materiales salidas_materiales_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_pkey PRIMARY KEY (id);


--
-- TOC entry 5021 (class 2606 OID 18932)
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- TOC entry 5025 (class 2606 OID 18934)
-- Name: ticket_gastos ticket_gastos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos
    ADD CONSTRAINT ticket_gastos_pkey PRIMARY KEY (id);


--
-- TOC entry 5028 (class 2606 OID 18936)
-- Name: ticket_tecnicos ticket_tecnicos_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_tecnicos
    ADD CONSTRAINT ticket_tecnicos_pkey PRIMARY KEY (id);


--
-- TOC entry 5030 (class 2606 OID 18938)
-- Name: ticket_tecnicos ticket_tecnicos_ticket_id_user_id_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_tecnicos
    ADD CONSTRAINT ticket_tecnicos_ticket_id_user_id_key UNIQUE (ticket_id, user_id);


--
-- TOC entry 5033 (class 2606 OID 18940)
-- Name: tickets tickets_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_codigo_unique UNIQUE (codigo);


--
-- TOC entry 5036 (class 2606 OID 18942)
-- Name: tickets tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_pkey PRIMARY KEY (id);


--
-- TOC entry 5039 (class 2606 OID 18944)
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- TOC entry 5041 (class 2606 OID 18946)
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- TOC entry 5042 (class 1259 OID 26121)
-- Name: auditoria_logs_modulo_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auditoria_logs_modulo_created_at_index ON public.auditoria_logs USING btree (modulo, created_at);


--
-- TOC entry 5045 (class 1259 OID 26123)
-- Name: auditoria_logs_registro_tipo_registro_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auditoria_logs_registro_tipo_registro_id_index ON public.auditoria_logs USING btree (registro_tipo, registro_id);


--
-- TOC entry 5046 (class 1259 OID 26122)
-- Name: auditoria_logs_user_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX auditoria_logs_user_id_created_at_index ON public.auditoria_logs USING btree (user_id, created_at);


--
-- TOC entry 4946 (class 1259 OID 18947)
-- Name: clientes_nombre_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX clientes_nombre_index ON public.clientes USING btree (nombre);


--
-- TOC entry 4951 (class 1259 OID 18948)
-- Name: conversaciones_ia_usuario_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX conversaciones_ia_usuario_id_index ON public.conversaciones_ia USING btree (usuario_id);


--
-- TOC entry 4952 (class 1259 OID 18949)
-- Name: cotizacion_detalle_material_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cotizacion_detalle_material_id_index ON public.cotizacion_detalle USING btree (material_id);


--
-- TOC entry 4959 (class 1259 OID 18950)
-- Name: cotizaciones_cotizador_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cotizaciones_cotizador_id_index ON public.cotizaciones USING btree (cotizador_id);


--
-- TOC entry 4960 (class 1259 OID 18951)
-- Name: cotizaciones_estado_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cotizaciones_estado_index ON public.cotizaciones USING btree (estado);


--
-- TOC entry 4963 (class 1259 OID 18952)
-- Name: cotizaciones_ticket_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cotizaciones_ticket_id_index ON public.cotizaciones USING btree (ticket_id);


--
-- TOC entry 4964 (class 1259 OID 18953)
-- Name: historial_precios_material_fecha_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX historial_precios_material_fecha_index ON public.historial_precios_materiales USING btree (material_id, fecha);


--
-- TOC entry 4967 (class 1259 OID 18954)
-- Name: materiales_categoria_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX materiales_categoria_index ON public.materiales USING btree (categoria);


--
-- TOC entry 4972 (class 1259 OID 18955)
-- Name: materiales_proveedor_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX materiales_proveedor_id_index ON public.materiales USING btree (proveedor_id);


--
-- TOC entry 4973 (class 1259 OID 18956)
-- Name: materiales_stock_stock_minimo_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX materiales_stock_stock_minimo_index ON public.materiales USING btree (stock, stock_minimo);


--
-- TOC entry 4976 (class 1259 OID 18957)
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- TOC entry 4979 (class 1259 OID 18958)
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- TOC entry 4982 (class 1259 OID 18959)
-- Name: movimientos_inventario_cotizacion_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX movimientos_inventario_cotizacion_id_index ON public.movimientos_inventario USING btree (cotizacion_id);


--
-- TOC entry 4983 (class 1259 OID 18960)
-- Name: movimientos_inventario_material_id_fecha_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX movimientos_inventario_material_id_fecha_index ON public.movimientos_inventario USING btree (material_id, fecha);


--
-- TOC entry 4986 (class 1259 OID 18961)
-- Name: movimientos_inventario_tipo_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX movimientos_inventario_tipo_index ON public.movimientos_inventario USING btree (tipo);


--
-- TOC entry 4987 (class 1259 OID 18962)
-- Name: movimientos_inventario_usuario_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX movimientos_inventario_usuario_id_index ON public.movimientos_inventario USING btree (usuario_id);


--
-- TOC entry 4994 (class 1259 OID 18963)
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- TOC entry 4999 (class 1259 OID 18964)
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- TOC entry 5018 (class 1259 OID 26129)
-- Name: salidas_materiales_salida_original_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX salidas_materiales_salida_original_id_index ON public.salidas_materiales USING btree (salida_original_id);


--
-- TOC entry 5019 (class 1259 OID 18965)
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- TOC entry 5022 (class 1259 OID 18966)
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- TOC entry 5023 (class 1259 OID 18967)
-- Name: ticket_gastos_cotizacion_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ticket_gastos_cotizacion_index ON public.ticket_gastos USING btree (cotizacion_id);


--
-- TOC entry 5026 (class 1259 OID 18968)
-- Name: ticket_gastos_ticket_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ticket_gastos_ticket_index ON public.ticket_gastos USING btree (ticket_id);


--
-- TOC entry 5031 (class 1259 OID 18969)
-- Name: ticket_tecnicos_user_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ticket_tecnicos_user_index ON public.ticket_tecnicos USING btree (user_id);


--
-- TOC entry 5034 (class 1259 OID 18970)
-- Name: tickets_estado_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tickets_estado_index ON public.tickets USING btree (estado);


--
-- TOC entry 5037 (class 1259 OID 18971)
-- Name: tickets_tecnico_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX tickets_tecnico_id_index ON public.tickets USING btree (tecnico_id);


--
-- TOC entry 5083 (class 2606 OID 26116)
-- Name: auditoria_logs auditoria_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.auditoria_logs
    ADD CONSTRAINT auditoria_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5047 (class 2606 OID 18972)
-- Name: conversaciones_ia conversaciones_ia_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.conversaciones_ia
    ADD CONSTRAINT conversaciones_ia_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5048 (class 2606 OID 18977)
-- Name: cotizacion_detalle cotizacion_detalle_cotizacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_detalle
    ADD CONSTRAINT cotizacion_detalle_cotizacion_id_foreign FOREIGN KEY (cotizacion_id) REFERENCES public.cotizaciones(id) ON DELETE CASCADE;


--
-- TOC entry 5049 (class 2606 OID 18982)
-- Name: cotizacion_detalle cotizacion_detalle_material_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_detalle
    ADD CONSTRAINT cotizacion_detalle_material_id_foreign FOREIGN KEY (material_id) REFERENCES public.materiales(id) ON DELETE RESTRICT;


--
-- TOC entry 5050 (class 2606 OID 18987)
-- Name: cotizacion_servicios cotizacion_servicios_cotizacion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_servicios
    ADD CONSTRAINT cotizacion_servicios_cotizacion_id_fkey FOREIGN KEY (cotizacion_id) REFERENCES public.cotizaciones(id) ON DELETE CASCADE;


--
-- TOC entry 5051 (class 2606 OID 18992)
-- Name: cotizacion_servicios cotizacion_servicios_servicio_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizacion_servicios
    ADD CONSTRAINT cotizacion_servicios_servicio_id_fkey FOREIGN KEY (servicio_id) REFERENCES public.catalogo_servicios(id) ON DELETE SET NULL;


--
-- TOC entry 5052 (class 2606 OID 18997)
-- Name: cotizaciones cotizaciones_cliente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones
    ADD CONSTRAINT cotizaciones_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES public.clientes(id) ON DELETE RESTRICT;


--
-- TOC entry 5053 (class 2606 OID 19002)
-- Name: cotizaciones cotizaciones_cotizador_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones
    ADD CONSTRAINT cotizaciones_cotizador_id_foreign FOREIGN KEY (cotizador_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5054 (class 2606 OID 19007)
-- Name: cotizaciones cotizaciones_ticket_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cotizaciones
    ADD CONSTRAINT cotizaciones_ticket_id_foreign FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE SET NULL;


--
-- TOC entry 5055 (class 2606 OID 19012)
-- Name: historial_precios_materiales historial_precios_materiales_material_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_precios_materiales
    ADD CONSTRAINT historial_precios_materiales_material_id_fkey FOREIGN KEY (material_id) REFERENCES public.materiales(id) ON DELETE CASCADE;


--
-- TOC entry 5056 (class 2606 OID 19017)
-- Name: historial_precios_materiales historial_precios_materiales_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_precios_materiales
    ADD CONSTRAINT historial_precios_materiales_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES public.proveedores(id) ON DELETE SET NULL;


--
-- TOC entry 5057 (class 2606 OID 19022)
-- Name: historial_precios_materiales historial_precios_materiales_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.historial_precios_materiales
    ADD CONSTRAINT historial_precios_materiales_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5058 (class 2606 OID 19027)
-- Name: materiales materiales_proveedor_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.materiales
    ADD CONSTRAINT materiales_proveedor_id_foreign FOREIGN KEY (proveedor_id) REFERENCES public.proveedores(id) ON DELETE SET NULL;


--
-- TOC entry 5059 (class 2606 OID 19032)
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5060 (class 2606 OID 19037)
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5061 (class 2606 OID 19042)
-- Name: movimientos_inventario movimientos_inventario_cotizacion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_cotizacion_id_foreign FOREIGN KEY (cotizacion_id) REFERENCES public.cotizaciones(id) ON DELETE SET NULL;


--
-- TOC entry 5062 (class 2606 OID 19047)
-- Name: movimientos_inventario movimientos_inventario_material_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_material_id_foreign FOREIGN KEY (material_id) REFERENCES public.materiales(id) ON DELETE CASCADE;


--
-- TOC entry 5063 (class 2606 OID 19052)
-- Name: movimientos_inventario movimientos_inventario_salida_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_salida_id_fkey FOREIGN KEY (salida_id) REFERENCES public.salidas_materiales(id) ON DELETE SET NULL;


--
-- TOC entry 5064 (class 2606 OID 19057)
-- Name: movimientos_inventario movimientos_inventario_usuario_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.movimientos_inventario
    ADD CONSTRAINT movimientos_inventario_usuario_id_foreign FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5065 (class 2606 OID 19062)
-- Name: precios_proveedor_material precios_proveedor_material_material_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.precios_proveedor_material
    ADD CONSTRAINT precios_proveedor_material_material_id_fkey FOREIGN KEY (material_id) REFERENCES public.materiales(id) ON DELETE CASCADE;


--
-- TOC entry 5066 (class 2606 OID 19067)
-- Name: precios_proveedor_material precios_proveedor_material_proveedor_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.precios_proveedor_material
    ADD CONSTRAINT precios_proveedor_material_proveedor_id_fkey FOREIGN KEY (proveedor_id) REFERENCES public.proveedores(id) ON DELETE CASCADE;


--
-- TOC entry 5067 (class 2606 OID 19072)
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- TOC entry 5068 (class 2606 OID 19077)
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- TOC entry 5069 (class 2606 OID 19082)
-- Name: salida_material_detalle salida_material_detalle_material_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salida_material_detalle
    ADD CONSTRAINT salida_material_detalle_material_id_fkey FOREIGN KEY (material_id) REFERENCES public.materiales(id) ON DELETE RESTRICT;


--
-- TOC entry 5070 (class 2606 OID 19087)
-- Name: salida_material_detalle salida_material_detalle_salida_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salida_material_detalle
    ADD CONSTRAINT salida_material_detalle_salida_id_fkey FOREIGN KEY (salida_id) REFERENCES public.salidas_materiales(id) ON DELETE CASCADE;


--
-- TOC entry 5071 (class 2606 OID 26096)
-- Name: salidas_materiales salidas_materiales_confirmado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_confirmado_por_foreign FOREIGN KEY (confirmado_por) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- TOC entry 5072 (class 2606 OID 26124)
-- Name: salidas_materiales salidas_materiales_salida_original_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_salida_original_id_foreign FOREIGN KEY (salida_original_id) REFERENCES public.salidas_materiales(id) ON DELETE SET NULL;


--
-- TOC entry 5073 (class 2606 OID 19092)
-- Name: salidas_materiales salidas_materiales_ticket_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_ticket_id_fkey FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE SET NULL;


--
-- TOC entry 5074 (class 2606 OID 19097)
-- Name: salidas_materiales salidas_materiales_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.salidas_materiales
    ADD CONSTRAINT salidas_materiales_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- TOC entry 5075 (class 2606 OID 19102)
-- Name: ticket_gastos ticket_gastos_cotizacion_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos
    ADD CONSTRAINT ticket_gastos_cotizacion_id_fkey FOREIGN KEY (cotizacion_id) REFERENCES public.cotizaciones(id) ON DELETE SET NULL;


--
-- TOC entry 5076 (class 2606 OID 19107)
-- Name: ticket_gastos ticket_gastos_registrado_por_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos
    ADD CONSTRAINT ticket_gastos_registrado_por_fkey FOREIGN KEY (registrado_por) REFERENCES public.users(id) ON DELETE RESTRICT;


--
-- TOC entry 5077 (class 2606 OID 19112)
-- Name: ticket_gastos ticket_gastos_salida_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos
    ADD CONSTRAINT ticket_gastos_salida_id_fkey FOREIGN KEY (salida_id) REFERENCES public.salidas_materiales(id) ON DELETE SET NULL;


--
-- TOC entry 5078 (class 2606 OID 19117)
-- Name: ticket_gastos ticket_gastos_ticket_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_gastos
    ADD CONSTRAINT ticket_gastos_ticket_id_fkey FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE CASCADE;


--
-- TOC entry 5079 (class 2606 OID 19122)
-- Name: ticket_tecnicos ticket_tecnicos_ticket_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_tecnicos
    ADD CONSTRAINT ticket_tecnicos_ticket_id_fkey FOREIGN KEY (ticket_id) REFERENCES public.tickets(id) ON DELETE CASCADE;


--
-- TOC entry 5080 (class 2606 OID 19127)
-- Name: ticket_tecnicos ticket_tecnicos_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ticket_tecnicos
    ADD CONSTRAINT ticket_tecnicos_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- TOC entry 5081 (class 2606 OID 19132)
-- Name: tickets tickets_cliente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_cliente_id_foreign FOREIGN KEY (cliente_id) REFERENCES public.clientes(id) ON DELETE RESTRICT;


--
-- TOC entry 5082 (class 2606 OID 19137)
-- Name: tickets tickets_tecnico_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tickets
    ADD CONSTRAINT tickets_tecnico_id_foreign FOREIGN KEY (tecnico_id) REFERENCES public.users(id) ON DELETE SET NULL;


-- Completed on 2026-10-31 14:02:26

--
-- PostgreSQL database dump complete
--

\unrestrict tbwC4CndMexlFL5HTr14HohkKOVaftL6X2V8vIVcGNKcYKLnuGbWvIkS8xFsOkK

