@extends('layouts.admin')

@section('content')
@php
    $tabs = [
        'actifs'    => 'En cours et planifiés',
        'en_retard' => 'En retard',
        'termine'   => 'Terminés',
        'tous'      => 'Tous',
    ];
@endphp

<div class="page-head">
    <div>
        <h1>{{ trans('cruds.project.title') }}</h1>
        <p class="sub">Construction et réhabilitation des structures : avancement, budget et échéances.</p>
    </div>
    <div class="page-actions">
        @can('intervenant_access')
            <a href="{{ route('admin.intervenants.index') }}" class="btn btn-default"><i class="bi bi-people"></i> Intervenants</a>
        @endcan
        @can('project_create')
            <a href="{{ route('admin.projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau projet</a>
        @endcan
    </div>
</div>

@include('partials.module-overview', ['head' => false])

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.projects.index', ['statut' => $key]) }}" @class(['active' => $filter === $key])>
            {{ $label }}<span class="count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-Project">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Référence</th>
                        <th>Projet</th>
                        <th>Infrastructure(s)</th>
                        <th>Avancement</th>
                        <th>Budget</th>
                        <th>Échéance</th>
                        <th>Statut</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projects as $project)
                        <tr data-entry-id="{{ $project->id }}">
                            <td></td>
                            <td class="strong nowrap"><a href="{{ route('admin.projects.show', $project) }}">{{ $project->reference ?: '#'.$project->id }}</a></td>
                            <td class="strong">
                                {{ $project->name }}
                                <div class="muted">{{ $project->type_label }}@if($project->chef_projets->isNotEmpty()) · {{ $project->chef_projets->map(fn ($c) => trim($c->prenom.' '.$c->nom))->implode(', ') }}@endif</div>
                            </td>
                            <td class="muted">{{ $project->infrastructures->pluck('name')->implode(', ') ?: '—' }}</td>
                            <td data-order="{{ $project->progress }}">
                                <div class="read-bar" style="width: 130px"><span style="width: {{ $project->progress }}%"></span></div>
                                <span class="muted">
                                    {{ $project->progress }} %
                                    @if($project->milestones_count) · {{ $project->milestones_done_count }}/{{ $project->milestones_count }} jalons @endif
                                </span>
                            </td>
                            <td class="nowrap" data-order="{{ (float) $project->budget }}">
                                {{ \App\Support\Fmt::money($project->budget) }}
                                @if($project->budget_rate !== null)<div class="muted">{{ $project->budget_rate }} % engagé</div>@endif
                            </td>
                            <td class="nowrap" data-order="{{ $project->end_date?->toDateString() }}">
                                {{ $project->end_date?->format('d/m/Y') ?? '—' }}
                                @if($project->is_late)<div><span class="pill pill-critical">En retard</span></div>@endif
                            </td>
                            <td><span class="pill pill-{{ \App\Models\Project::STATUS_TONES[$project->status] ?? 'neutral' }}">{{ $project->status_label }}</span></td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.projects.show', $project) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @can('project_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.projects.edit', $project) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('project_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.projects.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) { return $(entry).data('entry-id') });
            if (ids.length === 0) { alert('{{ trans('global.datatables.zero_selected') }}'); return }
            if (confirm('{{ trans('global.areYouSure') }}')) {
                $.ajax({ headers: {'x-csrf-token': _token}, method: 'POST', url: config.url, data: { ids: ids, _method: 'DELETE' } })
                    .done(function () { location.reload() })
            }
        }
    })
@endcan
    $('.datatable-Project:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[6, 'asc']], pageLength: 50 })
})
</script>
@endsection
