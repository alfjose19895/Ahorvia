<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Identidad de Marca Ahorvia
    |--------------------------------------------------------------------------
    */
    'brand' => [
        'name' => env('APP_NAME', 'Ahorvia'),
        'technical_name' => 'ahorvia',
        'full_name' => 'Ahorvia — Finanzas Personales',
        'slogan' => 'Tu dinero bajo control. Tus metas más cerca.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Parámetros Financieros y Contables
    |--------------------------------------------------------------------------
    */
    'finance' => [
        'default_currency' => env('APP_CURRENCY', 'USD'),
        'supported_currencies' => [
            'USD' => ['name' => 'Dólar estadounidense', 'symbol' => '$'],
            'EUR' => ['name' => 'Euro', 'symbol' => '€'],
            'MXN' => ['name' => 'Peso mexicano', 'symbol' => '$'],
            'COP' => ['name' => 'Peso colombiano', 'symbol' => '$'],
            'PEN' => ['name' => 'Sol peruano', 'symbol' => 'S/'],
            'CLP' => ['name' => 'Peso chileno', 'symbol' => '$'],
            'ARS' => ['name' => 'Peso argentino', 'symbol' => '$'],
        ],
        'decimal_precision' => 19,
        'decimal_scale' => 4,
        'display_decimals' => 2,
        'budget_alert_thresholds' => [80, 90, 100],
        'savings_planner_default_months' => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Autenticación y Preparación Comercial (Hotmart-ready)
    |--------------------------------------------------------------------------
    | El registro público permanece deshabilitado inicialmente. La separación
    | entre autenticación y estado de acceso prepara la plataforma para futura
    | activación por pago único sin acoplar pasarelas en el MVP.
    */
    'auth' => [
        'registration_enabled' => (bool) env('AHORVIA_REGISTRATION_ENABLED', false),
        'enforce_active_access' => true,
        'login_max_attempts' => 5,
        'two_factor_max_attempts' => 5,
    ],
];
