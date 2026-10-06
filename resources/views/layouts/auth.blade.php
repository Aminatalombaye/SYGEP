@extends('layouts.public')

@push('styles')
    <style>
        .auth-section { padding: 64px 0 88px; background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%); }
        .auth-card {
            max-width: 480px; margin: 0 auto; background: #fff; border: 1px solid var(--line);
            border-radius: 24px; padding: 44px 44px 36px; box-shadow: 0 30px 60px -30px rgba(194,97,15,.25);
        }
        .auth-card .auth-icon {
            width: 56px; height: 56px; border-radius: 16px; background: var(--tint); color: var(--navy);
            display: flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 20px;
        }
        .auth-card .flag-strip { display: flex; gap: 6px; margin-bottom: 18px; }
        .auth-card .flag-strip span { width: 34px; height: 4px; border-radius: 2px; }
        .auth-card h1 { font-size: 28px; font-weight: 800; color: var(--navy); letter-spacing: -0.02em; margin-bottom: 6px; }
        .auth-card .sub { color: var(--muted); font-size: 15px; margin-bottom: 26px; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 14px; font-weight: 600; color: var(--navy); margin-bottom: 8px; }
        .input-icon { position: relative; }
        .input-icon > i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 17px; pointer-events: none; }
        .input-icon input {
            width: 100%; padding: 14px 16px 14px 46px; border: 1px solid var(--line); border-radius: 12px;
            font: inherit; font-size: 15px; color: var(--text); background: #fbfcfe; transition: border-color .2s, box-shadow .2s;
        }
        .input-icon input:focus { outline: none; border-color: var(--navy); background: #fff; box-shadow: 0 0 0 4px rgba(194,97,15,.12); }
        .field.has-error input { border-color: #e31b23; }
        .field .error { color: #c81e25; font-size: 13px; margin-top: 6px; display: flex; gap: 6px; align-items: center; }
        .btn-block { width: 100%; justify-content: center; margin-top: 6px; }
        .alert { border-radius: 14px; padding: 14px 16px; margin-bottom: 22px; display: flex; gap: 10px; align-items: flex-start; font-size: 14px; }
        .alert i { font-size: 18px; line-height: 1.2; }
        .alert-success { background: #ecfdf3; color: #065f35; border: 1px solid #b7ebcd; }
        .alert-error { background: #fef2f2; color: #9b1c1c; border: 1px solid #fecaca; }
        .auth-links { text-align: center; margin-top: 22px; font-size: 14px; color: var(--muted); }
        .auth-links a { color: var(--navy); font-weight: 600; text-decoration: none; }
        .auth-links a:hover { text-decoration: underline; }
        @media (max-width: 560px) {
            .auth-section { padding: 32px 0 56px; }
            .auth-card { padding: 30px 22px; border-radius: 18px; }
            .auth-card h1 { font-size: 24px; }
        }
    </style>
@endpush

@section('content')
    <section class="auth-section">
        <div class="container">
            <div class="auth-card">
                <div class="auth-icon"><i class="bi @yield('icon', 'bi-shield-lock')" aria-hidden="true"></i></div>
                <h1>@yield('heading')</h1>
                <p class="sub">@yield('intro')</p>

                @if(session('status'))
                    <div class="alert alert-success" role="status">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i><div>{{ session('status') }}</div>
                    </div>
                @endif

                @yield('form')
            </div>
        </div>
    </section>
@endsection
