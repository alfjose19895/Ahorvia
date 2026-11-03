<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Planificador Maestro de Ahorvia (Compatible con Cron Job de cPanel)
|--------------------------------------------------------------------------
|
| En cPanel, configurar una única tarea Cron cada minuto:
| * * * * * /usr/local/bin/php /home/usuario/ahorvia/artisan schedule:run >> /dev/null 2>&1
|
| Este programador despacha de forma autónoma:
| 1. Procesamiento de colas database sin requerir demonios residentes (Supervisor).
| 2. Respaldos contables automáticos diarios a disco privado.
| 3. Mantenimiento y depuración de tokens expirados.
|
*/

// 1. Despachar trabajos en cola de base de datos de manera controlada
Schedule::command('queue:work database --stop-when-empty --tries=3')
    ->everyMinute()
    ->withoutOverlapping();

// 2. Respaldo contable automático diario
Schedule::command('ahorvia:backup --retention=7')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// 3. Limpieza de tokens de reseteo de contraseña expirados
Schedule::command('auth:clear-resets')
    ->hourly();
