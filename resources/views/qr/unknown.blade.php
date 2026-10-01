@extends('layouts.public')

@section('title', 'Code inconnu')

@section('content')
<section style="padding: 72px 0 96px; text-align: center;">
    <div class="container" style="max-width: 560px">
        <div style="width:64px;height:64px;border-radius:18px;background:#fef2f2;color:#c42b2f;display:flex;align-items:center;justify-content:center;font-size:30px;margin:0 auto 20px">
            <i class="bi bi-qr-code"></i>
        </div>
        <h1 style="font-size:28px;font-weight:800;color:var(--navy);margin-bottom:10px">Code non reconnu</h1>
        <p style="color:var(--muted);font-size:16px;line-height:1.6;margin-bottom:26px">
            Le code <strong>{{ $code }}</strong> ne correspond à aucun matériel enregistré dans SYGEP.
            L'étiquette est peut-être abîmée ou ancienne.
        </p>
        <a href="{{ route('contact', ['objet' => 'signalement', 'ref' => $code]) }}" class="btn-primary btn-lg">
            <i class="bi bi-envelope"></i> Signaler à la cellule informatique
        </a>
    </div>
</section>
@endsection
