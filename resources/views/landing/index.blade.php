<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brand }} — Sistema de ventas para Paraguay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0f172a; --muted:#475569; --accent:#0f766e; --bg:#f8fafc; }
        * { box-sizing: border-box; }
        body { margin:0; font-family:Outfit,system-ui,sans-serif; color:var(--ink); background:var(--bg); }
        a { color:var(--accent); }
        header, footer { max-width:1100px; margin:0 auto; padding:24px 20px; display:flex; justify-content:space-between; align-items:center; }
        .hero { max-width:1100px; margin:0 auto; padding:48px 20px 24px; }
        .hero h1 { font-size:clamp(2rem,4vw,3.2rem); margin:0 0 12px; }
        .hero p { color:var(--muted); font-size:1.15rem; max-width:40rem; }
        .cta { display:inline-block; background:var(--accent); color:#fff; text-decoration:none; padding:12px 20px; border-radius:10px; font-weight:600; margin-top:16px; }
        .plans { max-width:1100px; margin:0 auto; padding:24px 20px 64px; display:grid; gap:18px; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); }
        .card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; }
        .card h2 { margin:0 0 8px; }
        .price { font-size:1.6rem; font-weight:700; }
        .price span { font-size:.95rem; color:var(--muted); font-weight:500; }
        ul { padding-left:18px; color:var(--muted); }
        .staff { font-size:.9rem; color:var(--muted); }
    </style>
</head>
<body>
    <header>
        <strong>{{ $brand }}</strong>
        <a class="staff" href="{{ route('platform.login') }}">Acceso staff</a>
    </header>
    <section class="hero">
        <h1>Cobrás en el mostrador. Cerrás caja con números claros.</h1>
        <p>POS, stock, créditos y caja para comercios de Paraguay. Te damos de alta nosotros, pagás por transferencia o efectivo, y entrás por tu subdominio.</p>
        <a class="cta" href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Hola, quiero el sistema de ventas') }}">Hablar por WhatsApp</a>
    </section>
    <section class="plans">
        @foreach ($plans as $plan)
            <article class="card">
                <h2>{{ $plan->name }}</h2>
                <p class="price">Gs. {{ number_format($plan->price_monthly, 0, ',', '.') }} <span>/ mes</span></p>
                <p>{{ $plan->description }}</p>
                <ul>
                    <li>{{ $plan->max_users }} usuarios</li>
                    <li>{{ $plan->max_cajas }} cajas</li>
                    <li>{{ $plan->sifen_documents_monthly ? $plan->sifen_documents_monthly.' documentos SIFEN / mes' : 'Factura PDF (sin SIFEN)' }}</li>
                    <li>Gs. {{ number_format($plan->price_yearly, 0, ',', '.') }} / año (−10%)</li>
                </ul>
                <a class="cta" href="https://wa.me/{{ $whatsapp }}?text={{ urlencode('Quiero el plan '.$plan->name) }}">Quiero este plan</a>
            </article>
        @endforeach
    </section>
    <footer>
        <span>Pago por transferencia o efectivo. 7 días de gracia antes de pausar el POS.</span>
        <span>El certificado digital SIFEN lo gestiona el comercio (DNIT).</span>
    </footer>
</body>
</html>
