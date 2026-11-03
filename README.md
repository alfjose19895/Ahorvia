# Ahorvia — Sistema Inteligente de Finanzas Personales

> **Tu dinero bajo control. Tus metas más cerca.**
> Controla tus gastos, organiza tus deudas y construye tu plan de ahorro.

**Ahorvia** es una plataforma web y móvil tipo **PWA** diseñada para la administración integral de finanzas personales con rigor contable: cuentas, ingresos, gastos, transferencias, tarjetas de crédito, préstamos, presupuestos mensuales y un **planificador inteligente de ahorro a seis meses** con cálculo de capacidad financiera real.

---

## Características Principales

- **Seguridad Fintech:** Autenticación robusta con verificación en dos pasos (**2FA TOTP** + códigos de recuperación), sesiones cifradas, cabeceras HTTP de protección, rate limiting y registro público deshabilitado por defecto.
- **Arquitectura Monolítica Modular:** Separación estricta de responsabilidades con `Services`, `Actions`, `DTOs`, `Enums`, `Form Requests`, `Policies` y componentes `Livewire` ligeros sin lógica contable acoplada.
- **Precisión Monetaria `DECIMAL(19,4)`:** Aritmética exacta mediante `bcmath` (`App\Support\DecimalMath`), partida doble simplificada e inmutabilidad mediante reversos auditables (cero `FLOAT`).
- **Experiencia Fintech Mobile-First & PWA:** Identidad visual propia de Ahorvia con modo claro/oscuro, sidebar de escritorio, barra de navegación móvil con acceso rápido (**Añadir**), gráficos con **Chart.js** y Service Worker seguro que no almacena respuestas financieras sensibles sin conexión.
- **Preparación Comercial y Multi-usuario:** Aislamiento por `user_id` y separación entre autenticación y estado de acceso (`AccessStatus`), lista para futura comercialización mediante pago único (Hotmart) sin sobreingeniería en el MVP.
- **Compatible con BanaHosting (cPanel):** Diseñada para operar sobre PHP 8.3 + MySQL 8 (`InnoDB`) + Cron Job estándar sin depender de Docker, Redis, Supervisor ni procesos Node.js residentes en producción.

---

## Stack Tecnológico

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13, Eloquent ORM, Laravel Fortify (2FA TOTP) |
| Frontend | Blade, Livewire 4, Alpine.js, Tailwind CSS 4, Chart.js 4, Vite 7 |
| Base de Datos | MySQL 8+ (`InnoDB`, `utf8mb4_unicode_ci`, importes `DECIMAL(19,4)`) |
| Calidad | PHPUnit 12.5, Laravel Pint, Larastan 3 (PHPStan) |

---

## Requisitos Locales

- **PHP** `8.3` o superior con extensiones `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `xml`, `zip`.
- **Composer** `2.8+`
- **Node.js** `20+` y **npm** `10+`
- **MySQL** `8.0+` (para ejecución con base de datos persistente; la suite de pruebas automatizadas utiliza SQLite `:memory:` de forma aislada).

---

## Instalación y Configuración Local

1. **Clonar el repositorio e ingresar al directorio:**
   ```bash
   git clone https://github.com/alfjose19895/Ahorvia.git
   cd Ahorvia
   ```

2. **Instalar dependencias PHP y Frontend:**
   ```bash
   composer install
   npm install
   ```

3. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configurar conexión MySQL 8 en `.env`:**
   Crea una base de datos vacía llamada `ahorvia` con cotejamiento `utf8mb4_unicode_ci` y ajusta tus credenciales locales en `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ahorvia
   DB_USERNAME=root
   DB_PASSWORD=tu_password_local
   ```
   *(Nota opcional de desarrollo rápido: si aún no tienes el servicio MySQL levantado localmente, puedes probar temporalmente cambiando `DB_CONNECTION=sqlite`, creando un archivo vacío `database/database.sqlite` y ejecutando `php artisan migrate`, manteniendo siempre MySQL 8 como objetivo principal).*

5. **Ejecutar migraciones:**
   ```bash
   php artisan migrate
   ```

6. **Crear tu usuario propietario (el registro público está deshabilitado):**
   ```bash
   php artisan ahorvia:create-user --name="Tu Nombre" --email="tu@correo.com" --currency="USD"
   ```

7. **Compilar assets y levantar servidor local:**
   ```bash
   npm run build
   php artisan serve
   ```
   Accede desde el navegador a `http://localhost:8000`.
   Para desarrollo con recarga activa de estilos/JS ejecuta en paralelo `npm run dev` (o `composer run dev`).

---

## Verificación de Calidad y Pruebas

```bash
# Ejecutar suite de pruebas unitarias y funcionales (PHPUnit 12.5)
php artisan test

# Verificar formato de código (Laravel Pint)
composer run lint

# Ejecutar análisis estático (Larastan / PHPStan)
composer run analyse

# Compilar assets de producción (Vite 7)
npm run build
```

---

## Documentación del Proyecto

- [`AGENTS.md`](./AGENTS.md): Reglas persistentes de arquitectura, partida doble `DECIMAL(19,4)`, SOLID y seguridad.
- [`PROJECT_STATUS.md`](./PROJECT_STATUS.md): Estado actual del desarrollo, fases completadas, decisiones técnicas y hoja de ruta.
- [`DEPLOYMENT.md`](./DEPLOYMENT.md): Guía detallada de despliegue seguro en **BanaHosting mediante cPanel**.
