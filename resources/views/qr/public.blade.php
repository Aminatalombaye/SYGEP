@extends('layouts.public')

@section('title', 'Matériel '.$asset->qr_code)

@push('styles')
    <style>
        .qr-section { padding: 56px 0 80px; background: linear-gradient(180deg, #ffffff 0%, #f8fbfd 100%); }
        .qr-card {
            max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid var(--line); border-radius: 24px;
            padding: 36px 34px; box-shadow: 0 30px 60px -30px rgba(194,97,15,.25); text-align: center;
        }
        .qr-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 999px;
            background: var(--tint); color: var(--navy); font-weight: 700; font-size: 13px; margin-bottom: 18px;
        }
        .qr-card h1 { font-size: 28px; font-weight: 800; color: var(--navy); letter-spacing: -0.02em; margin-bottom: 6px; }
        .qr-card .cat { color: var(--muted); font-size: 16px; margin-bottom: 22px; }
        .qr-code-ref { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 15px; color: var(--navy); background: #f4f7fa; border-radius: 10px; padding: 10px 14px; display: inline-block; margin-bottom: 22px; }
        .qr-note { font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 26px; }
        .qr-actions { display: flex; flex-direction: column; gap: 10px; }
        .qr-actions .btn-primary, .qr-actions .btn-outline { justify-content: center; }
        .btn-outline {
            display: inline-flex; align-items: center; gap: 8px; padding: 14px 20px; border-radius: 50px;
            border: 2px solid var(--navy); color: var(--navy); background: #fff; font-weight: 600; text-decoration: none;
        }
        .btn-outline:hover { background: var(--tint); }
        .flag-strip { display: flex; gap: 6px; justify-content: center; margin-bottom: 22px; }
        .flag-strip span { width: 34px; height: 4px; border-radius: 2px; }
    </style>
@endpush

@section('content')
<section class="qr-section">
    <div class="container">
        <div class="qr-card">
            <div class="flag-strip" aria-hidden="true"><span class="green"></span><span class="yellow"></span><span class="red"></span></div>
            <span class="qr-badge"><i class="bi bi-patch-check-fill"></i> Propriété du MEFPT</span>
            <h1>{{ $asset->name ?: 'Matériel' }}</h1>
            <p class="cat">{{ $asset->category->name ?? 'Matériel' }}</p>
            <div class="qr-code-ref">{{ $asset->qr_code }}</div>
            <p class="qr-note">
                Ce matériel appartient au Ministère de l'Emploi et de la Formation Professionnelle et Technique.
                En cas de panne, de perte ou si vous l'avez trouvé, merci de le signaler.
            </p>
            <div class="qr-actions">
                <a href="{{ route('contact', ['objet' => 'signalement', 'ref' => $asset->qr_code]) }}" class="btn-primary btn-lg">
                    <i class="bi bi-exclamation-triangle"></i> Signaler un problème
                </a>
                <a href="{{ route('admin.assets.show', $asset) }}" class="btn-outline">
                    <i class="bi bi-box-arrow-in-right"></i> Agent du MEFPT ? Se connecter pour voir la fiche
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
