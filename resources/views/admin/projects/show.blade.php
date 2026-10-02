@extends('layouts.admin')

@section('content')
@php
    $expected = $project->expected_progress;
    $gap = $expected !== null ? $project->progress - $expected : null;
    $done = $project->milestones->whereNotNull('done_at')->count();
    $lateMilestones = $project->milestones->filter->is_late->count();
    $canEdit = auth()->user()->can('project_edit');
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.projects.index') }}">{{ trans('cruds.project.title') }}</a> › {{ $project->reference }}</div>
        <h1>
            {{ $project->name }}
            <span class="pill pill-{{ $project->status_tone }}" style="vertical-align: middle">{{ $project->is_late ? 'En retard' : $project->status_label }}</span>
        </h1>
        <p class="sub">
            {{ $project->reference }} · {{ $project->type_label }}
            · {{ $project->start_date?->format('d/m/Y') ?? '…' }} → {{ $project->end_date?->format('d/m/Y') ?? '…' }}
        </p>
    </div>
    <div class="page-actions">
        @can('report_create')
            <a href="{{ route('admin.reports.create', ['project' => $project->id]) }}" class="btn btn-default"><i class="bi bi-file-earmark-plus"></i> Rapport</a>
        @endcan
        @can('project_edit')
            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
    </div>
</div>

<div class="kpi-grid kpi-auto" style="--kpi-cols: 4">
    <div class="kpi kpi-static {{ $gap !== null && $gap < -15 ? 'kpi-critical' : ($project->progress >= 100 ? 'kpi-good' : '') }}">
        <span class="kpi-icon"><i class="bi bi-speedometer2"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $project->progress }} %</span>
            <span class="kpi-label">Avancement physique</span>
            @if($expected !== null && $project->status !== 'termine')
                <span class="kpi-hint">Attendu à date : {{ $expected }} %{{ $gap < 0 ? ' · retard de '.abs($gap).' pts' : '' }}</span>
            @endif
        </span>
    </div>
    <div class="kpi kpi-static {{ $project->budget_rate !== null && $project->budget_rate > 100 ? 'kpi-critical' : '' }}">
        <span class="kpi-icon"><i class="bi bi-cash-stack"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ \App\Support\Fmt::money($project->budget) }}</span>
            <span class="kpi-label">Budget prévu</span>
            @if($project->spent !== null)
                <span class="kpi-hint">{{ \App\Support\Fmt::money($project->spent) }} engagés{{ $project->budget_rate !== null ? ' ('.$project->budget_rate.' %)' : '' }}</span>
            @endif
        </span>
    </div>
    <div class="kpi kpi-static {{ $lateMilestones ? 'kpi-warning' : '' }}">
        <span class="kpi-icon"><i class="bi bi-signpost-split"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $done }} / {{ $project->milestones->count() }}</span>
            <span class="kpi-label">Jalons atteints</span>
            @if($lateMilestones)<span class="kpi-hint">{{ $lateMilestones }} jalon(s) en retard</span>@endif
        </span>
    </div>
    <div class="kpi kpi-static {{ $project->is_late ? 'kpi-critical' : '' }}">
        <span class="kpi-icon"><i class="bi bi-calendar-event"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ $project->end_date?->format('d/m/Y') ?? '—' }}</span>
            <span class="kpi-label">Échéance</span>
            @if($project->completed_at)
                <span class="kpi-hint">Terminé le {{ $project->completed_at->format('d/m/Y') }}</span>
            @elseif($project->end_date)
                <span class="kpi-hint">{{ $project->end_date->isPast() ? 'Dépassée de '.(int) $project->end_date->diffInDays(today()).' jour(s)' : 'Dans '.(int) today()->diffInDays($project->end_date).' jour(s)' }}</span>
            @endif
        </span>
    </div>
</div>

<section class="sy-card">
    <div class="sy-card-body">
        <div class="inv-progress-head">
            <strong>Avancement</strong>
            <span>{{ $project->progress }} %@if($expected !== null && $project->status !== 'termine') · attendu {{ $expected }} %@endif</span>
        </div>
        <div class="inv-progress" style="position: relative">
            <span style="width: {{ $project->progress }}%"></span>
            @if($expected !== null && $project->status !== 'termine')
                <i title="Avancement attendu à date" style="position:absolute; top:-3px; bottom:-3px; left: {{ $expected }}%; width: 2px; background: #e31b23"></i>
            @endif
        </div>
    </div>
</section>

<div class="sy-grid-2">
    <div>
        <section class="sy-card" id="jalons">
            <div class="sy-card-head">
                <div>
                    <h2>Jalons et tâches du projet</h2>
                    <p>L'avancement est calculé à partir des jalons atteints, selon leur poids.</p>
                </div>
            </div>
            <div class="sy-card-body flush">
                @if($project->milestones->isEmpty())
                    <div class="empty-state"><i class="bi bi-signpost-split"></i> Aucun jalon. Ajoutez les grandes étapes : études, gros œuvre, second œuvre, réception…</div>
                @else
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="dash-table">
                            <thead><tr><th></th><th>Jalon</th><th>Échéance</th><th>Intervenant</th><th>Poids</th><th></th></tr></thead>
                            <tbody>
                                @foreach($project->milestones as $m)
                                    <tr>
                                        <td width="36">
                                            @if($canEdit)
                                                <form method="POST" action="{{ route('admin.projects.milestones.toggle', [$project, $m]) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-icon" title="{{ $m->done_at ? 'Rouvrir' : 'Marquer comme atteint' }}">
                                                        <i class="bi {{ $m->done_at ? 'bi-check-circle-fill text-success' : 'bi-circle' }}" style="font-size: 18px"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <i class="bi {{ $m->done_at ? 'bi-check-circle-fill text-success' : 'bi-circle' }}"></i>
                                            @endif
                                        </td>
                                        <td class="strong">
                                            {{ $m->title }}
                                            @if($m->description)<div class="muted">{{ $m->description }}</div>@endif
                                            @if($m->done_at)<div class="muted">Atteint le {{ $m->done_at->format('d/m/Y') }}</div>@endif
                                        </td>
                                        <td class="nowrap">
                                            {{ $m->due_date?->format('d/m/Y') ?? '—' }}
                                            @if($m->is_late)<div><span class="pill pill-critical">En retard</span></div>@endif
                                        </td>
                                        <td class="muted">{{ $m->intervenant->name ?? '—' }}</td>
                                        <td>{{ $m->weight }}</td>
                                        <td class="nowrap">
                                            @if($canEdit)
                                                <form method="POST" action="{{ route('admin.projects.milestones.destroy', [$project, $m]) }}" onsubmit="return confirm('Supprimer ce jalon ?');" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-icon" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash3"></i></button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    </div>
                @endif
            </div>
            @if($canEdit)
                <div class="sy-card-body" style="border-top: 1px solid var(--sy-line)">
                    <form method="POST" action="{{ route('admin.projects.milestones.store', $project) }}" class="form-grid" style="grid-template-columns: 2fr 1fr 1fr">
                        @csrf
                        <div class="form-group">
                            <label for="m-title" class="required">Nouveau jalon</label>
                            <input type="text" name="title" id="m-title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" placeholder="Ex. : Fin du gros œuvre" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="m-due">Échéance</label>
                            <input type="date" name="due_date" id="m-due" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="m-weight">Poids</label>
                            <select name="weight" id="m-weight" class="form-control">
                                @foreach([1 => '1 — mineur', 2 => '2', 3 => '3 — moyen', 5 => '5 — majeur', 10 => '10 — critique'] as $w => $l)
                                    <option value="{{ $w }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="grid-column: span 2">
                            <label for="m-int">Intervenant responsable</label>
                            <select name="intervenant_id" id="m-int" class="form-control select2">
                                <option value="">—</option>
                                @foreach($project->intervenants as $i)
                                    <option value="{{ $i->id }}">{{ $i->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="align-self: end">
                            <button type="submit" class="btn btn-primary btn-block"><i class="bi bi-plus-lg"></i> Ajouter</button>
                        </div>
                    </form>
                </div>
            @endif
        </section>

        @if($project->description)
            <section class="sy-card">
                <div class="sy-card-head"><h2>Description</h2></div>
                <div class="sy-card-body" style="white-space: pre-line">{{ $project->description }}</div>
            </section>
        @endif

        <section class="sy-card">
            <div class="sy-card-head">
                <h2>Rapports de suivi</h2>
                @can('report_create')
                    <a href="{{ route('admin.reports.create', ['project' => $project->id]) }}" class="btn btn-default btn-sm"><i class="bi bi-plus-lg"></i> Nouveau rapport</a>
                @endcan
            </div>
            <div class="sy-card-body flush">
                @if($project->reports->isEmpty())
                    <div class="empty-state"><i class="bi bi-file-earmark-text"></i> Aucun rapport rattaché.</div>
                @else
                    <div class="table-responsive"><table class="dash-table">
                        <tbody>
                            @foreach($project->reports->sortByDesc('report_date') as $report)
                                <tr>
                                    <td class="strong"><a href="{{ route('admin.reports.show', $report) }}">{{ $report->title }}</a></td>
                                    <td class="muted nowrap">{{ $report->report_date }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </section>
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Détails</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Référence</dt><dd>{{ $project->reference }}</dd>
                    <dt>Nature</dt><dd>{{ $project->type_label }}</dd>
                    <dt>Statut</dt><dd>{{ $project->status_label }}</dd>
                    <dt>Chef(s) de projet</dt>
                    <dd>
                        @forelse($project->chef_projets as $chef)
                            <div>{{ trim($chef->prenom.' '.$chef->nom) }}@if($chef->telephone) <span class="muted">· {{ $chef->telephone }}</span>@endif</div>
                        @empty
                            —
                        @endforelse
                    </dd>
                    <dt>Infrastructure(s)</dt>
                    <dd>
                        @forelse($project->infrastructures as $infra)
                            <div><a href="{{ route('admin.infrastructures.show', $infra) }}">{{ $infra->name }}</a></div>
                        @empty
                            —
                        @endforelse
                    </dd>
                    <dt>Créé par</dt><dd>{{ $project->createdBy->name ?? '—' }}</dd>
                </dl>
            </div>
        </section>

        <section class="sy-card" id="intervenants">
            <div class="sy-card-head">
                <h2>Intervenants</h2>
                @can('intervenant_create')
                    <a href="{{ route('admin.intervenants.create', ['project' => $project->id]) }}" class="btn btn-default btn-sm"><i class="bi bi-person-plus"></i> Nouveau</a>
                @endcan
            </div>
            <div class="sy-card-body flush">
                @if($project->intervenants->isEmpty())
                    <div class="empty-state"><i class="bi bi-people"></i> Aucun intervenant : entreprise, bureau d'études, contrôle…</div>
                @else
                    <div class="table-responsive"><table class="dash-table">
                        <tbody>
                            @foreach($project->intervenants as $i)
                                <tr>
                                    <td class="strong">
                                        @can('intervenant_show')
                                            <a href="{{ route('admin.intervenants.show', $i) }}">{{ $i->name }}</a>
                                        @else
                                            {{ $i->name }}
                                        @endcan
                                        <div class="muted">{{ $i->role_label }}{{ $i->pivot->mission ? ' · '.$i->pivot->mission : '' }}</div>
                                    </td>
                                    <td class="muted nowrap">{{ $i->telephone }}</td>
                                    <td class="nowrap">
                                        @if($canEdit)
                                            <form method="POST" action="{{ route('admin.projects.intervenants.detach', [$project, $i]) }}" onsubmit="return confirm('Retirer cet intervenant du projet ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-icon" title="Retirer" aria-label="Retirer"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
            @if($canEdit && $intervenants->isNotEmpty())
                <div class="sy-card-body" style="border-top: 1px solid var(--sy-line)">
                    <form method="POST" action="{{ route('admin.projects.intervenants.attach', $project) }}">
                        @csrf
                        <div class="form-group">
                            <label for="i-id">Ajouter un intervenant existant</label>
                            <select name="intervenant_id" id="i-id" class="form-control select2" required>
                                <option value="">—</option>
                                @foreach($intervenants as $id => $name)
                                    @unless($project->intervenants->contains('id', $id))
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endunless
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="text" name="mission" class="form-control" placeholder="Mission sur ce projet (facultatif)">
                        </div>
                        <button type="submit" class="btn btn-default"><i class="bi bi-plus-lg"></i> Ajouter au projet</button>
                    </form>
                </div>
            @endif
        </section>

        @can('project_delete')
            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Supprimer ce projet ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer le projet</button>
            </form>
        @endcan
    </div>
</div>
@endsection
