@extends('layouts.admin')

@section('content')
@php($openAssignments = $agent->assignments->filter->isOpen())

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.agents.index') }}">Agents</a> › {{ $agent->full_name }}</div>
        <div class="holder" style="margin-top:6px">
            <span class="sy-avatar" style="width:52px;height:52px;font-size:20px">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($agent->prenom ?: $agent->nom, 0, 1)) }}</span>
            <div>
                <h1>{{ $agent->full_name }}</h1>
                <p class="sub">{{ $agent->service->name ?? 'Sans service' }}</p>
            </div>
        </div>
    </div>
    <div class="page-actions">
        @can('agent_edit')
            <a href="{{ route('admin.agents.edit', $agent) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
        @can('assignment_create')
            <a href="{{ route('admin.assignments.create', ['agent' => $agent->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Affecter du matériel</a>
        @endcan
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns: repeat(3, minmax(0,1fr)); margin-bottom:18px">
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-box-seam"></i></span>
        <span class="kpi-body"><span class="kpi-value">{{ $agent->assets->count() }}</span><span class="kpi-label">Matières détenues</span></span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-file-earmark-text"></i></span>
        <span class="kpi-body"><span class="kpi-value">{{ $openAssignments->count() }}</span><span class="kpi-label">Bons en cours</span></span>
    </div>
    <div class="kpi kpi-static {{ $openAssignments->filter->isOverdue()->count() ? 'kpi-critical' : '' }}">
        <span class="kpi-icon"><i class="bi bi-clock-history"></i></span>
        <span class="kpi-body"><span class="kpi-value">{{ $openAssignments->filter->isOverdue()->count() }}</span><span class="kpi-label">Retours en retard</span></span>
    </div>
</div>

<div class="sy-grid-2">
    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Matériel détenu</h2></div>
            <div class="sy-card-body flush">
                @if($agent->assets->isEmpty())
                    <div class="empty-state"><i class="bi bi-inbox"></i> Aucun matériel détenu actuellement.</div>
                @else
                    <div class="table-responsive">
                        <table class="dash-table">
                            <thead><tr><th>Matière</th><th>Catégorie</th><th>N° de série</th><th>Statut</th><th></th></tr></thead>
                            <tbody>
                                @foreach($agent->assets as $asset)
                                    <tr>
                                        <td class="strong"><a href="{{ route('admin.assets.show', $asset) }}">{{ $asset->name }}</a></td>
                                        <td>{{ $asset->category->name ?? '—' }}</td>
                                        <td class="muted">{{ $asset->serial_number ?: '—' }}</td>
                                        <td>@if($asset->status)<span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>@endif</td>
                                        <td class="nowrap">
                                            @can('assignment_return')
                                                @can('assignment_create')
                                                    <a href="{{ route('admin.assets.transfer', $asset) }}" class="btn btn-xs btn-default">Transférer</a>
                                                @endcan
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Bons d'affectation</h2></div>
            <div class="sy-card-body flush">
                @if($agent->assignments->isEmpty())
                    <div class="empty-state"><i class="bi bi-file-earmark"></i> Aucun bon pour cet agent.</div>
                @else
                    <div class="table-responsive">
                        <table class="dash-table">
                            <thead><tr><th>Référence</th><th>Affecté le</th><th>Matières</th><th>Retour prévu</th><th>Statut</th><th></th></tr></thead>
                            <tbody>
                                @foreach($agent->assignments as $a)
                                    <tr>
                                        <td class="strong"><a href="{{ route('admin.assignments.show', $a) }}">{{ $a->reference }}</a></td>
                                        <td class="nowrap">{{ $a->assigned_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td>{{ $a->isOpen() ? $a->outstanding_count.' / ' : '' }}{{ $a->matieres_count }}</td>
                                        <td class="nowrap">{{ $a->expected_return_at?->format('d/m/Y') ?? '—' }}</td>
                                        <td>
                                            @if($a->isOverdue())
                                                <span class="pill pill-en_retard">En retard</span>
                                            @else
                                                <span class="pill pill-{{ $a->status }}">{{ $a->status_label }}</span>
                                            @endif
                                        </td>
                                        <td class="nowrap">
                                            @if($a->isOpen())
                                                @can('assignment_return')
                                                    <a href="{{ route('admin.assignments.return', $a) }}" class="btn btn-xs btn-primary">Restituer</a>
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
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Coordonnées</h2></div>
            <div class="sy-card-body">
                <dl class="dl" style="grid-template-columns: 110px 1fr">
                    <dt>Service</dt><dd>{{ $agent->service->name ?? '—' }}</dd>
                    <dt>E-mail</dt><dd>@if($agent->email)<a href="mailto:{{ $agent->email }}">{{ $agent->email }}</a>@else — @endif</dd>
                    <dt>Téléphone</dt><dd>{{ $agent->telephone ?: '—' }}</dd>
                    <dt>Adresse</dt><dd>{{ $agent->adresse ?: '—' }}</dd>
                </dl>
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Historique</h2></div>
            <div class="sy-card-body">
                @if($agent->histories->isEmpty())
                    <div class="empty-state"><i class="bi bi-clock"></i> Aucun mouvement.</div>
                @else
                    <ul class="timeline">
                        @foreach($agent->histories as $h)
                            <li>
                                <div class="t-date">{{ $h->created_at?->format('d/m/Y à H:i') }}</div>
                                <div class="t-title">{{ $h->action_label }} — {{ $h->asset->name ?? 'Matière supprimée' }}</div>
                                <div class="t-body">
                                    @if($h->assignment)Bon <a href="{{ route('admin.assignments.show', $h->assignment) }}">{{ $h->assignment->reference }}</a>@endif
                                    @if($h->user) · par {{ $h->user->name }}@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection
