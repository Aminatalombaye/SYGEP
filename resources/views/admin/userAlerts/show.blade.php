@extends('layouts.admin')

@section('content')
@php
    $readCount = $userAlert->users->where('pivot.read', true)->count();
    $total = $userAlert->users->count();
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.user-alerts.index') }}">{{ trans('cruds.userAlert.title') }}</a> › #{{ $userAlert->id }}</div>
        <h1>{{ $userAlert->alert_text }}</h1>
        <p class="sub">
            <span class="pill pill-info"><i class="bi {{ $userAlert->icon }}"></i> {{ $userAlert->kind_label }}</span>
            · envoyée le {{ $userAlert->created_at?->format('d/m/Y à H:i') }}
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.user-alerts.index') }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}</a>
        @can('user_alert_delete')
            <form action="{{ route('admin.user-alerts.destroy', $userAlert) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-default"><i class="bi bi-trash3"></i> Supprimer</button>
            </form>
        @endcan
    </div>
</div>

<div class="kpi-grid kpi-auto" style="--kpi-cols: 3">
    <div class="kpi kpi-static"><span class="kpi-icon"><i class="bi bi-people"></i></span><span class="kpi-body"><span class="kpi-value">{{ $total }}</span><span class="kpi-label">Destinataires</span></span></div>
    <div class="kpi kpi-static kpi-good"><span class="kpi-icon"><i class="bi bi-check2-all"></i></span><span class="kpi-body"><span class="kpi-value">{{ $readCount }}</span><span class="kpi-label">Lue par</span>@if($total)<span class="kpi-hint">{{ round($readCount * 100 / $total) }} %</span>@endif</span></div>
    <div class="kpi kpi-static"><span class="kpi-icon"><i class="bi bi-hourglass-split"></i></span><span class="kpi-body"><span class="kpi-value">{{ $total - $readCount }}</span><span class="kpi-label">Pas encore lue</span></span></div>
</div>

<div class="sy-grid-2">
    <section class="sy-card">
        <div class="sy-card-head"><h2>Destinataires</h2></div>
        <div class="sy-card-body flush">
            @if($userAlert->users->isEmpty())
                <div class="empty-state"><i class="bi bi-people"></i> Aucun destinataire.</div>
            @else
                <table class="dash-table">
                    <thead><tr><th>Utilisateur</th><th>E-mail</th><th>Statut</th></tr></thead>
                    <tbody>
                        @foreach($userAlert->users as $u)
                            <tr>
                                <td class="strong">{{ $u->name }}</td>
                                <td class="muted">{{ $u->email }}</td>
                                <td>
                                    @if($u->pivot->read)
                                        <span class="pill pill-good"><i class="bi bi-check2"></i> Lue</span>
                                    @else
                                        <span class="pill pill-warning">Non lue</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <section class="sy-card">
        <div class="sy-card-head"><h2>Détails</h2></div>
        <div class="sy-card-body">
            <dl class="dl" style="grid-template-columns: 90px 1fr">
                <dt>Message</dt><dd>{{ $userAlert->alert_text }}</dd>
                <dt>Type</dt><dd>{{ $userAlert->kind_label }}</dd>
                <dt>Lien</dt>
                <dd>
                    @if($userAlert->alert_link)
                        <a href="{{ $userAlert->alert_link }}" @unless($userAlert->isInternal()) target="_blank" rel="noopener noreferrer" @endunless>Ouvrir la page <i class="bi bi-box-arrow-up-right"></i></a>
                    @else
                        —
                    @endif
                </dd>
                <dt>Envoyée</dt><dd>{{ $userAlert->created_at?->format('d/m/Y à H:i') }}</dd>
            </dl>
        </div>
    </section>
</div>
@endsection
