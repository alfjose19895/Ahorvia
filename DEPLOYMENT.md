# DEPLOYMENT.md — Guía de Despliegue de Ahorvia en BanaHosting (cPanel)

Esta guía describe el procedimiento seguro y recomendado para desplegar **Ahorvia — Finanzas Personales** en un entorno de hosting compartido con **cPanel** (como **BanaHosting**), sin exponer el código fuente, el archivo `.env` ni los comprobantes financieros privados.

---

## 1. Requisitos del Servidor en BanaHosting (cPanel)

Verificar desde el panel de cPanel antes de iniciar:

- **PHP:** `8.3` o superior (configurar en *Select PHP Version* o *MultiPHP Manager*).
- **Extensiones PHP habilitadas:**
  `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.
- **Base de datos:** MySQL 8.0+ (o MariaDB 10.6+ con motor `InnoDB` y soporte `utf8mb4`).
- **SSL:** Certificado HTTPS activo (AutoSSL / Let's Encrypt forzado).
- **Acceso SSH / Terminal cPanel:** Recomendado para ejecutar Composer y comandos `artisan`.
- **Sin dependencias no soportadas:** Ahorvia **no requiere** Docker, Redis, Supervisor, WebSockets ni Node.js corriendo en el servidor de producción.

---

## 2. Arquitectura Segura de Directorios en cPanel

> **REGLA CRÍTICA DE SEGURIDAD:** Nunca coloques la raíz completa del proyecto Laravel directamente dentro de `public_html/`. Si lo haces, archivos críticos como `.env`, `composer.json` o `storage/` podrían quedar expuestos ante un error de `.htaccess`.

Separa el núcleo privado de la carpeta pública del servidor web:

```text
/home/tu_usuario_cpanel/
├── ahorvia_core/              # Código completo del proyecto Laravel (FUERA de public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   │   └── app/private/       # Comprobantes y respaldos privados (no accesibles por URL)
│   ├── vendor/
│   ├── .env                   # Permisos 0600
│   └── artisan
└── public_html/               # (O subdominio: /home/tu_usuario_cpanel/app.ahorvia.com/)
    ├── build/                 # Assets compilados por Vite
    ├── icons/                 # Iconos PWA
    ├── index.php              # Apunta a ../ahorvia_core/
    ├── manifest.webmanifest
    ├── sw.js
    └── .htaccess
```

### Alternativa A (Recomendada si usas un subdominio como `app.ahorvia.com`)

1. Sube el proyecto completo a `/home/tu_usuario_cpanel/ahorvia_core`.
2. En **cPanel → Dominios**, configura el *Document Root* del dominio o subdominio directamente hacia:
   `/home/tu_usuario_cpanel/ahorvia_core/public`
3. De esta forma **no necesitas modificar `index.php`** y todo el núcleo queda fuera de la raíz pública.

### Alternativa B (Si debes usar `public_html` en el dominio principal)

1. Sube el proyecto completo a `/home/tu_usuario_cpanel/ahorvia_core`.
2. Reemplaza `public_html` por un enlace simbólico hacia `ahorvia_core/public` desde la Terminal de cPanel:
   ```bash
   mv ~/public_html ~/public_html_backup
   ln -s ~/ahorvia_core/public ~/public_html
   ```
   *(Si el plan tiene deshabilitado `ln -s` sobre `public_html`, copia el contenido de `ahorvia_core/public/` a `public_html/` y actualiza las rutas de `vendor/autoload.php` y `bootstrap/app.php` en `public_html/index.php` para que apunten a `__DIR__.'/../ahorvia_core/...'`).*

---

## 3. Preparación del Paquete antes de Subir

Dado que BanaHosting compartido no requiere Node.js residente, compila los assets localmente o en CI antes de empaquetar:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Verifica que `public/build/manifest.json` exista y que **no** exista el archivo `public/hot`.

---

## 4. Configuración de Base de Datos MySQL en cPanel

1. En **cPanel → MySQL® Databases**:
   - Crea la base de datos (ej. `usuario_ahorvia`).
   - Crea el usuario MySQL (ej. `usuario_ahorvia_app`) con contraseña robusta.
   - Asigna el usuario a la base de datos con privilegios estándar de aplicación (`SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES, LOCK TABLES`).
2. Verifica que el cotejamiento por defecto sea `utf8mb4_unicode_ci`.

---

## 5. Variables de Entorno (`.env`) en Producción

En `/home/tu_usuario_cpanel/ahorvia_core/.env` (permisos `chmod 600 .env`):

```dotenv
APP_NAME="Ahorvia"
APP_ENV=production
APP_KEY=base64:GENERAR_CON_ARTISAN_KEY_GENERATE
APP_DEBUG=false
APP_URL=https://tu-dominio.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_ES
APP_CURRENCY=USD

AHORVIA_REGISTRATION_ENABLED=false

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=usuario_ahorvia
DB_USERNAME=usuario_ahorvia_app
DB_PASSWORD="TU_PASSWORD_MYSQL_SEGURO"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=mail.tu-dominio.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@tu-dominio.com
MAIL_PASSWORD="TU_PASSWORD_SMTP"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="notificaciones@tu-dominio.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 6. Inicialización desde Terminal de cPanel (SSH)

```bash
cd ~/ahorvia_core

# Permisos seguros
chmod 600 .env
chmod -R 775 storage bootstrap/cache

# Generar APP_KEY (solo en la primera instalación)
php artisan key:generate --force

# Ejecutar migraciones en producción
php artisan migrate --force

# Crear el usuario propietario (registro público está deshabilitado por seguridad)
php artisan ahorvia:create-user

# Optimizar cachés de configuración, rutas y vistas
php artisan optimize
```

---

## 7. Configuración de Cron Jobs en cPanel (Scheduler y Colas)

Ahorvia utiliza **Laravel Scheduler** para tareas periódicas (recurrencias, alertas de vencimientos, recordatorios de metas de ahorro, limpieza de sesiones y ejecución controlada de trabajos en cola `database` sin requerir Supervisor).

En **cPanel → Cron Jobs**, añade **una única tarea** con frecuencia **Una vez por minuto (`* * * * *`)**:

```bash
/usr/local/bin/php /home/tu_usuario_cpanel/ahorvia_core/artisan schedule:run >> /dev/null 2>&1
```

*(Confirma la ruta exacta del binario PHP 8.3 en tu servidor ejecutando `which php` o revisando `/opt/alt/php83/usr/bin/php` si BanaHosting usa CloudLinux PHP Selector).*

---

## 8. SSL, PWA y Almacenamiento Privado

1. **HTTPS obligatorio:** Activa *Force HTTPS Redirect* en **cPanel → Domains**. Tanto los Service Workers de la PWA (`sw.js`) como las cookies `SESSION_SECURE_COOKIE=true` requieren HTTPS.
2. **Almacenamiento privado:** Los comprobantes financieros se almacenan en `storage/app/private`. **No ejecutes `php artisan storage:link` para exponer comprobantes privados.**
3. **Actualizaciones futuras (Zero-Risk Deploy):**
   ```bash
   cd ~/ahorvia_core
   php artisan down
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   php artisan up
   ```

---

## 9. Estrategia de Copias de Seguridad (Backups) y Exportación Contable

1. **Respaldo Automático Integrado de Ahorvia (`ahorvia:backup`):**
   - El Scheduler ejecuta diariamente a las `02:00` el comando:
     ```bash
     php artisan ahorvia:backup --retention=7
     ```
   - Este comando genera una instantánea completa en formato JSON estructurado con todos los asientos, líneas contables, cuentas, categorías, deudas, presupuestos y metas de ahorro en `storage/app/private/backups/`.
   - Limpia automáticamente los archivos anteriores a los días de retención especificados.
   - También puedes ejecutarlo manualmente en cualquier momento:
     ```bash
     php artisan ahorvia:backup --user=tu_correo@dominio.com --retention=15
     ```

2. **Exportación Contable del Libro Diario (`ahorvia:export-ledger`):**
   - Exporta el libro diario completo a formato CSV (con cabeceras contables compatibles con Excel) o JSON para fines de auditoría externa o archivo fiscal:
     ```bash
     # Exportación estándar a CSV
     php artisan ahorvia:export-ledger tu_correo@dominio.com --format=csv

     # Exportación con rango de fechas a JSON
     php artisan ahorvia:export-ledger tu_correo@dominio.com --format=json --from=2026-01-01 --to=2026-12-31
     ```
   - Los archivos generados se guardan de forma segura en `storage/app/private/exports/`.

3. **JetBackup en BanaHosting:** Verifica en cPanel que JetBackup diario esté activo para archivos y bases de datos.
4. **Respaldo MySQL previo a migraciones mayores:**
   ```bash
   mysqldump -u usuario_ahorvia_app -p --single-transaction --routines --triggers usuario_ahorvia | gzip > ~/backups/ahorvia_$(date +%F_%H%M).sql.gz
   chmod 600 ~/backups/ahorvia_*.sql.gz
   ```
   Guarda siempre `~/backups/` fuera de `public_html/`.

---

## 10. Checklist de Verificación Final Pre-Vuelo (BanaHosting cPanel)

Antes de abrir el acceso a los usuarios autorizados, verifica punto por punto:

- [ ] **PHP Version:** PHP 8.3 activo con extensiones `bcmath`, `pdo_mysql`, `intl`, `gd`, `zip`, `openssl`.
- [ ] **Base de datos:** Motor `InnoDB`, cotejamiento `utf8mb4_unicode_ci` y usuario con permisos completos.
- [ ] **Permisos de archivos:** `.env` en `0600`, carpetas `storage` y `bootstrap/cache` en `0775`.
- [ ] **Compilación frontend:** Carpeta `public/build/` con `manifest.json`, `app-*.css` y `app-*.js` generados.
- [ ] **Seguridad web:** Archivos `.htaccess` en raíz y en `public/` activos impidiendo acceso directo a `.env`, `storage/` o código fuente.
- [ ] **Cachés de producción:** Comandos `php artisan config:cache`, `php artisan route:cache` y `php artisan view:cache` ejecutados exitosamente.
- [ ] **Cron Job cPanel:** Tarea programada `* * * * *` llamando a `artisan schedule:run` configurada y operativa.
- [ ] **HTTPS forzado:** Certificado SSL activo y redirección HTTPS automática activada en cPanel.
- [ ] **2FA TOTP:** Probar activación de doble factor en `/configuracion` con Google Authenticator u otra app compatible.

