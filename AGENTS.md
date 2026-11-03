# AGENTS.md — Reglas Persistentes de Arquitectura, Código y Seguridad de Ahorvia

## 1. Identidad del Proyecto

- **Nombre comercial:** Ahorvia
- **Nombre técnico:** `ahorvia`
- **Nombre completo:** Ahorvia — Finanzas Personales
- **Eslogan oficial:** *Tu dinero bajo control. Tus metas más cerca.*
- **Regla estricta de marca:** Utilizar exclusivamente **Ahorvia** en todo el código fuente, configuración, vistas, traducciones, manifiesto PWA, correos, pruebas y documentación. Nunca utilizar otros nombres (como `FinTrack`).

---

## 2. Stack Tecnológico Oficial y Decisiones de Compatibilidad

- **Lenguaje:** PHP `^8.3` (`declare(strict_types=1);` obligatorio en todas las clases PHP de `app/` y `tests/`).
- **Framework Backend:** Laravel `^13.0` (`laravel/framework ^13.17`).
- **Autenticación y Seguridad:** `laravel/fortify ^1.41` con soporte nativo para 2FA TOTP (`pragmarx/google2fa ^9.0` + `bacon/bacon-qr-code ^3.0`), recuperación de contraseña, confirmación de contraseña y rate limiting.
- **UI Reactiva:** `livewire/livewire ^4.4` + Blade + Alpine.js (integrado con Livewire 4; no cargar una segunda instancia de Alpine).
- **Estilos y Gráficos:** Tailwind CSS v4 (vía `@tailwindcss/vite`), paleta oficial fintech de Ahorvia con modo claro/oscuro, y `chart.js` para visualización financiera.
- **Base de Datos Principal:** MySQL 8+ con motor `InnoDB`, charset `utf8mb4` y collation `utf8mb4_unicode_ci`.
- **Calidad y Pruebas:**
  - **PHPUnit `^12.5`** para pruebas unitarias y funcionales (seleccionado sobre `pestphp/pest-plugin-laravel v5` porque este último exige PHP `^8.4`, mientras que PHPUnit 12.5 mantiene compatibilidad nativa con PHP `8.3` local y en BanaHosting cPanel).
  - **Laravel Pint** para estándar de estilo de código.
  - **Larastan `^3.13`** (PHPStan) para análisis estático.

---

## 3. Arquitectura Monolítica Modular

Estructura obligatoria dentro de `app/`:

```text
app/
├── Actions/
│   ├── Accounts/
│   ├── Auth/
│   ├── Budgets/
│   ├── Debts/
│   ├── Savings/
│   └── Transactions/
├── Console/Commands/
├── Data/                  # DTOs inmutables tipados (readonly classes)
├── Enums/                 # Backed Enums (string/int) del dominio
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Jobs/
├── Livewire/
│   ├── Accounts/
│   ├── Budgets/
│   ├── Dashboard/
│   ├── Debts/
│   ├── Notifications/
│   ├── Reports/
│   ├── Savings/
│   ├── Settings/
│   └── Transactions/
├── Models/
│   └── Concerns/
├── Notifications/
├── Policies/
├── Providers/
├── Services/
│   ├── Budget/
│   ├── Debt/
│   ├── Finance/
│   ├── Reporting/
│   └── Savings/
└── Support/               # Utilidades puras (ej. MoneyFormatter, DecimalMath)
```

### Reglas SOLID y Separación de Responsabilidades

1. **Componentes Livewire delgados:** Los componentes en `app/Livewire/` gestionan únicamente estado de interfaz, ciclo de vida UI, autorización (`$this->authorize(...)`) y delegación hacia `Actions` (escritura/mutación) o `Services` (cálculo/consulta). **Prohibido colocar cálculos contables, fórmulas de amortización/ahorro o transacciones SQL complejas dentro de componentes Livewire.**
2. **Patrón Action:** Cada operación de escritura de negocio (crear cuenta, registrar asiento, abonar deuda, recalcular meta de ahorro) se encapsula en una clase `Action` atómica e inyectable.
3. **Patrón Service:** Los cálculos de dominio (saldos contables, capacidad de ahorro a 6 meses, ejecución presupuestaria, métricas de dashboard, reportes) viven en `app/Services/`.
4. **DTOs inmutables:** Preferir clases `final readonly class` en `app/Data/` para transferir resultados financieros entre `Services` y `Livewire`/vistas sin arrays asociativos mágicos.

---

## 4. Reglas Inquebrantables del Motor Financiero

1. **Precisión Monetaria Exacta:**
   - En base de datos: **todos** los importes monetarios deben definirse como `DECIMAL(19,4)`. **Prohibido usar `FLOAT` o `DOUBLE`.**
   - En PHP: operar importes como `string` numérico con `bcmath` (`bcadd`, `bcsub`, `bcmul`, `bcdiv`, `bccomp` con escala `4`). Nunca usar `float` para sumas, saldos, intereses ni cuotas.
2. **Partida Doble Simplificada:**
   - Todo movimiento financiero confirmado genera un `journal_entry` con al menos **2 `journal_lines`**.
   - Regla de balanceo estricto: $\sum \text{débitos} = \sum \text{créditos}$ (`bccomp($totalDebits, $totalCredits, 4) === 0`).
   - Los saldos de las cuentas se derivan de sus movimientos contables confirmados, nunca de contadores mutados sin respaldo contable.
3. **Inmutabilidad y Auditoría:**
   - **Prohibido eliminar físicamente (`DELETE`) asientos contables confirmados.**
   - Toda anulación o corrección de un movimiento confirmado se ejecuta mediante un **asiento de reverso auditable** vinculado al original y registrado en `audit_logs`.
4. **Transacciones ACID e Idempotencia:**
   - Toda operación financiera multi-tabla debe ejecutarse dentro de `DB::transaction(...)` con bloqueo pesimista (`lockForUpdate()`) cuando exista riesgo de condición de carrera.
   - Todo registro de movimiento debe admitir `idempotency_key` única por usuario para evitar duplicados ante dobles clics o reintentos de red.
5. **Prevención de Doble Conteo:**
   - Comprar con tarjeta de crédito registra el gasto y aumenta el pasivo; **pagar la tarjeta** transfiere liquidez desde un activo hacia el pasivo de la tarjeta (con desglose opcional de intereses/comisiones como gasto), sin volver a contabilizar la compra original como gasto.
   - El **Planificador Inteligente de Ahorro (6 meses)** calcula `Ahorro pendiente = Monto objetivo - Ahorro reconocido` y `Cuota mensual = Ahorro pendiente / Meses restantes`, evaluando capacidad de ahorro sin descontar dos veces obligaciones ya incluidas en presupuestos activos.

---

## 5. Multi-usuario, Seguridad y Preparación Comercial (Hotmart)

1. **Aislamiento Total por Usuario (`user_id`):**
   - Toda entidad financiera pertenece a un `user_id`.
   - Toda consulta debe filtrar explícitamente por el usuario autenticado y toda mutación/lectura individual debe pasar por su `Policy` correspondiente.
2. **Separación entre Autenticación y Derechos de Acceso:**
   - El modelo `User` separa credenciales de login respecto al estado de acceso (`is_active` y `access_status` vía `App\Enums\AccessStatus`).
   - El middleware `EnsureUserHasActiveAccess` protege todas las rutas internas de la aplicación, permitiendo en el futuro activar/revocar accesos mediante webhooks de pago único de Hotmart sin reestructurar los módulos financieros.
   - No implementar checkout, suscripciones ni webhooks de Hotmart hasta que se autorice la fase comercial.
3. **Registro Público Deshabilitado:**
   - Mantener `AHORVIA_REGISTRATION_ENABLED=false` por defecto. En la etapa inicial los usuarios se crean mediante seeder controlado o `php artisan ahorvia:create-user`.
4. **Protección de Comprobantes y Sesiones:**
   - Los archivos adjuntos (`attachments`) deben guardarse exclusivamente en el disco privado (`storage/app/private`) y servirse tras verificar `Policy` del propietario.
   - Sesiones cifradas (`SESSION_ENCRYPT=true`), `http_only`, regeneración en login/logout y cabeceras HTTP seguras activas.

---

## 6. Compatibilidad con BanaHosting (cPanel) y Entorno Windows/Linux

1. **Sin demonios residentes obligatorios:** La aplicación debe operar al 100% en hosting compartido cPanel usando MySQL, `SESSION_DRIVER=database`, `CACHE_STORE=database` y `QUEUE_CONNECTION=database` despachado mediante Cron Job estándar (`php artisan schedule:run`). No exigir Redis, Supervisor, Docker, WebSockets ni Node.js en producción.
2. **Assets precompilados:** El despliegue utiliza los artefactos compilados por Vite en `public/build/`.
3. **Portabilidad Windows ↔ Linux:** Respetar mayúsculas/minúsculas exactas en namespaces PSR-4 y nombres de vistas Blade (Linux en cPanel es case-sensitive). Mantener finales de línea `LF` mediante `.gitattributes`.
4. **Flujo de Verificación Obligatorio antes de cerrar cualquier tarea:**
   - `npm run build`
   - `php artisan test`
   - `vendor/bin/pint --test`
   - `vendor/bin/phpstan analyse`

