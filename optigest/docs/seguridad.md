# Seguridad — Cifrado, Recuperación de Contraseña y CAPTCHA

## 1. Cifrado de contraseñas

Todas las contraseñas del sistema (administrador, técnico, cotizador) se
cifran con **bcrypt** antes de guardarse en la base de datos. Nunca se
almacena texto plano.

- El modelo `App\Models\User` define el atributo `password` con el cast
  `'password' => 'hashed'`, por lo que **cualquier asignación** (`$user->password
  = '...'`, `User::create([...])`, `$user->update([...])`) se cifra
  automáticamente antes de persistirse.
- El costo del algoritmo se configura en `config/hashing.php` mediante
  `BCRYPT_ROUNDS` (por defecto `12`), un balance adecuado entre seguridad y
  tiempo de cómputo para este proyecto.
- La verificación al iniciar sesión (`Auth::attempt`) compara el hash
  almacenado contra el valor ingresado usando `password_verify()`
  internamente — la contraseña en texto plano nunca se compara
  directamente ni se expone en logs.

## 2. Recuperación de contraseña

Implementada con el **Password Broker** nativo de Laravel:

1. El usuario solicita recuperación en `/forgot-password` indicando su
   correo (protegido con CAPTCHA, ver sección 3).
2. Laravel genera un token aleatorio, lo guarda **hasheado** en la tabla
   `password_reset_tokens` (nunca en texto plano) y envía un correo con un
   enlace único a `/reset-password/{token}`.
3. El enlace expira a los 60 minutos (`config('auth.passwords.users.expire')`).
4. Al definir la nueva contraseña, el sistema valida el token contra el
   hash almacenado, actualiza la contraseña (cifrada nuevamente con
   bcrypt) y regenera el `remember_token` de la sesión por seguridad.

### Archivos involucrados

```
app/Http/Controllers/Auth/PasswordResetLinkController.php   (envía el enlace)
app/Http/Controllers/Auth/NewPasswordController.php          (define la nueva contraseña)
resources/views/auth/forgot-password.blade.php
resources/views/auth/reset-password.blade.php
routes/auth.php
```

### Configuración de correo requerida

El envío de correos usa la configuración de `config/mail.php` /
variables `MAIL_*` del `.env`. En desarrollo se recomienda una cuenta
gratuita de **Mailtrap** (bandeja de pruebas, no envía correos reales) —
ver `README.md` raíz para las instrucciones exactas.

## 3. CAPTCHA — Google reCAPTCHA v2

Se usa el checkbox **"No soy un robot"** de Google reCAPTCHA v2 para
proteger contra bots y ataques de fuerza bruta en:

- Inicio de sesión (`/login`)
- Registro de cuenta (`/register`)
- Solicitud de recuperación de contraseña (`/forgot-password`)

### Funcionamiento

1. El frontend carga el script de Google (`https://www.google.com/recaptcha/api.js`)
   y renderiza el widget con la **Site Key** pública
   (`config('services.recaptcha.site_key')`).
2. Al enviar el formulario, el navegador incluye un campo oculto
   `g-recaptcha-response` con un token generado por Google.
3. El backend valida ese token con la regla personalizada
   `App\Rules\Recaptcha`, que hace una petición server-to-server a
   `https://www.google.com/recaptcha/api/siteverify` usando la **Secret
   Key** privada. Solo si Google confirma `success: true` la petición
   continúa.

### Archivo de la regla

```
app/Rules/Recaptcha.php
```

### Variables de entorno necesarias

```
RECAPTCHA_SITE_KEY=...
RECAPTCHA_SECRET_KEY=...
```

Se obtienen registrando el dominio en
https://www.google.com/recaptcha/admin/create (agregar `localhost` y
`127.0.0.1` para pruebas locales, y el dominio del VPS para producción).

> Nota de diseño: si las llaves no están configuradas en el servidor, la
> regla `Recaptcha` no bloquea el flujo (para no impedir pruebas en
> entornos de desarrollo sin configurar aún), pero se recomienda
> configurarlas siempre antes de desplegar a producción.
