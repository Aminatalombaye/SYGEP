@extends('layouts.public')

@section('title', 'Connexion')
@section('description', "Connectez-vous à SYGEP, le Système de Gestion du Patrimoine Matériel du MEFPT.")

@push('styles')
    <style>
        .login-section {
            padding: 64px 0 80px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%);
        }

        .login-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border: 1px solid var(--line);
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 30px 60px -30px rgba(26,58,92,.25);
            min-height: 600px;
        }

        .login-visual {
            position: relative;
            background-image: url('{{ asset("img/cover.png") }}');
            background-size: cover;
            background-position: center right;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
        }
        .login-visual::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(255,255,255,.8) 0%, rgba(255,255,255,.55) 55%, rgba(255,255,255,.1) 100%);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
        }
        .login-visual > * { position: relative; z-index: 1; }

        .login-form-wrap {
            padding: 56px 56px 48px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .login-form-wrap .flag-strip { display: flex; gap: 6px; margin-bottom: 22px; }
        .login-form-wrap .flag-strip span { width: 34px; height: 4px; border-radius: 2px; }
        .login-form-wrap h1 {
            font-size: 34px; font-weight: 800; color: var(--navy);
            letter-spacing: -0.02em; margin-bottom: 6px;
        }
        .login-form-wrap .sub { color: var(--muted); font-size: 16px; margin-bottom: 30px; }

        .field { margin-bottom: 20px; }
        .field label { display: block; font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
        .input-icon { position: relative; }
        .input-icon > i {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            color: var(--muted); font-size: 17px; pointer-events: none;
        }
        .input-icon input {
            width: 100%; padding: 14px 16px 14px 46px;
            border: 1px solid var(--line); border-radius: 12px;
            font: inherit; font-size: 15px; color: var(--text); background: #fbfcfe;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-icon input:focus {
            outline: none; border-color: var(--navy); background: #fff;
            box-shadow: 0 0 0 4px rgba(26,58,92,.12);
        }
        .input-icon.has-toggle input { padding-right: 50px; }
        .toggle-password {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            border: 0; background: none; width: 36px; height: 36px; border-radius: 8px;
            color: var(--muted); font-size: 17px; cursor: pointer;
        }
        .toggle-password:hover { color: var(--navy); background: var(--tint); }
        .field.has-error input { border-color: #e31b23; }
        .field .error { color: #c81e25; font-size: 13px; margin-top: 6px; display: flex; gap: 6px; align-items: center; }

        .form-options {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; flex-wrap: wrap; margin: 4px 0 26px; font-size: 14px;
        }
        .remember { display: inline-flex; align-items: center; gap: 8px; color: var(--muted-2); cursor: pointer; }
        .remember input { width: 17px; height: 17px; accent-color: var(--navy); cursor: pointer; }
        .form-options a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .form-options a:hover { text-decoration: underline; }

        .btn-block { width: 100%; justify-content: center; }

        .alert { border-radius: 14px; padding: 14px 16px; margin-bottom: 22px; display: flex; gap: 10px; align-items: flex-start; font-size: 14px; }
        .alert i { font-size: 18px; line-height: 1.2; }
        .alert-info { background: var(--tint); color: var(--navy); border: 1px solid #d3e2ee; }
        .alert-error { background: #fef2f2; color: #9b1c1c; border: 1px solid #fecaca; }

        .secure-note {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 24px; padding-top: 22px; border-top: 1px solid var(--line-soft);
            color: var(--muted); font-size: 13px;
        }
        .secure-note i { color: var(--navy); }
        .help-link { text-align: center; margin-top: 10px; font-size: 13px; color: var(--muted); }
        .help-link a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .help-link a:hover { text-decoration: underline; }

        @media (max-width: 968px) {
            .login-section { padding: 36px 0 56px; }
            .login-card { grid-template-columns: 1fr; min-height: 0; }
            .login-visual { display: none; }
            .login-form-wrap { padding: 36px 24px; }
            .login-form-wrap h1 { font-size: 28px; }
        }
    </style>
@endpush

@section('content')
<section class="login-section">
    <div class="container" style="max-width: 1100px;">
        <div class="login-card">

            <div class="login-visual" aria-hidden="true">
            </div>

            <div class="login-form-wrap">
                <div class="flag-strip" aria-hidden="true">
                    <span class="green"></span><span class="yellow"></span><span class="red"></span>
                </div>
                <h1>Connexion</h1>
                <p class="sub">Accédez à votre espace de travail SYGEP.</p>

                @if(session('message'))
                    <div class="alert alert-info" role="status">
                        <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                        <div>{{ session('message') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error" role="alert">
                        <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div @class(['field', 'has-error' => $errors->has('email')])>
                        <label for="email">Adresse e-mail</label>
                        <div class="input-icon">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   required autofocus autocomplete="username" placeholder="prenom.nom@mefpt.gouv.sn"
                                   @error('email') aria-invalid="true" @enderror>
                        </div>
                    </div>

                    <div @class(['field', 'has-error' => $errors->has('password')])>
                        <label for="password">Mot de passe</label>
                        <div class="input-icon has-toggle">
                            <i class="bi bi-lock" aria-hidden="true"></i>
                            <input type="password" id="password" name="password"
                                   required autocomplete="current-password" placeholder="••••••••"
                                   @error('password') aria-invalid="true" @enderror>
                            <button type="button" class="toggle-password" aria-label="Afficher le mot de passe">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="error"><i class="bi bi-exclamation-circle" aria-hidden="true"></i>{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="remember">
                            <input type="checkbox" name="remember" id="remember" @checked(old('remember'))>
                            Se souvenir de moi
                        </label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-primary btn-lg btn-block">
                        Se connecter <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="secure-note">
                    <i class="bi bi-shield-lock-fill" aria-hidden="true"></i>
                    Plateforme sécurisée réservée aux agents du MEFPT
                </div>
                <p class="help-link">Pas encore de compte ou besoin d'aide ? <a href="{{ route('contact') }}">Contactez-nous</a></p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    <script>
        (function () {
            var btn = document.querySelector('.toggle-password');
            var input = document.getElementById('password');
            if (!btn || !input) return;
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
                btn.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        })();
    </script>
@endpush
