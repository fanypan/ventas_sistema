<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#4f46e5">
        <title>Sin conexión — {{ $appName }}</title>
        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: Outfit, system-ui, sans-serif;
                background: #f1f5f9;
                color: #0f172a;
                padding: 24px;
            }
            main {
                max-width: 28rem;
                text-align: center;
            }
            h1 {
                margin: 0 0 12px;
                font-size: 1.75rem;
                letter-spacing: -0.03em;
            }
            p {
                margin: 0 0 24px;
                color: #475569;
                line-height: 1.5;
            }
            button {
                appearance: none;
                border: 0;
                border-radius: 8px;
                background: #4f46e5;
                color: #fff;
                font: inherit;
                font-weight: 600;
                padding: 0.55rem 1.25rem;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Sin conexión</h1>
            <p>Para cobrar o consultar clientes hace falta red. Revisá el Wi‑Fi y reintentá.</p>
            <button type="button" onclick="location.reload()">Reintentar</button>
        </main>
    </body>
</html>
