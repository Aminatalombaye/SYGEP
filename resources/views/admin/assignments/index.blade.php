@extends('layouts.admin')

@section('content')
@php
    $tabs = [
        'en_cours'  => 'En cours',
        'en_retard' => 'En retard',
        'restitue'  => 'Restituées',
        'tous'      => 'Toutes',
    ];
@endphp

<div class="page-head">
    <div>
        <h1>Affectations</h1>
        <p class="sub">Bons d'affectation du matériel aux agents et aux services.</p>
    </div>
    <div class="page-actions">
        @can('assignment_create')
            <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nouvelle affectation
            </a>
        @endcan
    </div>
</div>

@include('partials.module-overview', ['head' => false])

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.assignments.index', ['statut' => $key]) }}" @class(['active' => $filter === $key])>
            {{ $label }}<span class="count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-Assignment">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Référence</th>
                        <th>Bénéficiaire</th>
                        <th>Service</th>
                        <th>Matières</th>
                        <th>Affecté le</th>
                        <th>Retour prévu</th>
                        <th>Statut</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                        @php($overdue = $assignment->isOverdue())
                        <tr data-entry-id="{{ $assignment->id }}">
                            <td></td>
                            <td class="strong nowrap">
                                <a href="{{ route('admin.assignments.show', $assignment) }}">{{ $assignment->reference }}</a>
                            </td>
                            <td>
                                @if($assignment->agent)
                                    <a href="{{ route('admin.agents.show', $assignment->agent) }}">{{ $assignment->agent->full_name }}</a>
                                @else
                                    {{ $assignment->beneficiary }}
                                @endif
                            </td>
                            <td>{{ $assignment->service->name ?? $assignment->agent?->service?->name ?? '—' }}</td>
                            <td class="nowrap">
                                @if($assignment->isOpen() && $assignment->outstanding_count < $assignment->matieres_count)
                                    {{ $assignment->outstanding_count }} / {{ $assignment->matieres_count }}
                                @else
                                    {{ $assignment->matieres_count }}
                                @endif
                            </td>
                            <td class="nowrap" data-order="{{ $assignment->assigned_at?->format('Y-m-d') }}">{{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="nowrap" data-order="{{ $assignment->expected_return_at?->format('Y-m-d') }}">
                                {{ $assignment->expected_return_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td>
                                @if($overdue)
                                    <span class="pill pill-en_retard"><i class="bi bi-clock-history"></i> En retard</span>
                                @else
                                    <span class="pill pill-{{ $assignment->status }}">{{ $assignment->status_label }}</span>
                                @endif
                            </td>
                            <td class="nowrap">
                                @can('assignment_show')
                                    <a class="btn btn-xs btn-default" href="{{ route('admin.assignments.show', $assignment) }}">Voir</a>
                                @endcan
                                @if($assignment->isOpen())
                                    @can('assignment_return')
                                        <a class="btn btn-xs btn-primary" href="{{ route('admin.assignments.return', $assignment) }}">Restituer</a>
                                    @endcan
                                @endif
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
@can('assignment_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.assignments.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                return $(entry).data('entry-id')
            });
            if (ids.length === 0) {
                alert('{{ trans('global.datatables.zero_selected') }}')
                return
            }
            if (confirm('Supprimer ces bons ? Les matières encore détenues seront libérées.')) {
                $.ajax({
                    headers: {'x-csrf-token': _token},
                    method: 'POST',
                    url: config.url,
                    data: { ids: ids, _method: 'DELETE' }
                }).done(function () { location.reload() })
            }
        }
    })
@endcan
    $('.datatable-Assignment:not(.ajaxTable)').DataTable({
        buttons: dtButtons,
        order: [[5, 'desc']],
        pageLength: 50
    })
})
</script>
@endsection
