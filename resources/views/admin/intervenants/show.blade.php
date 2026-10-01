@extends('layouts.admin')

@section('content')
@include('partials.show-head', ['module' => 'intervenant', 'record' => $intervenant, 'index' => 'admin.intervenants.index', 'edit' => ['route' => 'admin.intervenants.edit', 'can' => 'intervenant_edit']])

<div class="sy-grid-2">
    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Projets</h2></div>
            <div class="sy-card-body flush">
                @if($intervenant->projects->isEmpty())
                    <div class="empty-state"><i class="bi bi-kanban"></i> Aucun projet.</div>
                @else
                    <table class="dash-table">
                        <thead><tr><th>Projet</th><th>Mission</th><th>Avancement</th><th>Statut</th></tr></thead>
                        <tbody>
                            @foreach($intervenant->projects as $p)
                                <tr>
                                    <td class="strong"><a href="{{ route('admin.projects.show', $p) }}">{{ $p->name }}</a><div class="muted">{{ $p->reference }}</div></td>
                                    <td class="muted">{{ $p->pivot->mission ?: '—' }}</td>
                                    <td>{{ $p->progress }} %</td>
                                    <td><span class="pill pill-{{ $p->status_tone }}">{{ $p->status_label }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Jalons confiés</h2></div>
            <div class="sy-card-body flush">
                @if($intervenant->milestones->isEmpty())
                    <div class="empty-state"><i class="bi bi-signpost-split"></i> Aucun jalon.</div>
                @else
                    <table class="dash-table">
                        <tbody>
                            @foreach($intervenant->milestones as $m)
                                <tr>
                                    <td class="strong">{{ $m->title }}<div class="muted">{{ $m->project->name ?? '' }}</div></td>
                                    <td class="nowrap">{{ $m->due_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td>
                                        @if($m->done_at)
                                            <span class="pill pill-good">Atteint</span>
                                        @elseif($m->is_late)
                                            <span class="pill pill-critical">En retard</span>
                                        @else
                                            <span class="pill pill-warning">À venir</span>
                                        @endif
                                    </td>
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
            <div class="sy-card-head"><h2>Coordonnées</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Type</dt><dd>{{ $intervenant->role_label }}</dd>
                    <dt>Organisme</dt><dd>{{ $intervenant->organisation ?: '—' }}</dd>
                    <dt>Contact</dt><dd>{{ trim($intervenant->prenom.' '.$intervenant->nom) ?: '—' }}</dd>
                    <dt>Téléphone</dt><dd>{{ $intervenant->telephone ?: '—' }}</dd>
                    <dt>E-mail</dt><dd>{{ $intervenant->email ?: '—' }}</dd>
                    <dt>Adresse</dt><dd>{{ $intervenant->adresse ?: '—' }}</dd>
                    @if($intervenant->notes)<dt>Observations</dt><dd style="white-space: pre-line">{{ $intervenant->notes }}</dd>@endif
                </dl>
            </div>
        </section>
        @can('intervenant_delete')
            <form action="{{ route('admin.intervenants.destroy', $intervenant) }}" method="POST" onsubmit="return confirm('Supprimer cet intervenant ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer</button>
            </form>
        @endcan
    </div>
</div>
@endsection
