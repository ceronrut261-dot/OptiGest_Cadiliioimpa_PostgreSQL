# Módulo de Control de Inventario — Documentación Técnica

## 1. Objetivo

Controlar el ciclo de vida de los materiales manejados en bodega por
Constru Fontanería Cadiliompa: registro, entradas/salidas con trazabilidad,
alertas de bajo stock, integración con cotizaciones y reportes gerenciales.

## 2. Modelo de datos (PostgreSQL)

### Tabla `materiales`
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigserial PK | |
| codigo | varchar(20) unique | Correlativo `MAT-00001` |
| nombre | varchar(255) | |
| categoria | varchar(100) | Indexado para filtros/reportes |
| descripcion | text nullable | |
| precio | numeric(10,2) | |
| stock | integer | Se modifica únicamente vía `movimientos_inventario` |
| stock_minimo | integer | Umbral de alerta de bajo stock |
| unidad_medida | varchar(30) | ej. unidad, metro, galón |
| proveedor_id | FK → proveedores.id (nullable) | |
| activo | boolean | Baja lógica |

### Tabla `movimientos_inventario`
| Campo | Tipo | Descripción |
|---|---|---|
| id | bigserial PK | |
| material_id | FK → materiales.id | |
| tipo | varchar con CHECK (entrada, salida) | |
| cantidad | integer | |
| motivo | varchar(255) nullable | |
| fecha | timestamp | |
| usuario_id | FK → users.id | Trazabilidad: quién hizo el movimiento |
| cotizacion_id | FK → cotizaciones.id (nullable) | Si se originó por una cotización aprobada |
| stock_resultante | integer | Stock inmediatamente después del movimiento (auditoría) |

> Nota sobre PostgreSQL: Laravel traduce `$table->enum(...)` a una columna
> `varchar` con restricción `CHECK` en PostgreSQL (no existe un tipo ENUM
> nativo usado por Eloquent), por lo que el comportamiento es idéntico al
> de MySQL sin cambios en el código de la migración.

## 3. Reglas de negocio

1. El `stock` de un material **nunca se edita manualmente** desde el
   formulario de edición; solo cambia mediante `movimientos_inventario`.
2. Un movimiento de tipo `salida` se rechaza si `cantidad > stock actual`.
3. Toda actualización de stock ocurre dentro de una transacción con
   bloqueo de fila (`lockForUpdate`) para evitar condiciones de carrera.
4. Un material está en **alerta de bajo stock** cuando `stock <=
   stock_minimo`.

## 4. Integración con Cotizaciones

Al aprobar una cotización (`CotizacionController::aprobar`):

1. Verifica stock suficiente para **todos** los materiales antes de
   aplicar cualquier cambio (operación todo-o-nada).
2. Descuenta el stock de cada material vía
   `MovimientoInventarioController::registrarSalidaPorCotizacion()`.
3. Registra un movimiento `salida` con `cotizacion_id` enlazado.

## 5. Endpoints principales

| Método | Ruta | Rol requerido |
|---|---|---|
| GET/POST | `/inventario/materiales` | administrador, cotizador |
| PUT/DELETE | `/inventario/materiales/{material}` | administrador, cotizador |
| GET/POST | `/inventario/movimientos` | administrador, técnico, cotizador |
| GET | `/inventario/reportes/stock` | administrador |
| GET | `/inventario/reportes/stock/exportar` | administrador |

> El parámetro de ruta se fija explícitamente como `{material}` en
> `routes/web.php` (`->parameters(['materiales' => 'material'])`) porque
> Laravel singulariza "materiales" incorrectamente al no ser una palabra
> en inglés.

## 6. Archivos del módulo

```
backend/app/Models/Material.php
backend/app/Models/MovimientoInventario.php
backend/app/Http/Controllers/Inventario/MaterialController.php
backend/app/Http/Controllers/Inventario/MovimientoInventarioController.php
backend/app/Http/Controllers/Inventario/ReporteInventarioController.php
backend/database/migrations/2024_01_01_000002_create_materiales_table.php
backend/database/migrations/2024_01_01_000006_create_movimientos_inventario_table.php
backend/database/seeders/InventarioSeeder.php
backend/resources/views/inventario/**
```
