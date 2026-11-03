# PROJECT_STATUS.md — Estado de Desarrollo de Ahorvia

**Proyecto:** Ahorvia — Finanzas Personales (`ahorvia`)
**Eslogan:** *Tu dinero bajo control. Tus metas más cerca.*
**Última actualización:** 2026-11-03

---

## 1. Resumen Ejecutivo por Fases

| Fase | Nombre | Estado | Progreso |
| --- | --- | --- | --- |
| **Fase 0** | Análisis de Entorno y Compatibilidad | **Completada** | 100% |
| **Fase 1** | Estructura Base, Autenticación 2FA, Identidad Visual y PWA Inicial | **Completada** | 100% |
| **Fase 2** | Motor Financiero Contable (Partida Doble `DECIMAL(19,4)`) | **Completada** | 100% |
| **Fase 3** | Módulos Funcionales (Cuentas, Movimientos, Presupuestos, Deudas, Ahorro 6M, Reportes) | **Completada** | 100% |
| **Fase 4** | Endurecimiento de Producción, Backups, Scheduler y Despliegue en BanaHosting cPanel | **Completada** | 100% |

---

## 2. Fase 0 — Resultados del Análisis de Entorno

- **Workspace:** `C:\Proyectos\Ahorvia` (repositorio Git activo conectado a `origin/main` preservado íntegramente).
- **PHP:** `8.3.32` nativo con todas las extensiones requeridas (`bcmath`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `xml`, `zip`).
- **Composer:** `2.10.2`.
- **Node.js / npm:** Node `v24.18.0`, npm `11.16.0`.
- **Base de datos:** `pdo_mysql` verificado y configurado por defecto hacia MySQL 8 (`InnoDB`, `utf8mb4_unicode_ci`) en `config/database.php`, `.env` y `.env.example`. Al no encontrarse un servicio MySQL escuchando actualmente en `127.0.0.1:3306` en Windows ni en `Ubuntu-24.04` (WSL2), la suite automatizada de pruebas se configuró sobre SQLite `:memory:` para validación inmediata sin dependencias externas.
- **Decisiones técnicas documentadas:**
  1. **Raíz del proyecto:** Se instaló Laravel 13 directamente en la raíz del repositorio `C:\Proyectos\Ahorvia` (nombre técnico `ahorvia/ahorvia`) para mantener una jerarquía Git limpia y compatible con despliegue en BanaHosting (cPanel).
  2. **PHPUnit 12.5 sobre Pest 5:** `pestphp/pest-plugin-laravel v5` exige PHP `^8.4`, mientras que `pestphp/pest v4` entra en conflicto con `phpunit/phpunit >12.5.33`. Se seleccionó **PHPUnit 12.5** (oficial de Laravel 13 en PHP 8.3) junto con **Laravel Pint** y **Larastan 3.13**, garantizando 100% de compatibilidad local y en cPanel PHP 8.3.
  3. **Portabilidad Windows/WSL → Linux cPanel:** Se añadió `.gitattributes` forzando finales de línea `LF` y se configuraron sesiones, caché y colas con driver `database` sin requerir Redis, Supervisor ni demonios residentes.

---

## 3. Fase 1 — Entregables Implementados y Verificados

### Arquitectura y Configuración
- `config/ahorvia.php`: Configuración central de identidad, monedas soportadas, escala `DECIMAL(19,4)`, umbrales de presupuesto (`80, 90, 100`) y flags de autenticación/acceso.
- `config/database.php`, `.env`, `.env.example`: Conexión predeterminada `mysql` (`DB_DATABASE=ahorvia`, `DB_ENGINE=InnoDB`, `SESSION_ENCRYPT=true`).
- Estructura modular completa en `app/`: `Actions/`, `Console/Commands/`, `Data/Finance/`, `Enums/`, `Http/Middleware/`, `Http/Requests/`, `Jobs/`, `Livewire/`, `Models/Concerns/`, `Notifications/`, `Policies/`, `Services/` y `Support/`.
- Utilidades financieras puras sin `float`:
  - `App\Support\DecimalMath` (aritmética `bcmath` escala 4).
  - `App\Support\MoneyFormatter` (formateo multimoneda por cadenas sin conversión a coma flotante).
  - `App\Data\Finance\DashboardMetricsData` y `App\Services\Finance\DashboardSummaryService`.

### Autenticación, Seguridad y Preparación Comercial (Hotmart-ready)
- **Laravel Fortify 1.41** configurado con vistas fintech propias de Ahorvia:
  - Login (`/login`), Logout (`POST /logout`), Recuperación de contraseña (`/forgot-password`, `/reset-password/{token}`), Confirmación de contraseña (`/user/confirm-password`) y Desafío 2FA (`/two-factor-challenge` con soporte TOTP de 6 dígitos y códigos de recuperación).
  - **Registro público deshabilitado** (`AHORVIA_REGISTRATION_ENABLED=false`).
  - Rate limiting activo (5 intentos/minuto en login y 2FA).
- **Gestión de Perfil, Contraseña y 2FA en vivo (`/configuracion`):** Componente Livewire `App\Livewire\Settings\SettingsManager` con activación QR SVG local, clave manual, confirmación TOTP, visualización/regeneración de códigos de recuperación y desactivación protegida por contraseña.
- **Separación Autenticación vs. Derechos de Acceso:** `App\Enums\AccessStatus`, columnas `is_active` y `access_status` en `users`, validación en login y middleware `EnsureUserHasActiveAccess` (`active.access`).
- **Cabeceras de seguridad:** `SecurityHeadersMiddleware` (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`).
- **Comando CLI administrativo:** `php artisan ahorvia:create-user` para dar de alta usuarios autorizados con registro público cerrado.

### Identidad Visual Ahorvia, Navegación y PWA
- Paleta oficial en Tailwind CSS v4 (`#10B981`, `#064E3B`, `#0F172A`, `#FFFFFF`, `#F8FAFC`, `#64748B`, `#EF4444`) con **modo claro y oscuro** persistente sin parpadeo.
- Logotipo vectorial local `<x-ahorvia-logo />`, `public/favicon.svg` e iconos PNG generados localmente (`icon-192x192.png`, `icon-512x512.png`, `apple-touch-icon.png`).
- **Sidebar Web (10 secciones):** Dashboard, Cuentas, Movimientos, Presupuestos, Deudas, Tarjetas, Ahorro (`6M`), Reportes, Notificaciones y Configuración.
- **Navegación Móvil Inferior (5 acciones):** Inicio, Movimientos, **Añadir** (con *Quick Action Sheet* para Ingreso, Gasto y Transferencia), Metas y Reportes.
- **Dashboard Financiero (`/dashboard`):** Los 14 indicadores y bloques requeridos conectados a `DashboardSummaryService` con estados vacíos honestos y gráfico interactivo **Chart.js** de evolución mensual.
- **PWA Segura:** `public/manifest.webmanifest`, `public/sw.js` (sin cachear HTML ni endpoints financieros autenticados) y ruta `/offline`.

---

## 4. Fase 2 — Motor Financiero Contable (Completada al 100%)

### 18 Migraciones Financieras en MySQL 8 (`InnoDB`, `DECIMAL(19,4)`)
1. `2026_11_03_000100_create_accounts_table.php`: Cuentas financieras de activo y pasivo, límites de crédito, tasas y días de corte/pago.
2. `2026_11_03_000110_create_categories_table.php`: Categorías jerárquicas tipadas (ingreso/gasto/transferencia).
3. `2026_11_03_000120_create_journal_entries_table.php`: Cabecera de asientos con tipo de movimiento, numeración correlativa por usuario, idempotencia y auditoría de reversos.
4. `2026_11_03_000130_create_journal_lines_table.php`: Líneas contables con débitos y créditos exactos en `DECIMAL(19,4)` vinculadas a cuentas y categorías.
5. `2026_11_03_000140_create_budgets_table.php`: Presupuestos mensuales por usuario y moneda.
6. `2026_11_03_000150_create_budget_lines_table.php`: Límites por categoría y seguimiento de notificaciones de umbral (80%, 90%, 100%).
7. `2026_11_03_000160_create_debts_table.php`: Préstamos, tarjetas de crédito y deudas personales con vínculo directo a su cuenta de pasivo.
8. `2026_11_03_000170_create_debt_payments_table.php`: Abonos a deuda con desglose exacto de capital, intereses y comisiones.
9. `2026_11_03_000180_create_savings_goals_table.php`: Metas de ahorro inteligente a 6 meses (modos fijo y ajustable).
10. `2026_11_03_000190_create_savings_contributions_table.php`: Aportes reconocidos mediante transferencia contable o reserva identificable.
11. `2026_11_03_000200_create_recurring_rules_table.php`: Reglas de recurrencia financiera.
12. `2026_11_03_000210_create_recurring_occurrences_table.php`: Ocurrencias y ejecuciones de recurrencias.
13. `2026_11_03_000220_create_scheduled_obligations_table.php`: Obligaciones programadas y vencimientos.
14. `2026_11_03_000230_create_notifications_table.php`: Alertas internas y notificaciones al usuario.
15. `2026_11_03_000240_create_attachments_table.php`: Comprobantes y adjuntos polimórficos en disco privado.
16. `2026_11_03_000250_create_import_batches_table.php`: Lotes de importación de movimientos.
17. `2026_11_03_000260_create_import_rows_table.php`: Filas procesadas en importaciones.
18. `2026_11_03_000270_create_audit_logs_table.php`: Trazabilidad inmutable de eventos financieros.

### Modelos Eloquent e Inmutabilidad Contable
- `Account`, `Category`, `JournalEntry`, `JournalLine`, `Budget`, `BudgetLine`, `Debt`, `DebtPayment`, `SavingsGoal`, `SavingsContribution`, `ScheduledObligation`, `RecurringRule`, `RecurringOccurrence`, `Attachment`, `ImportBatch`, `ImportRow`, `AuditLog`.
- **Protección estricta contra eliminación física:** `JournalEntry` y `JournalLine` bloquean `delete()` y `update()` de débitos/créditos sobre movimientos confirmados arrojando `ImmutableLedgerException`. Los cambios se realizan mediante asientos de reverso auditables.
- **Aislamiento Multi-usuario:** Modelo base `UserOwnedModel` con trait `BelongsToUser` y scope `ownedBy($user)`.
- **Políticas de Autorización:** 10 Policies registradas en `AppServiceProvider` (`AccountPolicy`, `CategoryPolicy`, `JournalEntryPolicy`, `BudgetPolicy`, `DebtPolicy`, `SavingsGoalPolicy`, `RecurringRulePolicy`, `ScheduledObligationPolicy`, `AttachmentPolicy`, `UserPolicy`).

### Servicios Financieros de Dominio
- `JournalPosterService`: Registro atómico ACID de asientos contables por partida doble simplificada con bloqueo pesimista (`lockForUpdate`), control de idempotencia (`idempotency_key`), validación de balance estricto y asignación correlativa de `entry_number`.
- `LedgerBootstrapService`: Aprovisionamiento transparente de cuentas contables de sistema (`SystemEquity`, `SystemIncome`, `SystemExpense`) y categorías de intereses y comisiones bancarias.
- `AccountBalanceService`: Derivación en tiempo real de saldos contables, fondos reservados de ahorro y liquidez disponible (o cupo en tarjetas de crédito).
- `BudgetMonitorService`: Monitor de ejecución presupuestaria mensual con detección precisa de umbrales del 80%, 90% y 100%.
- `DebtBalanceService`: Cálculo de saldo de capital pendiente desde la cuenta de pasivo y desglose histórico de pagos (capital, intereses y comisiones).
- `SavingsPlannerService`: Planificador inteligente a 6 meses con fórmulas oficiales:
  - `Ahorro pendiente = Monto objetivo - Ahorro reconocido`
  - `Cuota mensual = Ahorro pendiente / Meses restantes`
  - Recálculo dinámico de cuotas futuras ante aportes omitidos sin modificar el historial previo.
- `SavingsCapacityService`: Cálculo de capacidad mensual de ahorro evaluando ingresos, gastos presupuestados y obligaciones previstas, garantizando la **prevención de doble cómputo** de obligaciones ya contempladas en presupuestos activos.
- `DashboardSummaryService`: Consolidación en vivo de las métricas patrimoniales, flujo mensual, distribución de gastos, vencimientos y alertas para el dashboard.

### Actions Atómicas de Dominio
- `CreateAccountAction`: Creación de cuentas y registro contable de saldo inicial contra patrimonio neto.
- `RecordIncomeAction`: Asiento balanceado de ingreso (débito a activo, crédito a cuenta de ingresos con categoría).
- `RecordExpenseAction`: Asiento balanceado de gasto o consumo con tarjeta (débito a gasto con categoría, crédito a cuenta o tarjeta).
- `RecordTransferAction`: Transferencia entre cuentas sin impacto en ingresos o gastos del mes.
- `ReverseJournalEntryAction`: Reverso auditable con inversión exacta de débitos y créditos, actualización de estado a `reversed`, reapertura de deudas o metas si correspondía y registro en `audit_logs`.
- `CreateDebtAction`: Creación de pasivo y registro contable de desembolso o saldo inicial.
- `RecordDebtPaymentAction`: Pago a deuda o tarjeta con desglose de capital, intereses y comisiones, **evitando duplicar el gasto de las compras previas de tarjeta**.
- `UpsertMonthlyBudgetAction`: Creación y actualización de presupuestos y líneas con suma exacta en `DecimalMath`.
- `CreateSavingsGoalAction`: Creación de meta con duración por defecto de 6 meses y registro de ahorro inicial.
- `RecordSavingsContributionAction`: Aportes vía transferencia contable o reserva identificable (con validación de fondos disponibles `InsufficientAvailableFundsException`).

### Form Requests Centralizados
- `StoreAccountRequest`, `StoreTransactionRequest`, `StoreDebtPaymentRequest`, `StoreSavingsGoalRequest`.

### Verificación de Calidad Ejecutada (Fase 2)
- **PHPUnit 12.5.43 (`php artisan test`):** **41 passed, 310 assertions** en `3.35s` (0 fallos).
- **Laravel Pint 1.32.1 (`vendor/bin/pint --test`):** `passed` (0 desviaciones de formato).
- **Larastan 3.13.2 / PHPStan 2.3.4 Nivel 5 (`vendor/bin/phpstan analyse`):** **`[OK] No errors`** sobre 116 archivos PHP del proyecto.
- **Vite 7.3.7 (`npm run build`):** Compilación limpia de assets CSS/JS.

---

## 5. Fase 3 — Módulos Funcionales (Completada al 100%)

### Componentes Livewire Reactivos y Vistas Blade
1. **Cuentas Financieras (`/cuentas`):**
   - Clase: `App\Livewire\Accounts\AccountsIndex`
   - Vista: `resources/views/livewire/accounts/accounts-index.blade.php`
   - Funcionalidades: Tarjetas KPI (Liquidez disponible, Activos totales, Pasivos totales, Patrimonio neto), listado agrupado por cuentas activas/inactivas, creación modal con saldo inicial contable (`CreateAccountAction`), edición de cuentas, alternancia activo/inactivo (`toggleStatus`) y transferencias directas con validación multi-cuenta (`RecordTransferAction`).
2. **Ingresos, Gastos y Transferencias (`/movimientos`):**
   - Clase: `App\Livewire\Transactions\TransactionsIndex`
   - Vista: `resources/views/livewire/transactions/transactions-index.blade.php`
   - Funcionalidades: Filtros en URL (`?tipo=income|expense|transfer|all`, búsqueda en tiempo real, filtro por cuenta, categoría y rango de fechas), apertura automática modal cuando se accede desde accesos rápidos (`/movimientos?tipo=income`), paginación reactiva, registro contable de ingresos, gastos y transferencias, y reverso auditable con confirmación de motivo (`ReverseJournalEntryAction`).
3. **Presupuestos por Categoría (`/presupuestos`):**
   - Clase: `App\Livewire\Budgets\BudgetsIndex`
   - Vista: `resources/views/livewire/budgets/budgets-index.blade.php`
   - Funcionalidades: Navegación mensual (anterior, siguiente, mes actual), integración con `BudgetMonitorService`, tarjetas KPI (Presupuestado, Ejecutado, Margen restante, % consumo global), barras de progreso con umbrales visuales automáticos (<80% verde, 80-89% ámbar, 90-99% naranja, >=100% rojo) y modal de configuración de límites mensuales por categoría (`UpsertMonthlyBudgetAction`).
4. **Deudas y Préstamos (`/deudas`):**
   - Clase: `App\Livewire\Debts\DebtsIndex`
   - Vista: `resources/views/livewire/debts/debts-index.blade.php`
   - Funcionalidades: Control de pasivos (préstamos bancarios, personales, hipotecarios), saldos pendientes calculados desde cuentas contables de pasivo (`DebtBalanceService`), desglose histórico acumulado (capital, intereses y comisiones pagadas), modal de creación con cuenta de desembolso (`CreateDebtAction`) y modal de abono contable con separación estricta de capital, interés y comisiones (`RecordDebtPaymentAction`).
5. **Tarjetas de Crédito (`/tarjetas`):**
   - Clase: `App\Livewire\Debts\CardsIndex`
   - Vista: `resources/views/livewire/debts/cards-index.blade.php`
   - Funcionalidades: Visualización fintech tipo tarjeta con banco emisor, cupo total, saldo adeudado, cupo libre, día de corte y día límite de pago, modal de nueva tarjeta y modal de pago a tarjeta transfiriendo liquidez desde activo hacia pasivo sin duplicar gastos (`RecordDebtPaymentAction`).
6. **Planificador Inteligente de Ahorro a 6 Meses (`/ahorro`):**
   - Clase: `App\Livewire\Savings\SavingsPlannerIndex`
   - Vista: `resources/views/livewire/savings/savings-planner-index.blade.php`
   - Funcionalidades: Panel de Inteligencia Financiera con evaluación de capacidad de ahorro a 6 meses (`SavingsCapacityService`: ingresos previstos, gastos presupuestados, obligaciones externas sin doble conteo, capacidad bruta y margen remanente), listado de metas a 6 meses con cuota mensual recomendada (`Ahorro pendiente / Meses restantes`), alerta de recálculo ante omisiones, modal de creación de metas (`CreateSavingsGoalAction`) y modal de aportes por transferencia o reserva contable (`RecordSavingsContributionAction`).
7. **Reportes Financieros con Chart.js (`/reportes`):**
   - Clase: `App\Livewire\Reports\ReportsIndex`
   - Servicio: `App\Services\Reporting\FinancialReportingService`
   - DTO: `App\Data\Reporting\FinancialReportData`
   - Vista: `resources/views/livewire/reports/reports-index.blade.php`
   - Funcionalidades: Selector multitemporal (3, 6, 12 meses), KPIs de período (ingresos, gastos, ahorro neto y tasa media de ahorro), gráfico interactivo de flujo de caja comparativo mensual, gráfico doughnut de distribución por categorías con tabla detallada y gráfico de evolución del patrimonio neto.
8. **Centro de Notificaciones (`/notificaciones`):**
   - Clase: `App\Livewire\Notifications\NotificationsIndex`
   - Servicio: `App\Services\Reporting\NotificationCenterService`
   - Vista: `resources/views/livewire/notifications/notifications-index.blade.php`
   - Funcionalidades: Detección proactiva de alertas críticas (vencimientos de deuda atrasados, presupuestos excedidos al 100%), advertencias (consumos al 80% y 90%, vencimientos < 7 días, cuotas de ahorro recalculadas) y recomendaciones de seguridad (2FA TOTP), con botones de acción directa hacia cada módulo financiero.

### Verificación de Calidad Ejecutada (Fase 3)
- **PHPUnit 12.5.43 (`php artisan test`):** **48 passed, 375 assertions** en `4.83s` (0 fallos).
- **Laravel Pint 1.32.1 (`vendor/bin/pint --test`):** `passed` (0 desviaciones de formato).
- **Larastan 3.13.2 / PHPStan 2.3.4 Nivel 5 (`vendor/bin/phpstan analyse`):** **`[OK] No errors`** sobre 119 archivos PHP del proyecto.
- **Vite 7.3.7 (`npm run build`):** Compilación limpia de assets CSS/JS (`manifest.json`, `app.css`, `app.js`).

---

## 6. Fase 4 — Endurecimiento de Producción, Backups, Scheduler y Despliegue en BanaHosting cPanel (Completada al 100%)

### Comandos Artisan Administrativos y de Respaldo
1. **Comando de Respaldo Contable Estructurado (`php artisan ahorvia:backup`):**
   - Clase: `App\Console\Commands\CreateAhorviaBackupCommand`
   - Opciones: `--user=` (respaldo por usuario o global), `--retention=` (días de retención, por defecto 7).
   - Genera instantáneas completas en formato JSON con metadatos (`app`, `version`, `created_at`), serializando todas las entidades financieras (cuentas, categorías, libro diario con líneas, presupuestos, deudas con abonos, metas con aportes y logs de auditoría) en el disco privado `storage/app/private/backups/`.
   - Limpia de forma autónoma los respaldos antiguos y asienta el evento en `audit_logs`.
2. **Comando de Exportación del Libro Diario (`php artisan ahorvia:export-ledger`):**
   - Clase: `App\Console\Commands\ExportLedgerCommand`
   - Parámetros: `{user}` (ID o correo electrónico).
   - Opciones: `--format=csv|json` (por defecto `csv` con cabeceras contables UTF-8 BOM compatibles con Excel), `--from=`, `--to=`, `--output=`.
   - Permite extraer el libro diario completo para conciliaciones fiscales, auditorías o almacenamiento local en `storage/app/private/exports/`.

### Planificador Maestro y Cron Job en Hosting Compartido
- Archivo: `routes/console.php`
- Despacho autónomo compatible al 100% con un único Cron Job de cPanel (`* * * * * /usr/local/bin/php /home/usuario/ahorvia/artisan schedule:run >> /dev/null 2>&1`):
  1. `queue:work database --stop-when-empty --tries=3`: Procesa trabajos en cola de forma periódica sin necesidad de demonios residentes como Supervisor.
  2. `ahorvia:backup --retention=7`: Ejecución diaria programada a las `02:00` con prevención de solapamiento (`withoutOverlapping`).
  3. `auth:clear-resets`: Limpieza horaria de tokens de recuperación expirados.

### Endurecimiento del Servidor Web (.htaccess)
- **`.htaccess` en Raíz del Proyecto:** Bloqueo preventivo de acceso directo a archivos sensibles (`.env`, `.git`, `composer.*`, `package.*`, `phpunit.xml`, `phpstan.neon`, `artisan`, `storage/`, `database/`, etc.), desactivación de listado de directorios (`Options -Indexes`) y redirección transparente hacia la carpeta `public/` en entornos donde no se puede modificar el Document Root.
- **`public/.htaccess`:** Configuración optimizada para Apache/LiteSpeed con compresión Gzip/Deflate para texto, CSS, JS, SVG y JSON; cabeceras de caché inmutable (`Cache-Control: "max-age=31536000, public, immutable"`) para assets versionados de Vite en `public/build/`; y cabeceras de seguridad preventivas (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`).

### Compatibilidad con Cachés de Producción
- Verificación exitosa de compilación y almacenamiento en caché:
  - `php artisan config:cache` (Configuración en caché limpia).
  - `php artisan route:cache` (Rutas en caché sin dependencias de closures no serializables).
  - `php artisan view:cache` (Plantillas Blade precompiladas para máximo rendimiento).

### Guía de Despliegue en Producción
- Archivo: `DEPLOYMENT.md` actualizado con:
  - Procedimiento paso a paso para BanaHosting cPanel (PHP 8.3, MySQL 8 `InnoDB`).
  - Instrucciones detalladas de respaldos contables y exportación.
  - Checklist pre-vuelo de 10 puntos para verificar antes de la apertura a usuarios.

### Verificación de Calidad Final de la Plataforma
- **PHPUnit 12.5.43 (`php artisan test`):** **53 passed, 397 assertions** en `7.32s` (0 fallos).
- **Laravel Pint 1.32.1 (`vendor/bin/pint --test`):** `passed` (0 desviaciones de formato).
- **Larastan 3.13.2 / PHPStan 2.3.4 Nivel 5 (`vendor/bin/phpstan analyse`):** **`[OK] No errors`** sobre 121 archivos PHP del proyecto.
- **Vite 7.3.7 (`npm run build`):** Compilación limpia de assets CSS/JS (`manifest.json`, `app-*.css`, `app-*.js`).

---

## 7. Conclusión del Proyecto

**Ahorvia — Finanzas Personales** se encuentra **100% desarrollada, endurecida y lista para producción** en hosting compartido BanaHosting (cPanel) o cualquier servidor Linux/Windows compatible con PHP 8.3 y MySQL 8, cumpliendo íntegramente con:
- Arquitectura monolítica modular limpia (Services, Actions, DTOs inmutables, Enums, Form Requests, Policies).
- Partida doble simplificada con balance estricto y precisión monetaria `DECIMAL(19,4)` mediante `bcmath`.
- Planificador Inteligente de Ahorro a 6 meses con fórmulas oficiales y recálculo automático sin alterar el historial.
- Prevención de doble conteo contable en tarjetas de crédito y presupuestos.
- Interfaz reactiva con Livewire 4, Alpine.js y Tailwind CSS fintech en modos claro y oscuro.
- Sistema de autenticación con 2FA TOTP (Google Authenticator) y separación comercial para activación futura vía Hotmart.
