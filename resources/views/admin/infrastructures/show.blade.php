@extends('layouts.admin')

@section('content')
@php
    $share = $infrastructure->depreciatedShare();
@endphp

<div class="page-head">
    <div>
        <div class="crumb">
            <a href="{{ route('admin.infrastructures.index') }}">{{ trans('cruds.infrastructure.title') }}</a> ›
            @if($infrastructure->parent)
                <a href="{{ route('admin.infrastructures.show', $infrastructure->parent) }}">{{ $infrastructure->parent->name }}</a> ›
            @endif
            {{ \Illuminate\Support\Str::limit($infrastructure->name, 40) }}
        </div>
        <h1>
            {{ $infrastructure->name }}
            <span class="pill pill-{{ $infrastructure->status_tone }}" style="vertical-align: middle">{{ $infrastructure->status_label }}</span>
        </h1>
        <p class="sub">{{ $infrastructure->nature_label }}{{ $infrastructure->type ? ' · '.$infrastructure->type : '' }}{{ $infrastructure->location ? ' · '.$infrastructure->location : '' }}</p>
    </div>
    <div class="page-actions">
        @can('maintenance_request_create')
            <a href="{{ route('admin.maintenance-requests.create', ['infrastructure' => $infrastructure->id]) }}" class="btn btn-default"><i class="bi bi-tools"></i> Signaler un problème</a>
        @endcan
        @can('project_create')
            <a href="{{ route('admin.projects.create', ['infrastructure' => $infrastructure->id]) }}" class="btn btn-default"><i class="bi bi-kanban"></i> Nouveau projet</a>
        @endcan
        @can('infrastructure_edit')
            <a href="{{ route('admin.infrastructures.edit', $infrastructure) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
    </div>
</div>

<div class="kpi-grid kpi-auto" style="--kpi-cols: 4">
    <div class="kpi kpi-static kpi-{{ $infrastructure->condition ? $infrastructure->condition_tone : '' }}">
        <span class="kpi-icon"><i class="bi bi-clipboard2-pulse"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ $infrastructure->condition_label ?? 'Non évalué' }}</span>
            <span class="kpi-label">État constaté</span>
            <span class="kpi-hint">{{ $infrastructure->last_inspection_at ? 'Visite du '.$infrastructure->last_inspection_at->format('d/m/Y') : 'Aucune visite enregistrée' }}</span>
        </span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-cash-coin"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ $infrastructure->canDepreciate() ? \App\Support\Fmt::money($infrastructure->net_book_value) : '—' }}</span>
            <span class="kpi-label">Valeur nette comptable</span>
            @if($infrastructure->canDepreciate())
                <span class="kpi-hint">sur {{ \App\Support\Fmt::money($infrastructure->acquisition_value) }} · amortie à {{ round($share * 100) }} %</span>
            @endif
        </span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-kanban"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $infrastructure->projects->count() }}</span>
            <span class="kpi-label">Projet(s)</span>
            <span class="kpi-hint">{{ $infrastructure->projects->whereIn('status', ['planifie', 'en_cours', 'suspendu'])->count() }} en cours</span>
        </span>
    </div>
    @php($openRequests = $infrastructure->maintenanceRequests->filter->isOpen()->count())
    <div class="kpi kpi-static {{ $openRequests ? 'kpi-warning' : '' }}">
        <span class="kpi-icon"><i class="bi bi-tools"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $openRequests }}</span>
            <span class="kpi-label">Demande(s) de maintenance ouvertes</span>
            <span class="kpi-hint">{{ $infrastructure->maintenancePlans->where('active', true)->count() }} plan(s) préventif(s)</span>
        </span>
    </div>
</div>

<div class="sy-grid-2">
    <div>
        <section class="sy-card">
            <div class="sy-card-head">
                <div>
                    <h2>Plan d'amortissement</h2>
                    <p>Linéaire sur {{ $infrastructure->depreciation_years ?: '…' }} an(s) à partir de la mise en service.</p>
                </div>
            </div>
            @if(empty($schedule))
                <div class="sy-card-body">
                    <div class="empty-state"><i class="bi bi-calculator"></i> Renseignez la date de mise en service, la valeur d'origine et la durée pour calculer le plan.</div>
                </div>
            @else
                <div class="sy-card-body">
                    <div class="inv-progress-head">
                        <strong>Amorti à date</strong>
                        <span>{{ round($share * 100) }} % · fin le {{ $infrastructure->depreciation_end->format('d/m/Y') }}</span>
                    </div>
                    <div class="inv-progress"><span style="width: {{ round($share * 100) }}%"></span></div>
                </div>
                <div class="sy-card-body flush">
                    <div class="table-responsive" style="max-height: 420px">
                        <table class="dash-table">
                            <thead><tr><th>Année</th><th>Période</th><th>Dotation</th><th>Cumul</th><th>Valeur nette</th></tr></thead>
                            <tbody>
                                @foreach($schedule as $row)
                                    <tr @if($row['current']) style="background: var(--sy-tint)" @endif>
                                        <td class="strong">{{ $row['rank'] }}</td>
                                        <td class="muted nowrap">{{ $row['from']->format('d/m/Y') }} → {{ $row['to']->format('d/m/Y') }}</td>
                                        <td class="nowrap">{{ \App\Support\Fmt::money($row['amount']) }}</td>
                                        <td class="nowrap">{{ \App\Support\Fmt::money($row['cumulative']) }}</td>
                                        <td class="nowrap strong">{{ \App\Support\Fmt::money($row['net']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Projets de construction et de réhabilitation</h2></div>
            <div class="sy-card-body flush">
                @if($infrastructure->projects->isEmpty())
                    <div class="empty-state"><i class="bi bi-kanban"></i> Aucun projet.</div>
                @else
                    <table class="dash-table">
                        <tbody>
                            @foreach($infrastructure->projects as $p)
                                <tr>
                                    <td class="strong"><a href="{{ route('admin.projects.show', $p) }}">{{ $p->name }}</a><div class="muted">{{ $p->reference }} · {{ $p->type_label }}</div></td>
                                    <td style="min-width: 140px">
                                        <div class="read-bar"><span style="width: {{ $p->progress }}%"></span></div>
                                        <span class="muted">{{ $p->progress }} %</span>
                                    </td>
                                    <td><span class="pill pill-{{ $p->status_tone }}">{{ $p->is_late ? 'En retard' : $p->status_label }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Maintenance</h2></div>
            <div class="sy-card-body flush">
                @if($infrastructure->maintenanceRequests->isEmpty() && $infrastructure->maintenancePlans->isEmpty())
                    <div class="empty-state"><i class="bi bi-tools"></i> Aucune demande ni plan préventif.</div>
                @else
                    <table class="dash-table">
                        <tbody>
                            @foreach($infrastructure->maintenancePlans as $plan)
                                <tr>
                                    <td class="strong"><i class="bi bi-arrow-repeat"></i> {{ $plan->title }}<div class="muted">Préventif · {{ $plan->frequency_label }}</div></td>
                                    <td class="nowrap">Prochaine : {{ $plan->next_due_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td>@if($plan->is_due)<span class="pill pill-warning">À programmer</span>@endif</td>
                                </tr>
                            @endforeach
                            @foreach($infrastructure->maintenanceRequests as $req)
                                <tr>
                                    <td class="strong"><a href="{{ route('admin.maintenance-requests.show', $req) }}">{{ $req->reference }}</a> — {{ \Illuminate\Support\Str::limit($req->title ?: $req->description, 60) }}</td>
                                    <td class="muted nowrap">{{ $req->created_at?->format('d/m/Y') }}</td>
                                    <td><span class="pill pill-{{ $req->status_tone }}">{{ $req->status_label }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Fiche</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Nature</dt><dd>{{ $infrastructure->nature_label }}</dd>
                    <dt>Rattachée à</dt><dd>@if($infrastructure->parent)<a href="{{ route('admin.infrastructures.show', $infrastructure->parent) }}">{{ $infrastructure->parent->name }}</a>@else — @endif</dd>
                    <dt>Usage</dt><dd>{{ $infrastructure->type ?: '—' }}</dd>
                    <dt>Localisation</dt><dd>{{ $infrastructure->location ?: '—' }}</dd>
                    <dt>Surface</dt><dd>{{ $infrastructure->surface ? \App\Support\Fmt::qty($infrastructure->surface).' m²' : '—' }}</dd>
                    <dt>Mise en service</dt><dd>{{ $infrastructure->construction_date?->format('d/m/Y') ?? '—' }}</dd>
                    <dt>Valeur d'origine</dt><dd>{{ \App\Support\Fmt::money($infrastructure->acquisition_value) }}</dd>
                    <dt>Annuité</dt><dd>{{ \App\Support\Fmt::money($infrastructure->annual_depreciation) }}</dd>
                </dl>
                @if($infrastructure->description)
                    <p style="margin: 16px 0 0; white-space: pre-line">{{ $infrastructure->description }}</p>
                @endif
            </div>
        </section>

        @if($infrastructure->nature !== 'bloc')
            <section class="sy-card">
                <div class="sy-card-head">
                    <h2>{{ $infrastructure->nature === 'structure' ? 'Bâtiments et blocs' : 'Blocs et salles' }}</h2>
                    @can('infrastructure_create')
                        <a href="{{ route('admin.infrastructures.create', ['parent' => $infrastructure->id]) }}" class="btn btn-default btn-sm"><i class="bi bi-plus-lg"></i> Ajouter</a>
                    @endcan
                </div>
                <div class="sy-card-body flush">
                    @if($infrastructure->children->isEmpty())
                        <div class="empty-state"><i class="bi bi-building"></i> Aucun élément rattaché.</div>
                    @else
                        <table class="dash-table">
                            <tbody>
                                @foreach($infrastructure->children as $child)
                                    <tr>
                                        <td class="strong"><a href="{{ route('admin.infrastructures.show', $child) }}">{{ $child->name }}</a><div class="muted">{{ $child->nature_label }}{{ $child->type ? ' · '.$child->type : '' }}</div></td>
                                        <td><span class="pill pill-{{ $child->status_tone }}">{{ $child->status_label }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
        @endif

        @can('infrastructure_delete')
            <form action="{{ route('admin.infrastructures.destroy', $infrastructure) }}" method="POST" onsubmit="return confirm('Supprimer cette infrastructure ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer</button>
            </form>
        @endcan
    </div>
</div>
@endsection
