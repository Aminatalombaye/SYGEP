<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · @yield('title') | SYGEP</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif; color: #1a1f2b; min-height: 100vh;
            display: flex; flex-direction: column; background: linear-gradient(180deg, #ffffff 0%, #eef4f9 100%);
        }
        .flag { display: flex; height: 4px; } .flag span { flex: 1; }
        .flag span:nth-child(1) { background: #00853f; } .flag span:nth-child(2) { background: #fdcb0a; } .flag span:nth-child(3) { background: #e31b23; }
        main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
        .box { max-width: 520px; text-align: center; }
        .logo { height: 54px; margin-bottom: 36px; }
        .code { font-size: 96px; font-weight: 800; line-height: 1; color: #1a3a5c; letter-spacing: -0.04em; }
        .code span { color: #2a78d6; }
        h1 { font-size: 24px; font-weight: 800; color: #1a3a5c; margin: 14px 0 10px; }
        p { color: #64748b; font-size: 16px; line-height: 1.6; }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 28px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 10px;
            font-weight: 600; font-size: 14px; text-decoration: none; border: 1px solid #1a3a5c; cursor: pointer; font-family: inherit;
        }
        .btn-primary { background: #1a3a5c; color: #fff; }
        .btn-primary:hover { background: #142e4a; }
        .btn-outline { background: #fff; color: #1a3a5c; }
        .btn-outline:hover { background: #eef4f9; }
        footer { text-align: center; padding: 18px; font-size: 13px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="flag"><span></span><span></span><span></span></div>
    <main>
        <div class="box">
            <img src="{{ asset('img/logo.png') }}" alt="SYGEP" class="logo">
            <div class="code">@yield('code')</div>
            <h1>@yield('title')</h1>
            <p>@yield('message')</p>
            <div class="actions">
                <a href="{{ url('/') }}" class="btn btn-primary">Retour à l'accueil</a>
                <button type="button" class="btn btn-outline" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">Page précédente</button>
            </div>
        </div>
    </main>
    <footer>© {{ date('Y') }} MEFPT – Cellule Informatique</footer>
</body>
</html>
