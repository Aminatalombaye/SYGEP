@extends('layouts.admin')

@section('content')
@php
    $conditions = \App\Models\Assignment::CONDITIONS;
    $outstanding = $assignment->matieres->whereNull('pivot.returned_at');
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.assignments.index') }}">Affectations</a> › {{ $assignment->reference }}</div>
        <h1>
            Bon {{ $assignment->reference }}
            @if($assignment->isOverdue())
                <span class="pill pill-en_retard" style="vertical-align:middle"><i class="bi bi-clock-history"></i> En retard</span>
            @else
                <span class="pill pill-{{ $assignment->status }}" style="vertical-align:middle">{{ $assignment->status_label }}</span>
            @endif
        </h1>
        <p class="sub">
            {{ $assignment->matieres->count() }} matière(s)
            @if($assignment->isOpen() && $outstanding->count() < $assignment->matieres->count())
                · {{ $outstanding->count() }} encore détenue(s)
            @endif
            · affecté le {{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}
        </p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.assignments.print', $assignment) }}" target="_blank" class="btn btn-default"><i class="bi bi-printer"></i> Imprimer le bon</a>
        @can('assignment_edit')
            <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
        @if($assignment->isOpen())
            @can('assignment_return')
                <a href="{{ route('admin.assignments.return', $assignment) }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i> Restituer</a>
            @endcan
        @endif
    </div>
</div>

<div class="sy-grid-2">
    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Matériel</h2></div>
            <div class="sy-card-body flush">
                <div class="table-responsive">
                    <table class="dash-table">
                        <thead>
                            <tr><th>Matière</th><th>N° de série</th><th>Catégorie</th><th>Situation</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($assignment->matieres as $asset)
                                <tr>
                                    <td class="strong">
                                        @can('asset_show')
                                            <a href="{{ route('admin.assets.show', $asset) }}">{{ $asset->name ?: 'Sans nom' }}</a>
                                        @else
                                            {{ $asset->name ?: 'Sans nom' }}
                                        @endcan
                                    </td>
                                    <td class="muted">{{ $asset->serial_number ?: '—' }}</td>
                                    <td>{{ $asset->category->name ?? '—' }}</td>
                                    <td>
                                        @if($asset->pivot->returned_at)
                                            <span class="pill pill-{{ $asset->pivot->return_condition === 'transfert' ? 'transfert' : 'restitution' }}">
                                                {{ $asset->pivot->return_condition === 'transfert' ? 'Transférée' : 'Restituée' }}
                                                le {{ \Illuminate\Support\Carbon::parse($asset->pivot->returned_at)->format('d/m/Y') }}
                                            </span>
                                            @if($asset->pivot->return_condition && $asset->pivot->return_condition !== 'transfert')
                                                <div class="muted">{{ $conditions[$asset->pivot->return_condition] ?? $asset->pivot->return_condition }}</div>
                                            @endif
                                        @else
                                            <span class="pill pill-en_cours">Détenue</span>
                                        @endif
                                    </td>
                                    <td class="nowrap">
                                        @if(! $asset->pivot->returned_at)
                                            @can('assignment_return')
                                                @can('assignment_create')
                                                    <a href="{{ route('admin.assets.transfer', $asset) }}" class="btn btn-xs btn-default">Transférer</a>
                                                @endcan
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Suivi</h2></div>
            <div class="sy-card-body">
                @if($assignment->histories->isEmpty())
                    <div class="empty-state"><i class="bi bi-clock"></i> Aucun mouvement enregistré.</div>
                @else
                    <ul class="timeline">
                        @foreach($assignment->histories as $h)
                            <li>
                                <div class="t-date">{{ $h->created_at?->format('d/m/Y à H:i') }}@if($h->user) · par {{ $h->user->name }}@endif</div>
                                <div class="t-title">{{ $h->action_label }} — {{ $h->asset->name ?? 'Matière supprimée' }}</div>
                                @if($h->notes)<div class="t-body">{{ $h->notes }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Bénéficiaire</h2></div>
            <div class="sy-card-body">
                @if($assignment->agent)
                    <div class="holder">
                        <span class="sy-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($assignment->agent->prenom ?: $assignment->agent->nom, 0, 1)) }}</span>
                        <div>
                            <div class="name"><a href="{{ route('admin.agents.show', $assignment->agent) }}">{{ $assignment->agent->full_name }}</a></div>
                            <div class="meta">{{ $assignment->service->name ?? $assignment->agent->service->name ?? 'Sans service' }}</div>
                            @if($assignment->agent->telephone || $assignment->agent->email)
                                <div class="meta">{{ collect([$assignment->agent->telephone, $assignment->agent->email])->filter()->implode(' · ') }}</div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="holder">
                        <span class="sy-avatar"><i class="bi bi-building"></i></span>
                        <div>
                            <div class="name">{{ $assignment->beneficiary }}</div>
                            <div class="meta">Affectation au service</div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Détails</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Référence</dt><dd>{{ $assignment->reference }}</dd>
                    <dt>Type</dt><dd>{{ $assignment->type_label }}</dd>
                    <dt>Affecté le</dt><dd>{{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}</dd>
                    <dt>Retour prévu</dt><dd>{{ $assignment->expected_return_at?->format('d/m/Y') ?? 'Sans date' }}</dd>
                    @if($assignment->closed_at)
                        <dt>Clôturé le</dt><dd>{{ $assignment->closed_at->format('d/m/Y') }}</dd>
                    @endif
                    <dt>Emplacement</dt><dd>{{ $assignment->location->name ?? '—' }}</dd>
                    <dt>Enregistré par</dt><dd>{{ $assignment->createdBy->name ?? '—' }}</dd>
                    @if($assignment->notes)
                        <dt>Observations</dt><dd style="white-space:pre-line">{{ $assignment->notes }}</dd>
                    @endif
                </dl>
            </div>
        </section>

        @can('assignment_delete')
            <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST"
                  onsubmit="return confirm('Supprimer ce bon ? Les matières encore détenues seront libérées.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash"></i> Supprimer ce bon</button>
            </form>
        @endcan
    </div>
</div>
@endsection
