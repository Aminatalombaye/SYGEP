@extends('layouts.public')

@section('title', 'Contact')
@section('description', 'Contactez la cellule informatique du MEFPT pour toute question sur SYGEP : accès, assistance technique, signalement.')

@push('styles')
    <style>
        .page-hero {
            background: linear-gradient(135deg, var(--tint) 0%, #ffffff 70%);
            padding: 64px 0 56px;
            border-bottom: 1px solid var(--line-soft);
        }
        .breadcrumb { font-size: 13px; color: var(--muted); margin-bottom: 14px; }
        .breadcrumb a { color: var(--muted); text-decoration: none; }
        .breadcrumb a:hover { color: var(--navy); text-decoration: underline; }
        .page-hero h1 { font-size: 42px; font-weight: 800; color: var(--navy); letter-spacing: -0.02em; margin-bottom: 12px; }
        .page-hero p { font-size: 17px; color: var(--muted); max-width: 640px; }

        .contact-section { padding: 72px 0 96px; }
        .contact-grid { display: grid; grid-template-columns: 380px 1fr; gap: 48px; align-items: start; }

        .info-card {
            background: var(--navy); color: #fff; border-radius: 24px; padding: 36px 32px;
            position: relative; overflow: hidden;
        }
        .info-card::after {
            content: ''; position: absolute; right: -60px; bottom: -60px; width: 200px; height: 200px;
            border-radius: 50%; background: rgba(255,255,255,.06);
        }
        .info-card h2 { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .info-card .sub { color: rgba(255,255,255,.75); font-size: 14px; margin-bottom: 28px; }
        .info-item { display: flex; gap: 16px; margin-bottom: 22px; position: relative; z-index: 1; }
        .info-item i {
            width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,.12);
            display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;
        }
        .info-item .label { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.65); font-weight: 600; }
        .info-item .value { font-size: 15px; font-weight: 500; line-height: 1.5; }
        .info-item a { color: #fff; text-decoration: none; }
        .info-item a:hover { text-decoration: underline; }
        .info-flag { display: flex; gap: 6px; margin-top: 8px; }
        .info-flag span { width: 34px; height: 4px; border-radius: 2px; }

        .form-card { background: #fff; border: 1px solid var(--line); border-radius: 24px; padding: 40px; }
        .form-card h2 { font-size: 24px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
        .form-card .sub { color: var(--muted); font-size: 15px; margin-bottom: 28px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
        .field label .req { color: #e31b23; }
        .field label .opt { color: var(--muted); font-weight: 400; }
        .field input, .field select, .field textarea {
            width: 100%; padding: 13px 16px; border: 1px solid var(--line); border-radius: 12px;
            font: inherit; font-size: 15px; color: var(--text); background: #fbfcfe; transition: border-color .2s, box-shadow .2s;
        }
        .field textarea { min-height: 170px; resize: vertical; }
        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none; border-color: var(--navy); background: #fff; box-shadow: 0 0 0 4px rgba(194,97,15,.12);
        }
        .field.has-error input, .field.has-error select, .field.has-error textarea { border-color: #e31b23; }
        .field .error { color: #c81e25; font-size: 13px; margin-top: 6px; display: flex; gap: 6px; align-items: center; }
        .counter { font-size: 12px; color: var(--muted); text-align: right; margin-top: 4px; }
        .hp { position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden; }

        .form-footer { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-top: 8px; }
        .form-footer .note { font-size: 13px; color: var(--muted); }

        .alert { border-radius: 14px; padding: 16px 18px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start; font-size: 15px; }
        .alert i { font-size: 20px; line-height: 1.2; }
        .alert-success { background: #ecfdf3; color: #065f35; border: 1px solid #b7ebcd; }
        .alert-error { background: #fef2f2; color: #9b1c1c; border: 1px solid #fecaca; }

        @media (max-width: 1100px) {
            .contact-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .page-hero { padding: 44px 0 40px; }
            .page-hero h1 { font-size: 30px; }
            .contact-section { padding: 44px 0 64px; }
            .form-card { padding: 26px 20px; }
            .form-row { grid-template-columns: 1fr; gap: 0; }
        }
    </style>
@endpush

@section('content')
    <section class="page-hero">
        <div class="container">
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('welcome') }}">Accueil</a> &rsaquo; <span aria-current="page">Contact</span>
            </nav>
            <h1>Contactez-nous</h1>
            <p>Une question sur SYGEP, une demande d'accès ou un problème technique ? La cellule informatique du MEFPT est à votre écoute.</p>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="contact-grid">
                <aside class="info-card" aria-label="Nos coordonnées">
                    <h2>Nos coordonnées</h2>
                    <p class="sub">{{ $contact['service'] }}</p>

                    <div class="info-item">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <div>
                            <div class="label">Structure</div>
                            <div class="value">{{ $contact['organisation'] }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <div>
                            <div class="label">Adresse</div>
                            <div class="value">{{ $contact['adresse'] }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-telephone" aria-hidden="true"></i>
                        <div>
                            <div class="label">Téléphone</div>
                            <div class="value"><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['telephone']) }}">{{ $contact['telephone'] }}</a></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <div>
                            <div class="label">E-mail</div>
                            <div class="value"><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <div>
                            <div class="label">Horaires</div>
                            <div class="value">{{ $contact['horaires'] }}</div>
                        </div>
                    </div>
                    <div class="info-flag" aria-hidden="true">
                        <span class="green"></span><span class="yellow"></span><span class="red"></span>
                    </div>
                </aside>

                <div class="form-card">
                    <h2>Envoyer un message</h2>
                    <p class="sub">Les champs marqués d'un <span style="color:#e31b23">*</span> sont obligatoires.</p>

                    @if(session('contact_success'))
                        <div class="alert alert-success" role="status">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            <div>{{ session('contact_success') }}</div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-error" role="alert">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            <div>Le formulaire contient des erreurs. Merci de vérifier les champs indiqués.</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" novalidate>
                        @csrf

                        <div class="hp" aria-hidden="true">
                            <label for="website">Ne pas remplir</label>
                            <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-row">
                            <div @class(['field', 'has-error' => $errors->has('name')])>
                                <label for="name">Nom complet <span class="req">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required maxlength="120" autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                                @error('name')<div class="error" id="name-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                            </div>
                            <div @class(['field', 'has-error' => $errors->has('email')])>
                                <label for="email">Adresse e-mail <span class="req">*</span></label>
                                <input type="email" id="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required maxlength="190" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                @error('email')<div class="error" id="email-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="form-row">
                            <div @class(['field', 'has-error' => $errors->has('phone')])>
                                <label for="phone">Téléphone <span class="opt">(facultatif)</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" placeholder="+221 77 000 00 00" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                                @error('phone')<div class="error" id="phone-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                            </div>
                            <div @class(['field', 'has-error' => $errors->has('structure')])>
                                <label for="structure">Structure / service <span class="opt">(facultatif)</span></label>
                                <input type="text" id="structure" name="structure" value="{{ old('structure') }}" maxlength="190" placeholder="Ex. : Centre de formation de Thiès" @error('structure') aria-invalid="true" aria-describedby="structure-error" @enderror>
                                @error('structure')<div class="error" id="structure-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div @class(['field', 'has-error' => $errors->has('subject')])>
                            <label for="subject">Objet <span class="req">*</span></label>
                            <select id="subject" name="subject" required @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                                <option value="" disabled @selected(!old('subject', request('objet')))>— Choisissez un objet —</option>
                                @foreach($subjects as $value => $label)
                                    <option value="{{ $value }}" @selected(old('subject', request('objet')) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('subject')<div class="error" id="subject-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                        </div>

                        <div @class(['field', 'has-error' => $errors->has('message')])>
                            <label for="message">Message <span class="req">*</span></label>
                            <textarea id="message" name="message" required minlength="10" maxlength="5000" @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ old('message', request('ref') ? "Référence du matériel : ".request('ref')."\n\n" : '') }}</textarea>
                            <div class="counter"><span id="message-count">0</span> / 5000</div>
                            @error('message')<div class="error" id="message-error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>@enderror
                        </div>

                        <div class="form-footer">
                            <p class="note"><i class="bi bi-shield-check" aria-hidden="true"></i> Vos informations sont uniquement utilisées pour répondre à votre demande.</p>
                            <button type="submit" class="btn-primary btn-lg">
                                Envoyer le message <i class="bi bi-send" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var ta = document.getElementById('message');
            var out = document.getElementById('message-count');
            if (!ta || !out) return;
            var update = function () { out.textContent = ta.value.length; };
            ta.addEventListener('input', update);
            update();
        })();
    </script>
@endpush
