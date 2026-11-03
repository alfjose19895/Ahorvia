<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F172A">
    <title>Sin conexión — Ahorvia</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <style>
        :root {
            color-scheme: dark light;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            background-color: #0F172A;
            color: #F8FAFC;
        }
        .card {
            max-width: 26rem;
            width: 100%;
            background: #1E293B;
            border: 1px solid rgba(148, 163, 184, 0.18);
            border-radius: 1.25rem;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }
        .logo {
            width: 3.5rem;
            height: 3.5rem;
            margin: 0 auto 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #064E3B, #10B981);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 0.925rem;
            line-height: 1.55;
            color: #94A3B8;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            border: none;
            background-color: #10B981;
            color: #064E3B;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
        }
        .slogan {
            margin-top: 1.25rem;
            font-size: 0.78rem;
            color: #64748B;
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="logo" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 64 64" fill="none">
                <path d="M15 45L27.5 23.5L35.5 34L47 18" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M39 18H47V26" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="47" cy="45" r="4.5" fill="#A7F3D0"/>
            </svg>
        </div>
        <h1>Estás sin conexión a Internet</h1>
        <p>Por seguridad financiera, Ahorvia no almacena saldos ni movimientos sensibles sin conexión. Reconéctate para sincronizar tus finanzas.</p>
        <button type="button" class="btn" onclick="window.location.reload()">Reintentar conexión</button>
        <div class="slogan">Ahorvia — Tu dinero bajo control. Tus metas más cerca.</div>
    </main>
</body>
</html>

