# Backlog de Producto y Sprints — Metodología SCRUM

## Roles SCRUM

| Rol | Responsable |
|---|---|
| Product Owner | Propietario de Constru Fontanería Cadiliompa |
| Scrum Master / Desarrolladora | Rut Noemí Cerón Salas |
| Equipo de desarrollo | Rut Noemí Cerón Salas (proyecto individual de graduación) |

## Sprints

### Sprint 1 — Fundamentos, seguridad y base de datos
- Instalación Laravel 11 + PostgreSQL.
- Autenticación (login, registro), roles con Spatie Laravel-Permission.
- CAPTCHA (Google reCAPTCHA v2) en login/registro.
- Recuperación de contraseña vía correo.
- Dashboard inicial con KPIs.

### Sprint 2 — Módulo de Tickets
- CRUD completo, flujo de estados, código correlativo.

### Sprint 3 — Inventario y Proveedores
- CRUD de proveedores y materiales.
- Movimientos de entrada/salida con trazabilidad.
- Alertas de bajo stock.

### Sprint 4 — Cotizaciones e integración con inventario
- CRUD de cotizaciones, cálculo automático de totales.
- Descuento automático de stock al aprobar (transacción atómica).
- Exportación a PDF.

### Sprint 5 — Reportes gerenciales y Asistente IA
- Reporte de disponibilidad por categoría, exportable a PDF.
- Asistente inteligente para consultas gerenciales.
- Pruebas de responsividad móvil y seguridad.

## Indicadores de éxito

| Indicador | Línea base | Meta |
|---|---|---|
| Tiempo de elaboración de cotización | 1 a 3 días | Menos de 30 minutos |
| Exactitud de inventario | 0% (sin control digital) | ≥ 95% |
| Incremento en rapidez de cotizaciones | — | ≥ 20% |
| Reducción de errores en cotizaciones | — | ≥ 30% |
