@extends('layouts.public')

@section('title', 'Gestion du Patrimoine Matériel')

@php($ctaUrl = auth()->check() ? route('admin.home') : route('login'))

@push('styles')
    <style>
        /* Hero */
        .hero {
            padding: 80px 0;
            position: relative;
            overflow: hidden;
            background-image: url('{{ asset("img/cover.png") }}');
            background-size: cover;
            background-position: center right;
            background-repeat: no-repeat;
            min-height: 720px;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0.95) 0%,
                rgba(255, 255, 255, 0.85) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            z-index: 1;
            pointer-events: none;
            mask-image: linear-gradient(90deg, black 0%, black 60%, transparent 100%);
            -webkit-mask-image: linear-gradient(90deg, black 0%, black 60%, transparent 100%);
        }

        .hero-grid {
            position: relative;
            z-index: 2;
            width: 100%;
            padding-left: 48px;
            padding-top: 40px;
            display: flex;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .hero-note {
            font-family: 'Caveat', cursive;
            font-size: 34px;
            font-weight: 600;
            color: #1a3a5c;
            line-height: 1.35;
            text-align: left;
            transform: rotate(-3deg);
            z-index: 3;
            background: rgba(255, 255, 255, 0.92);
            padding: 26px 38px;
            border-radius: 16px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 12px 36px rgba(26, 58, 92, 0.18);
            max-width: 420px;
            border-left: 5px solid #1a3a5c;
        }

        .hero-note .highlight {
            color: #1a3a5c;
            opacity: 0.65;
            text-decoration: underline;
            text-decoration-thickness: 2.5px;
            text-underline-offset: 5px;
        }

        /* Blocs */
        .three-blocks {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            padding: 60px 0;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .block-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding: 20px 40px;
            border-right: 1px solid #f1f5f9;
            transition: all 0.3s;
        }

        .block-item:last-child { border-right: none; }
        .block-item:hover { transform: translateY(-4px); }

        .block-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s;
            background: #eef4f9;
        }

        .block-item:hover .block-icon { transform: scale(1.08); }

        .block-icon-green,
        .block-icon-blue,
        .block-icon-purple { background: #fdf3e7; }
        .block-icon-green i,
        .block-icon-blue i,
        .block-icon-purple i { color: #c2610f; font-size: 28px; }

        .block-title {
            font-size: 19px;
            font-weight: 700;
            color: #1a3a5c;
            margin-bottom: 8px;
        }

        .block-desc {
            font-size: 15px;
            color: #64748b;
            line-height: 1.55;
        }

        /* Modules */
        .features {
            padding: 100px 0;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
            position: relative;
        }

        .section-header {
            text-align: center;
            margin-bottom: 64px;
        }

        .section-header h2 {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
            color: #1a3a5c;
        }

        .section-header p {
            font-size: 17px;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto;
        }

        .flag-strip {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 26px;
        }

        .flag-green,
        .flag-yellow,
        .flag-red {
            width: 50px;
            height: 4px;
            border-radius: 2px;
        }

        .flag-green { background: #00853f; }
        .flag-yellow { background: #fdcb0a; }
        .flag-red { background: #e31b23; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        .feature-card {
            background: white;
            padding: 36px 32px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.4s;
            text-decoration: none;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #1a3a5c 0%, #3b82c4 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: #1a3a5c;
            box-shadow: 0 30px 50px -20px rgba(26,58,92,0.3);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #fdf3e7 0%, #fbe3c4 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            transition: all 0.3s;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.08) rotate(-5deg);
            background: linear-gradient(135deg, #fbe3c4 0%, #f6d9b4 100%);
        }

        .feature-icon i {
            font-size: 30px;
            color: #c2610f;
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1a3a5c;
            line-height: 1.3;
        }

        .feature-card p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .feature-link {
            font-size: 14px;
            font-weight: 600;
            color: #1a3a5c;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: gap 0.3s;
        }

        .feature-card:hover .feature-link {
            gap: 14px;
        }

        /* Aperçu */
        .preview-section {
            padding: 100px 0;
            background: #ffffff;
        }

        .preview-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .preview-text h2 {
            font-size: 38px;
            font-weight: 800;
            margin-bottom: 22px;
            line-height: 1.2;
            color: #1a3a5c;
            letter-spacing: -0.02em;
        }

        .preview-text h2 .accent {
            color: #1a3a5c;
            opacity: 0.65;
        }

        .preview-text p {
            font-size: 17px;
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .preview-list {
            list-style: none;
            margin-bottom: 36px;
        }

        .preview-list li {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
            font-size: 16px;
            color: #475569;
            padding: 6px 0;
            transition: all 0.3s;
        }

        .preview-list li:hover {
            transform: translateX(6px);
            color: #1a3a5c;
        }

        .preview-list li i {
            color: #c2610f;
            font-size: 22px;
            flex-shrink: 0;
        }

        .preview-image {
            position: relative;
        }

        .preview-image svg {
            width: 100%;
            height: auto;
            display: block;
            filter: drop-shadow(0 30px 60px rgba(26,58,92,0.2));
            transition: transform 0.3s;
        }

        .preview-image:hover svg {
            transform: scale(1.02);
        }

        .preview-actions { display: flex; gap: 14px; flex-wrap: wrap; }
        .btn-outline {
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid var(--navy); color: var(--navy); background: #fff;
            font-weight: 600; text-decoration: none; transition: all .3s;
        }
        .btn-outline:hover { background: var(--tint); transform: translateY(-2px); }
        .features, .preview-section { scroll-margin-top: 110px; }

        @media (max-width: 1200px) {
            .section-header h2 { font-size: 36px; }
            .features-grid { gap: 22px; grid-template-columns: repeat(2, 1fr); }
            .hero { min-height: 600px; }
            .hero-note { font-size: 30px; max-width: 380px; }
        }

        @media (max-width: 968px) {
            .hero { min-height: 500px; padding: 40px 0; }
            .hero::before {
                width: 100%;
                background: linear-gradient(180deg, rgba(255,255,255,.92) 0%, rgba(255,255,255,.6) 60%, rgba(255,255,255,.2) 100%);
                mask-image: none; -webkit-mask-image: none;
            }
            .hero-grid { padding-left: 20px; padding-top: 20px; }
            .hero-note { font-size: 26px; max-width: 320px; padding: 20px 28px; }
            .three-blocks { grid-template-columns: 1fr; gap: 20px; padding: 40px 0; }
            .block-item { border-right: none; border-bottom: 1px solid var(--line-soft); padding: 20px 0; }
            .block-item:last-child { border-bottom: none; }
            .features { padding: 60px 0; }
            .section-header { margin-bottom: 44px; }
            .section-header h2 { font-size: 30px; }
            .section-header p { font-size: 15px; }
            .features-grid { grid-template-columns: 1fr; gap: 20px; }
            .feature-card { padding: 28px 24px; }
            .preview-section { padding: 60px 0; }
            .preview-content { grid-template-columns: 1fr; gap: 44px; }
            .preview-text h2 { font-size: 26px; }
            .preview-text p { font-size: 15px; }
            .preview-list li { font-size: 15px; }
        }

        @media (max-width: 500px) {
            .hero { min-height: 420px; padding: 30px 0; }
            .hero-grid { padding-left: 16px; padding-top: 16px; }
            .hero-note { font-size: 22px; max-width: 280px; padding: 18px 22px; }
        }
    </style>
@endpush

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <h1 id="hero-title" class="sr-only">SYGEP – Système de Gestion du Patrimoine Matériel du MEFPT</h1>
        <div class="hero-grid">
            <div class="hero-note">
                Un patrimoine<br>
                mieux <span class="highlight">géré</span>,<br>
                pour un service<br>
                plus performant
            </div>
        </div>
    </section>

    <div class="container">
        <div class="three-blocks">
            <div class="block-item">
                <div class="block-icon block-icon-green">
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="block-title">Inventaire</div>
                    <div class="block-desc">Recenser et organiser les biens.</div>
                </div>
            </div>

            <div class="block-item">
                <div class="block-icon block-icon-blue">
                    <i class="bi bi-people" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="block-title">Affectation</div>
                    <div class="block-desc">Suivre les biens affectés aux agents et services.</div>
                </div>
            </div>

            <div class="block-item">
                <div class="block-icon block-icon-purple">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="block-title">Suivi du patrimoine</div>
                    <div class="block-desc">Visualiser l'état et les mouvements des équipements.</div>
                </div>
            </div>
        </div>
    </div>

    <section class="features" id="modules">
        <div class="container">
            <div class="section-header">
                <h2>Modules SYGEP</h2>
                <p>Une solution complète pour la gestion du patrimoine matériel</p>
                <div class="flag-strip">
                    <div class="flag-green"></div>
                    <div class="flag-yellow"></div>
                    <div class="flag-red"></div>
                </div>
            </div>

            <div class="features-grid">
                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-boxes" aria-hidden="true"></i>
                    </div>
                    <h3>Gestion des matières</h3>
                    <p>Suivi des stocks, matières périssables et non périssables, alertes automatiques de réapprovisionnement.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>

                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-building" aria-hidden="true"></i>
                    </div>
                    <h3>Gestion des infrastructures</h3>
                    <p>Centres de formation, bâtiments, équipements, états des lieux et inventaire.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>

                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-tools" aria-hidden="true"></i>
                    </div>
                    <h3>Gestion de la maintenance</h3>
                    <p>Planification des interventions préventives et correctives sur les équipements.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>

                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-kanban" aria-hidden="true"></i>
                    </div>
                    <h3>Suivi des projets</h3>
                    <p>Construction et réhabilitation des centres de formation professionnelle.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>

                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-graph-up" aria-hidden="true"></i>
                    </div>
                    <h3>Tableaux de bord</h3>
                    <p>Indicateurs clés de performance, rapports stratégiques et analytics.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>

                <a href="{{ $ctaUrl }}" class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    </div>
                    <h3>Administration</h3>
                    <p>Gestion des utilisateurs, rôles, permissions et paramètres système.</p>
                    <div class="feature-link">
                        Explorer <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <section class="preview-section" id="apropos">
        <div class="container">
            <div class="preview-content">
                <div class="preview-text">
                    <h2>Une vision <span class="accent">360°</span> du Patrimoine Matériel du MEFPT</h2>
                    <p>SYGEP offre une vue consolidée sur l'ensemble des ressources matérielles et infrastructures à travers tout le Sénégal.</p>
                    <ul class="preview-list">
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Vue consolidée sur les 14 régions</li>
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Alertes et notifications en temps réel</li>
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Rapports automatisés pour la hiérarchie</li>
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Suivi budgétaire et financier des projets</li>
                        <li><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Gestion documentaire centralisée</li>
                    </ul>
                    <div class="preview-actions">
                    <a href="{{ $ctaUrl }}" class="btn-primary btn-lg">
                        Découvrir SYGEP <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </a>
                    </div>
                </div>

                <div class="preview-image">
                    <svg viewBox="0 0 600 420" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Aperçu du tableau de bord SYGEP">
                        <rect x="20" y="20" width="560" height="360" rx="14" fill="#1a202c"/>
                        <rect x="32" y="32" width="536" height="336" rx="8" fill="#ffffff"/>

                        <rect x="32" y="32" width="536" height="40" rx="8" fill="#f8fafc"/>
                        <circle cx="56" cy="52" r="8" fill="#1a3a5c"/>
                        <text x="72" y="57" font-family="Inter" font-size="12" font-weight="700" fill="#1a3a5c">SYGEP</text>
                        <text x="160" y="57" font-family="Inter" font-size="9" fill="#94a3b8">Tableau de bord</text>
                        <text x="500" y="57" font-family="Inter" font-size="9" fill="#94a3b8">Admin</text>

                        <rect x="32" y="80" width="110" height="288" rx="4" fill="#f8fafc"/>
                        <rect x="42" y="94" width="90" height="20" rx="4" fill="#1a3a5c"/>
                        <text x="52" y="108" font-family="Inter" font-size="8" font-weight="600" fill="white">Tableau de bord</text>
                        <rect x="42" y="122" width="90" height="16" rx="3" fill="#e2e8f0"/>
                        <rect x="42" y="144" width="90" height="16" rx="3" fill="#e2e8f0"/>
                        <rect x="42" y="166" width="90" height="16" rx="3" fill="#e2e8f0"/>
                        <rect x="42" y="188" width="90" height="16" rx="3" fill="#e2e8f0"/>
                        <rect x="42" y="210" width="90" height="16" rx="3" fill="#e2e8f0"/>
                        <rect x="42" y="232" width="90" height="16" rx="3" fill="#e2e8f0"/>

                        <rect x="156" y="88" width="100" height="58" rx="6" fill="#eef4f9"/>
                        <text x="164" y="104" font-family="Inter" font-size="7" fill="#1a3a5c">Total biens</text>
                        <text x="164" y="128" font-family="Inter" font-size="20" font-weight="800" fill="#1a3a5c">245</text>

                        <rect x="266" y="88" width="100" height="58" rx="6" fill="#eef4f9"/>
                        <text x="274" y="104" font-family="Inter" font-size="7" fill="#1a3a5c">En service</text>
                        <text x="274" y="128" font-family="Inter" font-size="20" font-weight="800" fill="#1a3a5c">218</text>

                        <rect x="376" y="88" width="100" height="58" rx="6" fill="#eef4f9"/>
                        <text x="384" y="104" font-family="Inter" font-size="7" fill="#1a3a5c">Maintenance</text>
                        <text x="384" y="128" font-family="Inter" font-size="20" font-weight="800" fill="#1a3a5c">12</text>

                        <rect x="486" y="88" width="82" height="58" rx="6" fill="#eef4f9"/>
                        <text x="494" y="104" font-family="Inter" font-size="7" fill="#1a3a5c">Hors service</text>
                        <text x="494" y="128" font-family="Inter" font-size="20" font-weight="800" fill="#1a3a5c">15</text>

                        <rect x="156" y="160" width="310" height="208" rx="6" fill="#ffffff" stroke="#e2e8f0" stroke-width="0.5"/>
                        <text x="170" y="180" font-family="Inter" font-size="10" font-weight="700" fill="#1a3a5c">Derniers mouvements</text>
                        <line x1="170" y1="190" x2="456" y2="190" stroke="#e2e8f0" stroke-width="0.5"/>

                        <text x="174" y="206" font-family="Inter" font-size="7" font-weight="600" fill="#94a3b8">Date</text>
                        <text x="240" y="206" font-family="Inter" font-size="7" font-weight="600" fill="#94a3b8">Équipement</text>
                        <text x="350" y="206" font-family="Inter" font-size="7" font-weight="600" fill="#94a3b8">Type</text>
                        <text x="410" y="206" font-family="Inter" font-size="7" font-weight="600" fill="#94a3b8">Statut</text>

                        <text x="174" y="226" font-family="Inter" font-size="7" fill="#475569">23/09/2025</text>
                        <text x="240" y="226" font-family="Inter" font-size="7" fill="#1a3a5c">Ordinateur portable</text>
                        <text x="350" y="226" font-family="Inter" font-size="7" fill="#475569">Affectation</text>
                        <rect x="410" y="220" width="50" height="10" rx="5" fill="#dbe7f1"/>
                        <text x="418" y="228" font-family="Inter" font-size="6" font-weight="600" fill="#1a3a5c">En service</text>

                        <text x="174" y="248" font-family="Inter" font-size="7" fill="#475569">21/09/2025</text>
                        <text x="240" y="248" font-family="Inter" font-size="7" fill="#1a3a5c">Imprimante laser</text>
                        <text x="350" y="248" font-family="Inter" font-size="7" fill="#475569">Maintenance</text>
                        <rect x="410" y="242" width="55" height="10" rx="5" fill="#dbe7f1"/>
                        <text x="416" y="250" font-family="Inter" font-size="6" font-weight="600" fill="#1a3a5c">En maintenance</text>

                        <text x="174" y="270" font-family="Inter" font-size="7" fill="#475569">20/09/2025</text>
                        <text x="240" y="270" font-family="Inter" font-size="7" fill="#1a3a5c">Véhicule utilitaire</text>
                        <text x="350" y="270" font-family="Inter" font-size="7" fill="#475569">Affectation</text>
                        <rect x="410" y="264" width="50" height="10" rx="5" fill="#dbe7f1"/>
                        <text x="418" y="272" font-family="Inter" font-size="6" font-weight="600" fill="#1a3a5c">En service</text>

                        <text x="174" y="292" font-family="Inter" font-size="7" fill="#475569">18/09/2025</text>
                        <text x="240" y="292" font-family="Inter" font-size="7" fill="#1a3a5c">Table de réunion</text>
                        <text x="350" y="292" font-family="Inter" font-size="7" fill="#475569">Affectation</text>
                        <rect x="410" y="286" width="50" height="10" rx="5" fill="#dbe7f1"/>
                        <text x="418" y="294" font-family="Inter" font-size="6" font-weight="600" fill="#1a3a5c">En service</text>

                        <rect x="476" y="160" width="92" height="208" rx="6" fill="#ffffff" stroke="#e2e8f0" stroke-width="0.5"/>
                        <text x="486" y="180" font-family="Inter" font-size="10" font-weight="700" fill="#1a3a5c">Répartition</text>

                        <circle cx="522" cy="240" r="38" fill="none" stroke="#e2e8f0" stroke-width="14"/>
                        <circle cx="522" cy="240" r="38" fill="none" stroke="#1a3a5c" stroke-width="14"
                                stroke-dasharray="95 240" stroke-dashoffset="0" transform="rotate(-90 522 240)"/>
                        <circle cx="522" cy="240" r="38" fill="none" stroke="#3b82c4" stroke-width="14"
                                stroke-dasharray="70 240" stroke-dashoffset="-95" transform="rotate(-90 522 240)"/>
                        <circle cx="522" cy="240" r="38" fill="none" stroke="#7aa8cc" stroke-width="14"
                                stroke-dasharray="50 240" stroke-dashoffset="-165" transform="rotate(-90 522 240)"/>
                        <circle cx="522" cy="240" r="38" fill="none" stroke="#c1d5e6" stroke-width="14"
                                stroke-dasharray="25 240" stroke-dashoffset="-215" transform="rotate(-90 522 240)"/>

                        <circle cx="490" cy="310" r="3" fill="#1a3a5c"/>
                        <text x="500" y="313" font-family="Inter" font-size="7" fill="#64748b">Informatique</text>
                        <circle cx="490" cy="326" r="3" fill="#3b82c4"/>
                        <text x="500" y="329" font-family="Inter" font-size="7" fill="#64748b">Mobilier</text>
                        <circle cx="490" cy="342" r="3" fill="#7aa8cc"/>
                        <text x="500" y="345" font-family="Inter" font-size="7" fill="#64748b">Véhicules</text>
                        <circle cx="490" cy="358" r="3" fill="#c1d5e6"/>
                        <text x="500" y="361" font-family="Inter" font-size="7" fill="#64748b">Autres</text>
                    </svg>
                </div>
            </div>
        </div>
    </section>
@endsection
