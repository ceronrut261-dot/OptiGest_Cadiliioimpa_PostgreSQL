# Manual de Usuario — OptiGest

## 1. Acceso al sistema

1. Abrir un navegador desde computadora o celular.
2. Ingresar a la URL del sistema.
3. **Iniciar sesión** con correo y contraseña, completando el CAPTCHA
   ("No soy un robot").
4. Si olvidaste tu contraseña, usa el enlace **"¿Olvidaste tu
   contraseña?"** en la pantalla de login: recibirás un correo con un
   enlace para definir una nueva.

El sistema es **responsivo**: en pantallas pequeñas el menú se oculta
automáticamente y se accede mediante el botón ☰.

## 2. Roles del sistema

| Rol | Descripción | Accesos principales |
|---|---|---|
| **Administrador** | Control total del sistema. | Todos los módulos, reportes gerenciales, gestión de usuarios y proveedores. |
| **Cotizador** | Genera cotizaciones y mantiene el catálogo de materiales. | Materiales, proveedores, cotizaciones, movimientos de inventario, tickets. |
| **Técnico** | Personal de campo que ejecuta los servicios. | Tickets asignados, registrar movimientos de inventario. |

## 3. Módulo de Tickets

`Tickets → Nuevo ticket`: cliente, dirección, descripción, prioridad y
técnico asignado. Código correlativo `TKT-00001`. Estados: `pendiente →
asignado → en_proceso → completado` (o `cancelado`).

## 4. Módulo de Control de Inventario

- **Materiales**: nombre, categoría, precio, proveedor, stock inicial y
  stock mínimo (umbral de alerta). Código `MAT-00001`.
- **Movimientos**: entradas/salidas con trazabilidad completa (usuario,
  fecha, motivo). No se permite una salida mayor al stock disponible.
- **Alertas**: un material se resalta en rojo cuando `stock <=
  stock_minimo`.
- **Descuento automático**: al aprobar una cotización, el stock se
  descuenta automáticamente y queda un movimiento enlazado.
- **Reporte gerencial** (`Inventario → Reportes`, solo administrador):
  stock por categoría, valorización total, exportable a PDF.

## 5. Módulo de Proveedores

Alta, edición y baja lógica de proveedores, asociables a materiales.

## 6. Módulo de Cotizaciones

1. `Cotizaciones → Nueva cotización`: cliente, ticket relacionado
   (opcional), materiales y cantidades. Cálculo automático de totales.
2. Se crea en estado `borrador` (no afecta el inventario).
3. Al **aprobar**, se valida stock suficiente y se descuenta
   automáticamente.
4. Exportable a PDF para enviar al cliente.

## 7. Asistente Inteligente

`Asistente IA`: preguntas en lenguaje natural sobre el estado operativo
actual (tickets abiertos, cotizaciones pendientes, materiales en alerta).

## 8. Seguridad de la cuenta

- Tu contraseña se almacena cifrada (bcrypt); nadie, ni el administrador,
  puede ver tu contraseña en texto plano.
- Todos los formularios de acceso están protegidos con CAPTCHA.
- Si sospechas que tu cuenta fue comprometida, usa "¿Olvidaste tu
  contraseña?" para restablecerla de inmediato.
