# OptiGest

**Sistema Web de Gestión Operativa para Constru Fontanería Cadiliompa**

Trabajo de graduación — Licenciatura en Ingeniería en Sistemas, Universidad
Mariano Gálvez de Guatemala (UMG). Autora: **Rut Noemí Cerón Salas**.

Aplicación web responsiva (cliente-servidor) que digitaliza tickets de
servicio técnico, control de inventario, proveedores, cotizaciones y
reportes gerenciales, con un asistente inteligente de apoyo a decisiones.

**Motor de base de datos: PostgreSQL 15+.**
**Seguridad: contraseñas cifradas con bcrypt, recuperación de contraseña
por correo, y CAPTCHA (Google reCAPTCHA v2) en login/registro/recuperación.**

## Estructura del proyecto

```
optigest/
├── backend/                  Aplicación Laravel 11 (PHP 8.2+)
│   ├── app/Http/Controllers/ Controladores por módulo (Auth, Inventario, ...)
│   ├── app/Models/            Modelos Eloquent
│   ├── app/Rules/Recaptcha.php  Validación de Google reCAPTCHA v2
│   ├── database/migrations/   Migraciones (compatibles con PostgreSQL)
│   ├── database/seeders/      Roles, usuarios y datos de prueba
│   ├── resources/views/       Vistas Blade (Bootstrap 5)
│   ├── routes/                web.php, auth.php (incluye recuperación de contraseña)
│   └── public/                Front controller + assets estáticos
├── frontend/                  Copia de referencia de vistas/assets
├── database/
│   ├── schema.sql              Script SQL en dialecto PostgreSQL
│   └── seed_data.sql           Datos iniciales de referencia
├── docs/
│   ├── manual_usuario.md
│   ├── inventario.md
│   ├── seguridad.md             Cifrado, recuperación de contraseña, CAPTCHA
│   ├── backlog_sprints.md
│   └── diagramas/                UML y BPMN (Mermaid)
└── README.md
```

## Requisitos previos

- PHP >= 8.2 con extensiones: `pdo_pgsql`, `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `bcmath`
- Composer 2.x
- **PostgreSQL 15+** (https://www.postgresql.org/download/)
- Git
- Cuenta de **Google reCAPTCHA v2** (gratuita): https://www.google.com/recaptcha/admin/create
- Cuenta de correo SMTP para pruebas (recomendado: **Mailtrap**, gratuito)

## Instalación local (Visual Studio Code + Laragon)

### 1. Habilitar PostgreSQL en PHP (Laragon)

Edita `C:\laragon\bin\php\php-8.x.x\php.ini` y descomenta:
```ini
extension=pdo_pgsql
extension=pgsql
```
Reinicia Laragon. Verifica con `php -m | findstr pgsql`.

### 2. Crear la base de datos

Con pgAdmin 4 (instalado junto a PostgreSQL): click derecho en
**Databases** → **Create** → **Database** → nombre `optigest`.

### 3. Instalar dependencias

```bash
cd backend
composer install
```

### 4. Configurar el entorno

```bash
copy .env.example .env
php artisan key:generate
```

Edita `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=optigest
DB_USERNAME=postgres
DB_PASSWORD=tu_password_de_postgres
```

### 5. Configurar CAPTCHA

Registra tu dominio (agrega `localhost` y `127.0.0.1` para pruebas) en
https://www.google.com/recaptcha/admin/create y copia las llaves a `.env`:
```env
RECAPTCHA_SITE_KEY=tu_site_key
RECAPTCHA_SECRET_KEY=tu_secret_key
```

### 6. Configurar correo (para recuperación de contraseña)

Con una cuenta gratuita de Mailtrap (https://mailtrap.io), copia las
credenciales SMTP a `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario_mailtrap
MAIL_PASSWORD=tu_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@cadiliompa.com"
MAIL_FROM_NAME="OptiGest"
```

### 7. Ejecutar migraciones y datos de prueba

```bash
php artisan config:clear
php artisan migrate --seed
```

### 8. Levantar el servidor

```bash
php artisan serve
```
Abrir `http://localhost:8000`.

### Usuarios de prueba

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@cadiliompa.com | password |
| Técnico | tecnico@cadiliompa.com | password |
| Cotizador | cotizador@cadiliompa.com | password |

> ⚠️ Cambiar estas contraseñas antes de desplegar en producción.

## Probar la recuperación de contraseña

1. En `/login`, click en **"¿Olvidaste tu contraseña?"**.
2. Ingresa el correo, completa el CAPTCHA, envía.
3. Revisa la bandeja de tu cuenta de Mailtrap (no llega a un correo real).
4. Abre el correo, sigue el enlace, define una nueva contraseña.
5. Inicia sesión con la contraseña nueva.

## Despliegue en servidor VPS

1. **Preparar el servidor** (Ubuntu 22.04/24.04):
   ```bash
   sudo apt update
   sudo apt install -y php8.2-fpm php8.2-pgsql php8.2-mbstring php8.2-xml \
       php8.2-curl php8.2-zip php8.2-bcmath postgresql postgresql-contrib nginx composer git
   ```

2. **Crear la base de datos en PostgreSQL**:
   ```bash
   sudo -u postgres psql
   CREATE DATABASE optigest WITH ENCODING 'UTF8';
   CREATE USER optigest_user WITH PASSWORD 'password_seguro';
   GRANT ALL PRIVILEGES ON DATABASE optigest TO optigest_user;
   \q
   ```

3. Subir el proyecto a `/var/www/optigest`, repetir pasos de instalación
   (composer install, `.env`, `key:generate`, `migrate --seed`).

4. Registrar el dominio real del VPS en la consola de Google reCAPTCHA
   (además de `localhost`) y actualizar `RECAPTCHA_SITE_KEY` /
   `RECAPTCHA_SECRET_KEY` en el `.env` de producción si usas llaves
   distintas a las de desarrollo.

5. Configurar un proveedor SMTP real para producción (ej. Postmark,
   Resend, o el SMTP de tu hosting) en lugar de Mailtrap.

6. Permisos:
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

7. `.env` de producción:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://tu-dominio.com
   ```

8. **Nginx** apuntando a `backend/public`:
   ```nginx
   server {
       listen 80;
       server_name tu-dominio.com;
       root /var/www/optigest/backend/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php8.2-fpm.sock;
       }
       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

9. Certificado SSL con Certbot / Let's Encrypt.

10. Optimizar:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

## Seguridad implementada

- **Contraseñas cifradas con bcrypt** (`config/hashing.php`, cast
  `'password' => 'hashed'` en el modelo `User`) — nunca se guarda texto
  plano.
- **Recuperación de contraseña** vía correo con token de un solo uso que
  expira en 60 minutos (`app/Http/Controllers/Auth/PasswordResetLinkController.php`,
  `NewPasswordController.php`).
- **CAPTCHA (Google reCAPTCHA v2)** en login, registro y recuperación de
  contraseña, validado server-side (`app/Rules/Recaptcha.php`).
- Autorización por rol con middleware `role:` (Spatie Laravel-Permission).
- Transacciones atómicas con bloqueo de fila (`lockForUpdate`) en las
  operaciones que modifican stock.
- Variables sensibles fuera del código fuente, en `.env`.

Ver [`docs/seguridad.md`](./docs/seguridad.md) para el detalle técnico
completo de estos tres mecanismos.

## Documentación adicional

Ver la carpeta [`docs/`](./docs/README.md) para el manual de usuario, la
documentación técnica del módulo de inventario, seguridad, el backlog de
sprints y los diagramas UML/BPMN del sistema.
