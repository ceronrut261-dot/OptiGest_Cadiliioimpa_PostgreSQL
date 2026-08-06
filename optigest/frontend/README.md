# frontend/ — Copia de referencia

Esta carpeta contiene una copia de solo lectura de las vistas Blade y los
assets estáticos usados por el backend Laravel (Bootstrap 5, sin build
step). El proyecto **no** usa un framework SPA separado: el frontend real
vive en `backend/resources/views` y `backend/public`.

Para resincronizar esta copia tras editar el backend:
```bash
./sync-frontend.sh
```
