@extends('layouts.admin')

@section('content')
@php
    $req = $maintenanceRequest;
    $steps = ['soumise' => 'Soumise', 'en_attente_direction' => 'Avis technique', 'validee' => 'Approuvée', 'planifiee' => 'Planifiée', 'en_cours' => 'En cours', 'terminee' => 'Terminée'];
    $order = array_keys($steps);
    $rejectedAt = $req->status === 'rejetee' ? ($req->approved_at ? 2 : 1) : null;
    $current = $rejectedAt ?? array_search($req->status, $order, true);
    $canEdit = auth()->user()->can('maintenance_request_edit');
    $canValidate = auth()->user()->can('maintenance_request_validate');
    $canApprove = auth()->user()->can('maintenance_request_approve');
@endphp

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.maintenance-requests.index') }}">{{ trans('cruds.maintenanceRequest.title') }}</a> › {{ $req->reference }}</div>
        <h1>
            {{ $req->title ?: 'Demande '.$req->reference }}
            <span class="pill pill-{{ $req->status_tone }}" style="vertical-align: middle">{{ $req->status_label }}</span>
        </h1>
        <p class="sub">
            {{ $req->reference }} · {{ $req->kind_label }} ·
            priorité <span class="pill pill-{{ $req->priority_tone }}">{{ $req->priority_label }}</span>
            · déposée le {{ $req->created_at?->format('d/m/Y à H:i') }}
        </p>
    </div>
    <div class="page-actions">
        @if($canEdit && $req->isOpen())
            <a href="{{ route('admin.maintenance-requests.edit', $req) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
        @endif
    </div>
</div>

@error('status')<div class="alert alert-danger">{{ $message }}</div>@enderror

<section class="sy-card">
    <div class="sy-card-body">
        <ol class="sy-steps">
            @foreach($steps as $key => $label)
                @php($i = $loop->index)
                <li @class([
                    'done'     => $req->status === 'terminee' || ($rejectedAt === null && $i < $current) || ($rejectedAt !== null && $i < $rejectedAt),
                    'current'  => $rejectedAt === null && $i === $current && $req->status !== 'terminee',
                    'rejected' => $rejectedAt === $i,
                ])>
                    <span class="dot">{{ $rejectedAt === $i ? '✕' : $i + 1 }}</span>
                    <span class="lbl">{{ $rejectedAt === $i ? 'Rejetée' : $label }}</span>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<div class="sy-grid-2">
    <div>
        {{-- Étape suivante --}}
        @if($req->isPending())
            @php($direction = $req->status === 'en_attente_direction')
            @php($canDecide = $direction ? $canApprove : $canValidate)
            <section class="sy-card">
                <div class="sy-card-head">
                    <div>
                        <h2>{{ $direction ? 'Approbation du Directeur' : 'Avis technique' }}</h2>
                        <p>{{ $direction
                            ? 'Le responsable de la maintenance a donné un avis favorable. La demande attend l\'approbation du Directeur.'
                            : 'Le responsable de la maintenance examine la demande avant de la transmettre au Directeur.' }}</p>
                    </div>
                </div>
                <div class="sy-card-body">
                    @if($canDecide)
                        <form method="POST" action="{{ route('admin.maintenance-requests.decide', $req) }}">
                            @csrf
                            <div class="form-group">
                                <label for="decision_notes">Observations / motif</label>
                                <textarea name="decision_notes" id="decision_notes" rows="3" class="form-control {{ $errors->has('decision_notes') ? 'is-invalid' : '' }}" placeholder="Obligatoire en cas de rejet">{{ old('decision_notes') }}</textarea>
                                @error('decision_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="page-actions">
                                <button type="submit" name="decision" value="valider" class="btn btn-primary">
                                    <i class="bi bi-check2-circle"></i> {{ $direction ? 'Approuver' : 'Avis favorable — transmettre au Directeur' }}
                                </button>
                                <button type="submit" name="decision" value="rejeter" class="btn btn-default text-danger"><i class="bi bi-x-circle"></i> Rejeter</button>
                            </div>
                        </form>
                    @else
                        <div class="empty-state"><i class="bi bi-hourglass-split"></i>
                            {{ $direction ? 'En attente de l\'approbation du Directeur.' : 'En attente de l\'avis du responsable de la maintenance.' }}
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if($canEdit && $req->canBePlanned())
            <section class="sy-card">
                <div class="sy-card-head"><div><h2>{{ $req->status === 'planifiee' ? 'Replanifier' : 'Planifier l\'intervention' }}</h2><p>Demande approuvée. Une tâche est créée dans le calendrier et le technicien est notifié.</p></div></div>
                <div class="sy-card-body">
                    <form method="POST" action="{{ route('admin.maintenance-requests.plan', $req) }}" class="form-grid">
                        @csrf
                        <div class="form-group">
                            <label for="planned_for" class="required">Date d'intervention</label>
                            <input type="date" name="planned_for" id="planned_for" class="form-control {{ $errors->has('planned_for') ? 'is-invalid' : '' }}" value="{{ old('planned_for', $req->planned_for?->toDateString() ?? today()->addDay()->toDateString()) }}" required>
                            @error('planned_for')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="due_date">Échéance de fin</label>
                            <input type="date" name="due_date" id="due_date" class="form-control {{ $errors->has('due_date') ? 'is-invalid' : '' }}" value="{{ old('due_date') }}">
                            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group full">
                            <label for="assigned_to_id">Technicien / responsable</label>
                            <select name="assigned_to_id" id="assigned_to_id" class="form-control select2">
                                <option value="">—</option>
                                @foreach($technicians as $id => $name)
                                    <option value="{{ $id }}" @selected((int) old('assigned_to_id', $req->assigned_to_id) === $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="instructions">Consignes</label>
                            <textarea name="instructions" id="instructions" rows="2" class="form-control" style="min-height: 0">{{ old('instructions') }}</textarea>
                        </div>
                        <div class="form-group full page-actions">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-calendar-check"></i> {{ $req->status === 'planifiee' ? 'Mettre à jour le planning' : 'Planifier' }}</button>
                        </div>
                    </form>
                    @if($req->canBeStarted())
                        <form method="POST" action="{{ route('admin.maintenance-requests.start', $req) }}" style="margin-top: 6px">
                            @csrf
                            <button type="submit" class="btn btn-default"><i class="bi bi-play-fill"></i> Démarrer l'intervention maintenant</button>
                        </form>
                    @endif
                </div>
            </section>
        @endif

        @if($canEdit && $req->canBeCompleted())
            <section class="sy-card">
                <div class="sy-card-head"><div><h2>Clôturer l'intervention</h2><p>Compte rendu de ce qui a été fait.</p></div></div>
                <div class="sy-card-body">
                    <form method="POST" action="{{ route('admin.maintenance-requests.complete', $req) }}" class="form-grid">
                        @csrf
                        <div class="form-group full">
                            <label for="resolution" class="required">Travaux réalisés</label>
                            <textarea name="resolution" id="resolution" rows="3" class="form-control {{ $errors->has('resolution') ? 'is-invalid' : '' }}" required>{{ old('resolution') }}</textarea>
                            @error('resolution')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="cost">Coût (FCFA)</label>
                            <input type="number" min="0" step="1" name="cost" id="cost" class="form-control" value="{{ old('cost') }}">
                        </div>
                        @if($req->asset)
                            <div class="form-group">
                                <label>Matière après intervention</label>
                                <label class="perm-other" style="font-weight: 500"><input type="radio" name="back_in_service" value="1" checked> Remise en service</label>
                                <label class="perm-other" style="font-weight: 500"><input type="radio" name="back_in_service" value="0"> Hors service</label>
                            </div>
                        @elseif($req->infrastructure)
                            <div class="form-group">
                                <label for="condition">État constaté après intervention</label>
                                <select name="condition" id="condition" class="form-control">
                                    <option value="">Inchangé</option>
                                    @foreach(\App\Models\Infrastructure::CONDITIONS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="form-group full page-actions">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2-all"></i> Clôturer</button>
                        </div>
                    </form>
                </div>
            </section>
        @endif

        <section class="sy-card">
            <div class="sy-card-head"><h2>Description</h2></div>
            <div class="sy-card-body" style="white-space: pre-line">{{ $req->description ?: 'Aucune description.' }}</div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Suivi</h2></div>
            <div class="sy-card-body">
                <ul class="timeline">
                    <li>
                        <div class="t-date">{{ $req->created_at?->format('d/m/Y à H:i') }}</div>
                        <div class="t-title">Demande déposée par {{ $req->requester_name }}</div>
                    </li>
                    @if($req->validated_at)
                        <li>
                            <div class="t-date">{{ $req->validated_at->format('d/m/Y à H:i') }}</div>
                            <div class="t-title">
                                @if($req->kind === 'preventive' && ! $req->validatedBy)
                                    Validée automatiquement (plan préventif)
                                @else
                                    {{ $req->status === 'rejetee' && ! $req->approved_at ? 'Rejetée' : 'Avis technique favorable' }}{{ $req->validatedBy ? ' — '.$req->validatedBy->name : '' }}
                                @endif
                            </div>
                            @if($req->decision_notes)<div class="t-body">{{ $req->decision_notes }}</div>@endif
                        </li>
                    @endif
                    @if($req->approved_at)
                        <li>
                            <div class="t-date">{{ $req->approved_at->format('d/m/Y à H:i') }}</div>
                            <div class="t-title">{{ $req->status === 'rejetee' ? 'Rejetée par le Directeur' : 'Approuvée par le Directeur' }}{{ $req->approvedBy ? ' — '.$req->approvedBy->name : '' }}</div>
                            @if($req->approval_notes)<div class="t-body">{{ $req->approval_notes }}</div>@endif
                        </li>
                    @endif
                    @if($req->planned_for)
                        <li>
                            <div class="t-date">Prévue le {{ $req->planned_for->format('d/m/Y') }}</div>
                            <div class="t-title">Intervention planifiée{{ $req->assignedTo ? ' — '.$req->assignedTo->name : '' }}</div>
                        </li>
                    @endif
                    @if($req->started_at)
                        <li>
                            <div class="t-date">{{ $req->started_at->format('d/m/Y à H:i') }}</div>
                            <div class="t-title">Intervention démarrée</div>
                        </li>
                    @endif
                    @if($req->completed_at)
                        <li>
                            <div class="t-date">{{ $req->completed_at->format('d/m/Y à H:i') }}</div>
                            <div class="t-title">Intervention terminée{{ $req->lead_time !== null ? ' en '.$req->lead_time.' jour(s)' : '' }}</div>
                            @if($req->resolution)<div class="t-body">{{ $req->resolution }}</div>@endif
                        </li>
                    @endif
                </ul>
            </div>
        </section>
    </div>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Concerne</h2></div>
            <div class="sy-card-body">
                @if($req->target_type === 'infrastructure' && $req->infrastructure)
                    <div class="holder">
                        <span class="sy-avatar"><i class="bi bi-building"></i></span>
                        <div>
                            <div class="name"><a href="{{ route('admin.infrastructures.show', $req->infrastructure) }}">{{ $req->infrastructure->name }}</a></div>
                            <div class="meta">{{ $req->infrastructure->nature_label }}{{ $req->infrastructure->location ? ' · '.$req->infrastructure->location : '' }}</div>
                            <div class="meta"><span class="pill pill-{{ $req->infrastructure->status_tone }}">{{ $req->infrastructure->status_label }}</span></div>
                        </div>
                    </div>
                @elseif($req->target_type === 'asset' && $req->asset)
                    <div class="holder">
                        <span class="sy-avatar"><i class="bi bi-pc-display"></i></span>
                        <div>
                            <div class="name"><a href="{{ route('admin.assets.show', $req->asset) }}">{{ $req->asset->name ?: 'Matière #'.$req->asset->id }}</a></div>
                            <div class="meta">{{ $req->asset->category->name ?? '' }}{{ $req->asset->qr_code ? ' · '.$req->asset->qr_code : '' }}</div>
                            <div class="meta">{{ $req->asset->location->name ?? '' }}</div>
                        </div>
                    </div>
                @else
                    <span class="muted">{{ $req->target_label }}</span>
                @endif
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Détails</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Référence</dt><dd>{{ $req->reference }}</dd>
                    <dt>Type</dt><dd>{{ $req->kind_label }}@if($req->plan) · <a href="{{ route('admin.maintenance-plans.edit', $req->plan) }}">{{ $req->plan->title }}</a>@endif</dd>
                    <dt>Demandeur</dt><dd>{{ $req->requester_name }}</dd>
                    <dt>Établissement</dt><dd>{{ $req->establishment ?: '—' }}</dd>
                    <dt>Technicien</dt><dd>{{ $req->assignedTo->name ?? '—' }}</dd>
                    <dt>Coût</dt><dd>{{ \App\Support\Fmt::money($req->cost) }}</dd>
                </dl>
            </div>
        </section>

        @if($req->tasks->isNotEmpty())
            <section class="sy-card">
                <div class="sy-card-head"><h2>Tâches du planning</h2></div>
                <div class="sy-card-body flush">
                    <table class="dash-table">
                        <tbody>
                            @foreach($req->tasks as $task)
                                <tr>
                                    <td class="strong">
                                        @can('task_show')<a href="{{ route('admin.tasks.show', $task) }}">{{ $task->name }}</a>@else{{ $task->name }}@endcan
                                        <div class="muted">{{ $task->scheduled_date ?: '—' }}</div>
                                    </td>
                                    <td>{!! \App\Support\Tone::status($task->status->name ?? null) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @can('maintenance_request_delete')
            <form action="{{ route('admin.maintenance-requests.destroy', $req) }}" method="POST" onsubmit="return confirm('Supprimer cette demande ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer la demande</button>
            </form>
        @endcan
    </div>
</div>
@endsection
