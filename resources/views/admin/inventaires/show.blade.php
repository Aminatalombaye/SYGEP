@extends('layouts.admin')

@section('content')
@php
    $tabs = [
        'a-controler' => ['À contrôler', $stats['pending'], 'bi-hourglass-split'],
        'controlees'  => ['Contrôlées', $stats['checked'] + $stats['outside'], 'bi-check2-circle'],
        'ecarts'      => ['Écarts', $stats['missing'] + $stats['outside'] + $stats['damaged'] + $stats['moved'], 'bi-exclamation-triangle'],
    ];
    if ($inventaire->isClosed()) {
        $tabs['manquantes'] = ['Manquantes', $stats['missing'], 'bi-question-circle'];
        unset($tabs['a-controler']);
    }
    $results = \App\Models\Inventaire::RESULTS;
    $conditions = \App\Models\Inventaire::CONDITIONS;
    $statusTone = ['brouillon' => 'neutral', 'en_cours' => 'info', 'cloture' => 'good'][$inventaire->status] ?? 'neutral';
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.inventaires.index') }}">{{ trans('cruds.inventaire.title') }}</a> › {{ $inventaire->reference }}</div>
        <h1>{{ $inventaire->nom ?: $inventaire->reference }} <span class="pill pill-{{ $statusTone }}" style="vertical-align: middle">{{ $inventaire->status_label }}</span></h1>
        <p class="sub">
            {{ $inventaire->reference }} · {{ $inventaire->scope_label }}
            · {{ $inventaire->starts_at?->format('d/m/Y') ?? '…' }} → {{ $inventaire->ends_at?->format('d/m/Y') ?? '…' }}
        </p>
    </div>
    <div class="page-actions">
        @if(! $inventaire->isDraft())
            <a href="{{ route('admin.inventaires.report', $inventaire) }}" target="_blank" class="btn btn-default"><i class="bi bi-file-earmark-text"></i> Procès-verbal</a>
        @endif
        @can('inventaire_edit')
            @unless($inventaire->isClosed())
                <a href="{{ route('admin.inventaires.edit', $inventaire) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
            @endunless
            @if($inventaire->isDraft())
                <form method="POST" action="{{ route('admin.inventaires.start', $inventaire) }}" onsubmit="return confirm('Démarrer la campagne ? La liste des matières à contrôler sera figée selon le périmètre.');">
                    @csrf
                    <button type="submit" class="btn btn-primary"><i class="bi bi-play-fill"></i> Démarrer la campagne</button>
                </form>
            @elseif($inventaire->isRunning())
                <a href="{{ route('admin.inventaires.scan', $inventaire) }}" class="btn btn-primary"><i class="bi bi-qr-code-scan"></i> Scanner</a>
            @endif
        @endcan
    </div>
</div>

@if($inventaire->isDraft())
    <section class="sy-card">
        <div class="sy-card-body">
            <div class="empty-state" style="padding: 28px 10px">
                <i class="bi bi-clipboard-check"></i>
                <strong style="display:block; color: var(--sy-text); font-size: 17px; margin-bottom: 6px">Campagne en préparation</strong>
                Vérifiez le périmètre ({{ $inventaire->scope_label }}), puis cliquez sur « Démarrer la campagne » :
                la liste des matières à contrôler sera figée et l'équipe pourra scanner les étiquettes.
            </div>
        </div>
    </section>
@else
    <div class="kpi-grid kpi-auto" style="--kpi-cols: 4">
        <div class="kpi kpi-static">
            <span class="kpi-icon"><i class="bi bi-list-check"></i></span>
            <span class="kpi-body"><span class="kpi-value">{{ $stats['expected'] }}</span><span class="kpi-label">Matières attendues</span></span>
        </div>
        <div class="kpi kpi-static kpi-good">
            <span class="kpi-icon"><i class="bi bi-check2-circle"></i></span>
            <span class="kpi-body"><span class="kpi-value">{{ $stats['checked'] }}</span><span class="kpi-label">Contrôlées</span><span class="kpi-hint">{{ $stats['progress'] }} % du périmètre</span></span>
        </div>
        <div class="kpi kpi-static {{ ($inventaire->isClosed() ? $stats['missing'] : $stats['pending']) ? 'kpi-critical' : '' }}">
            <span class="kpi-icon"><i class="bi {{ $inventaire->isClosed() ? 'bi-question-circle' : 'bi-hourglass-split' }}"></i></span>
            <span class="kpi-body">
                <span class="kpi-value">{{ $inventaire->isClosed() ? $stats['missing'] : $stats['pending'] }}</span>
                <span class="kpi-label">{{ $inventaire->isClosed() ? 'Manquantes' : 'Restant à contrôler' }}</span>
            </span>
        </div>
        <div class="kpi kpi-static {{ ($stats['damaged'] + $stats['moved'] + $stats['outside']) ? 'kpi-warning' : '' }}">
            <span class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></span>
            <span class="kpi-body">
                <span class="kpi-value">{{ $stats['damaged'] + $stats['moved'] + $stats['outside'] }}</span>
                <span class="kpi-label">Anomalies</span>
                <span class="kpi-hint">{{ $stats['damaged'] }} abîmée(s) · {{ $stats['moved'] }} déplacée(s) · {{ $stats['outside'] }} hors périmètre</span>
            </span>
        </div>
    </div>

    <section class="sy-card">
        <div class="sy-card-body">
            <div class="inv-progress-head">
                <strong>Avancement</strong>
                <span>{{ $stats['checked'] }} / {{ $stats['expected'] }} · {{ $stats['progress'] }} %</span>
            </div>
            <div class="inv-progress"><span style="width: {{ $stats['progress'] }}%"></span></div>
            @if($inventaire->isRunning())
                @can('inventaire_close')
                    <form method="POST" action="{{ route('admin.inventaires.close', $inventaire) }}" class="inv-close"
                          onsubmit="return confirm('Clôturer la campagne ? Les {{ $stats['pending'] }} matière(s) non contrôlée(s) seront déclarées manquantes.');">
                        @csrf
                        <label class="inv-apply">
                            <input type="checkbox" name="appliquer" value="1" checked>
                            Mettre à jour les fiches (emplacement constaté, « hors service » passe en panne)
                        </label>
                        <button type="submit" class="btn btn-default"><i class="bi bi-lock"></i> Clôturer la campagne</button>
                    </form>
                @endcan
            @elseif($inventaire->isClosed())
                <p class="muted" style="margin: 12px 0 0">
                    Clôturée le {{ $inventaire->closed_at?->format('d/m/Y à H:i') }}@if($inventaire->closedBy) par {{ $inventaire->closedBy->name }}@endif.
                </p>
            @endif
        </div>
    </section>

    <nav class="sy-tabs">
        @foreach($tabs as $key => [$label, $count, $icon])
            <a href="{{ route('admin.inventaires.show', ['inventaire' => $inventaire, 'vue' => $key]) }}" @class(['active' => $tab === $key])>
                <i class="bi {{ $icon }}"></i> {{ $label }}<span class="count">{{ $count }}</span>
            </a>
        @endforeach
    </nav>

    <section class="sy-card">
        <div class="sy-card-body flush">
            @if($items->isEmpty())
                <div class="empty-state"><i class="bi bi-inbox"></i> Rien à afficher dans cet onglet.</div>
            @else
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Code</th>
                                <th>Prévue à</th>
                                <th>Trouvée à</th>
                                <th>État</th>
                                <th>Résultat</th>
                                <th>Contrôle</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $asset)
                                @php
                                    $p = $asset->pivot;
                                    $moved = $p->found_location_id && $p->expected_location_id && (int) $p->found_location_id !== (int) $p->expected_location_id;
                                    $resultTone = ['vu' => 'good', 'attendu' => 'neutral', 'manquant' => 'critical', 'hors_perimetre' => 'warning'][$p->status] ?? 'neutral';
                                @endphp
                                <tr>
                                    <td class="strong">
                                        <a href="{{ route('admin.assets.show', $asset) }}">{{ $asset->name }}</a>
                                        <div class="muted">{{ $asset->category->name ?? '' }}@if($asset->serial_number) · N° {{ $asset->serial_number }}@endif</div>
                                    </td>
                                    <td><code>{{ $asset->qr_code }}</code></td>
                                    <td>{{ $locations[$p->expected_location_id] ?? '—' }}</td>
                                    <td>
                                        {{ $locations[$p->found_location_id] ?? '—' }}
                                        @if($moved)<span class="pill pill-warning">Déplacée</span>@endif
                                    </td>
                                    <td>
                                        @if($p->condition)
                                            <span class="pill pill-{{ in_array($p->condition, ['abime', 'hors_service']) ? 'critical' : 'good' }}">{{ $conditions[$p->condition] ?? $p->condition }}</span>
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td><span class="pill pill-{{ $resultTone }}">{{ $results[$p->status] ?? $p->status }}</span></td>
                                    <td class="muted nowrap">
                                        {{ $p->checked_at ? \Illuminate\Support\Carbon::parse($p->checked_at)->format('d/m/Y H:i') : '—' }}
                                    </td>
                                    <td class="nowrap">
                                        @if($inventaire->isRunning())
                                            @can('inventaire_edit')
                                                @if($p->status === 'attendu')
                                                    <form method="POST" action="{{ route('admin.inventaires.record', $inventaire) }}" style="display:inline">
                                                        @csrf
                                                        <input type="hidden" name="code" value="{{ $asset->qr_code ?: $asset->id }}">
                                                        <button type="submit" class="btn btn-xs btn-default" title="Pointer sans scanner"><i class="bi bi-check2"></i> Pointer</button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.inventaires.undo', [$inventaire, $asset->id]) }}" style="display:inline" onsubmit="return confirm('Annuler ce contrôle ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-icon" title="Annuler le contrôle" aria-label="Annuler le contrôle"><i class="bi bi-arrow-counterclockwise"></i></button>
                                                    </form>
                                                @endif
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </section>
@endif

@if($inventaire->notes)
    <section class="sy-card">
        <div class="sy-card-head"><h2>Consignes</h2></div>
        <div class="sy-card-body" style="white-space: pre-line">{{ $inventaire->notes }}</div>
    </section>
@endif

@can('inventaire_delete')
    <form action="{{ route('admin.inventaires.destroy', $inventaire) }}" method="POST" onsubmit="return confirm('Supprimer cette campagne et tous ses contrôles ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer la campagne</button>
    </form>
@endcan
@endsection
