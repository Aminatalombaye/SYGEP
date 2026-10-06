<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection('title')
        <title>@yield('title') | SYGEP</title>
    @else
        <title>SYGEP</title>
    @endif
    <meta name="description" content="@yield('description', 'SYGEP – Système de Gestion du Patrimoine Matériel du Ministère de l\'Emploi et de la Formation Professionnelle et Technique du Sénégal.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/favicon/favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|caveat:400,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        :root {
            --navy: #c2610f;
            --navy-dark: #9a4a0b;
            --blue: #f5a742;
            --text: #1a1a1a;
            --muted: #64748b;
            --muted-2: #475569;
            --line: #e2e8f0;
            --line-soft: #f1f5f9;
            --tint: #fdf3e7;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: var(--text);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container { max-width: 1400px; margin: 0 auto; padding: 0 48px; }

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        :focus-visible { outline: 3px solid var(--blue); outline-offset: 3px; border-radius: 4px; }

        /* Bande tricolore */
        .flag-strip-top { display: flex; height: 4px; width: 100%; }
        .flag-strip-top span { flex: 1; }
        .green { background: #00853f; }
        .yellow { background: #fdcb0a; }
        .red { background: #e31b23; }

        /* Bandeau institutionnel */
        .top-bar { background: #fff; border-bottom: 1px solid var(--line-soft); padding: 12px 0; font-size: 13px; }
        .top-bar-content { display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        .top-bar-left { display: flex; align-items: center; gap: 10px; color: var(--navy); font-weight: 600; }
        .top-bar-left .sep { color: #cbd5e1; font-weight: 400; }
        .top-bar-left .devise { color: var(--muted); font-weight: 500; font-style: italic; }
        .top-bar-right { color: var(--muted); font-weight: 500; }

        /* Navbar */
        .navbar-wrap { position: sticky; top: 0; z-index: 1000; background: #fff; border-bottom: 1px solid var(--line-soft); }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 18px 0; min-height: 96px; }
        .logo-block { display: flex; align-items: center; gap: 18px; text-decoration: none; flex-shrink: 0; }
        .logo-img { height: 72px; width: auto; max-width: 280px; object-fit: contain; transition: transform .3s; }
        .logo-block:hover .logo-img { transform: scale(1.02); }
        .logo-text { display: flex; flex-direction: column; padding-left: 18px; border-left: 1px solid var(--line); }
        .logo-tagline { font-size: 13px; color: var(--muted); font-weight: 500; line-height: 1.35; }

        .nav-right { display: flex; align-items: center; gap: 40px; }
        .nav-links { display: flex; gap: 38px; align-items: center; }
        .nav-links a {
            text-decoration: none; color: var(--muted-2); font-weight: 500; font-size: 17px;
            transition: color .3s; position: relative; padding: 5px 0;
        }
        .nav-links a.active { color: var(--navy); font-weight: 600; }
        .nav-links a.active::after {
            content: ''; position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%);
            width: 36px; height: 3px; background: var(--navy); border-radius: 3px;
        }
        .nav-links a:hover { color: var(--navy); }

        .btn-nav-login, .btn-primary {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
            background: #e8901a; color: #fff; border: 0; border-radius: 8px; font: inherit;
            font-weight: 600; font-size: 14px; text-decoration: none; cursor: pointer;
            transition: all .3s; white-space: nowrap; box-shadow: 0 4px 14px rgba(232,144,26,.35);
        }
        .btn-nav-login:hover, .btn-primary:hover {
            transform: translateY(-2px); background: #c2610f; box-shadow: 0 10px 26px rgba(194,97,15,.45);
        }
        .btn-lg { padding: 16px 32px; font-size: 15px; border-radius: 50px; gap: 12px; }

        .nav-toggle {
            display: none; background: none; border: 1px solid var(--line); border-radius: 8px;
            width: 42px; height: 42px; font-size: 22px; color: var(--navy); cursor: pointer;
            align-items: center; justify-content: center;
        }
        .mobile-menu { display: none; border-top: 1px solid var(--line-soft); padding: 8px 0 16px; }
        .mobile-menu a {
            display: block; padding: 12px 4px; color: var(--muted-2); text-decoration: none;
            font-weight: 500; border-bottom: 1px solid var(--line-soft);
        }
        .mobile-menu a.active { color: var(--navy); font-weight: 700; }
        .mobile-menu.open { display: block; }

        /* Footer */
        .footer { background: #1a3a5c; color: rgba(255,255,255,.85); font-size: 13px; }
        .footer-main {
            display: grid; grid-template-columns: 1.3fr 1fr 1.3fr; gap: 32px;
            padding: 32px 0 24px;
        }
        .footer-brand { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .footer-brand .name { font-size: 17px; font-weight: 800; color: #fff; letter-spacing: .02em; line-height: 1.1; }
        .footer-brand .name small { display: block; font-size: 11px; font-weight: 500; color: rgba(255,255,255,.7); letter-spacing: 0; margin-top: 2px; }
        .footer-about { line-height: 1.55; margin-bottom: 0; max-width: 320px; color: rgba(255,255,255,.7); }
        .footer-devise { font-style: italic; color: rgba(255,255,255,.65); font-size: 13px; }

        .footer h3 {
            color: #fff; font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .08em; margin-bottom: 12px; position: relative; padding-bottom: 8px;
        }
        .footer h3::after {
            content: ''; position: absolute; left: 0; bottom: 0; width: 28px; height: 3px; border-radius: 2px;
            background: linear-gradient(90deg, #00853f 0 33%, #fdcb0a 33% 66%, #e31b23 66%);
        }
        .footer-list { list-style: none; }
        .footer nav .footer-list { display: grid; grid-template-columns: 1fr 1fr; column-gap: 16px; }
        .footer-list li { margin-bottom: 6px; }
        .footer-list a { color: rgba(255,255,255,.8); text-decoration: none; transition: color .2s, padding-left .2s; }
        .footer-list a:hover { color: #fff; padding-left: 4px; }

        .footer-contact li { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 6px; line-height: 1.45; }
        .footer-contact i { color: #fdcb0a; font-size: 14px; margin-top: 2px; flex-shrink: 0; }
        .footer-contact a { color: rgba(255,255,255,.85); text-decoration: none; }
        .footer-contact a:hover { color: #fff; text-decoration: underline; }

        .footer-cta {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 8px;
            padding: 10px 18px; border: 1px solid rgba(255,255,255,.35); border-radius: 8px;
            color: #fff; text-decoration: none; font-weight: 600; transition: all .2s;
        }
        .footer-cta:hover { background: #fff; color: #1a3a5c; }

        .footer-bottom { border-top: 1px solid rgba(255,255,255,.12); padding: 12px 0; }
        .footer-bottom-content { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; font-size: 12px; color: rgba(255,255,255,.7); }
        .footer-bottom-links { display: flex; gap: 20px; flex-wrap: wrap; align-items: center; }
        .footer-bottom-links a { color: rgba(255,255,255,.7); text-decoration: none; }
        .footer-bottom-links a:hover { color: #fff; }
        .back-to-top {
            display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px;
            border-radius: 50%; background: #e8901a; color: #fff !important;
        }
        .back-to-top:hover { background: #c2610f; }
        .footer-flag { display: flex; height: 3px; }
        .footer-flag span { flex: 1; }

        @media (max-width: 1200px) {
            .container { padding: 0 32px; }
            .nav-links { gap: 26px; }
            .nav-links a { font-size: 15px; }
            .logo-text { display: none; }
            .logo-img { height: 60px; max-width: 230px; }
        }

        @media (max-width: 968px) {
            .container { padding: 0 20px; }
            .top-bar { padding: 10px 0; font-size: 12px; }
            .top-bar-right { display: none; }
            .navbar { min-height: 82px; padding: 14px 0; }
            .logo-img { height: 54px; max-width: 200px; }
            .nav-links { display: none; }
            .nav-right { gap: 12px; }
            .nav-toggle { display: inline-flex; }
            .btn-nav-login { padding: 9px 16px; font-size: 13px; }
            .footer-main { grid-template-columns: 1fr 1fr; gap: 24px; padding: 28px 0 20px; }
            .footer-main > div:first-child { grid-column: 1 / -1; }
        }

        @media (max-width: 500px) {
            .footer-main { grid-template-columns: 1fr; gap: 20px; }
            .footer-bottom-content { flex-direction: column; text-align: center; }
            .top-bar-left .devise, .top-bar-left .sep { display: none; }
            .logo-img { height: 46px; max-width: 170px; }
            .btn-nav-login { padding: 8px 12px; font-size: 12px; }
            .btn-nav-login .label { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body id="top">
    @php
        $navItems = [
            ['label' => 'Accueil',         'url' => route('welcome'),           'active' => request()->routeIs('welcome')],
            ['label' => 'Modules',         'url' => route('welcome').'#modules', 'active' => false],
            ['label' => 'À propos',        'url' => route('welcome').'#apropos', 'active' => false],
            ['label' => 'Contact',         'url' => route('contact'),           'active' => request()->routeIs('contact')],
        ];
    @endphp

    <div class="flag-strip-top" aria-hidden="true">
        <span class="green"></span><span class="yellow"></span><span class="red"></span>
    </div>

    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <div class="top-bar-left">
                    <span>République du Sénégal</span>
                    <span class="sep">•</span>
                    <span class="devise">Un Peuple – Un But – Une Foi</span>
                </div>
                <div class="top-bar-right">
                    Ministère de l'Emploi et de la Formation Professionnelle et Technique
                </div>
            </div>
        </div>
    </div>

    <header class="navbar-wrap">
        <div class="container">
            <nav class="navbar" aria-label="Navigation principale">
                <a href="{{ route('welcome') }}" class="logo-block">
                    <img src="{{ asset('img/logo.png') }}" alt="SYGEP – accueil" class="logo-img">
                    <div class="logo-text">
                        <span class="logo-tagline">Système de Gestion<br>du Patrimoine Matériel</span>
                    </div>
                </a>

                <div class="nav-right">
                    <div class="nav-links">
                        @foreach($navItems as $item)
                            <a href="{{ $item['url'] }}" @class(['active' => $item['active']]) @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                    @auth
                        <a href="{{ route('admin.home') }}" class="btn-nav-login">
                            <i class="bi bi-speedometer2" aria-hidden="true"></i> <span class="label">Tableau de bord</span>
                        </a>
                    @else
                        @unless(request()->routeIs('login'))
                            <a href="{{ route('login') }}" class="btn-nav-login">
                                <i class="bi bi-person" aria-hidden="true"></i> <span class="label">Se connecter</span>
                            </a>
                        @endunless
                    @endauth
                    <button type="button" class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobile-menu">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                </div>
            </nav>
            <div class="mobile-menu" id="mobile-menu">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" @class(['active' => $item['active']])>{{ $item['label'] }}</a>
                @endforeach
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    @php($contact = config('panel.contact'))
    <footer class="footer">
        <div class="container">
            <div class="footer-main">
                <div>
                    <div class="footer-brand">
                        <div class="name">SYGEP<small>Système de Gestion du Patrimoine Matériel</small></div>
                    </div>
                    <p class="footer-about">Gestion des biens, infrastructures et projets des structures de formation du MEFPT.</p>
                </div>

                <nav aria-label="Liens du pied de page">
                    <h3>Navigation</h3>
                    <ul class="footer-list">
                        <li><a href="{{ route('welcome') }}">Accueil</a></li>
                        <li><a href="{{ route('welcome') }}#modules">Modules</a></li>
                        <li><a href="{{ route('welcome') }}#apropos">À propos</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        @auth
                            <li><a href="{{ route('admin.home') }}">Tableau de bord</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Espace agent</a></li>
                        @endauth
                    </ul>
                </nav>

                <div>
                    <h3>Contact</h3>
                    <ul class="footer-list footer-contact">
                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i><span>{{ $contact['adresse'] }}</span></li>
                        <li><i class="bi bi-telephone" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['telephone']) }}">{{ $contact['telephone'] }}</a></li>
                        <li><i class="bi bi-envelope" aria-hidden="true"></i><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>© {{ date('Y') }} MEFPT – Cellule Informatique. Tous droits réservés.</p>
                    <div class="footer-bottom-links">
                        <span>SYGEP v{{ config('app.version', '1.0') }}</span>
                        <a href="#top" class="back-to-top" aria-label="Retour en haut de la page"><i class="bi bi-arrow-up" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-flag" aria-hidden="true">
            <span class="green"></span><span class="yellow"></span><span class="red"></span>
        </div>
    </footer>

    <script>
        (function () {
            var btn = document.querySelector('.nav-toggle');
            var menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;
            btn.addEventListener('click', function () {
                var open = menu.classList.toggle('open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                btn.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
                btn.querySelector('i').className = open ? 'bi bi-x-lg' : 'bi bi-list';
            });
            menu.addEventListener('click', function (e) {
                if (e.target.tagName === 'A') { menu.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); btn.querySelector('i').className = 'bi bi-list'; }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
