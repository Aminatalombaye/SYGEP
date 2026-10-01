@extends('layouts.admin')

@section('content')
@php
    $tabs = [
        'a_valider' => 'En attente de décision',
        'ouvertes'  => 'En cours de traitement',
        'terminee'  => 'Terminées',
        'rejetee'   => 'Rejetées',
        'toutes'    => 'Toutes',
    ];
@endphp

<div class="page-head">
    <div>
        <h1>{{ trans('cruds.maintenanceRequest.title') }}</h1>
        <p class="sub">Demandes d'intervention : soumission, validation, planification et clôture.</p>
    </div>
    <div class="page-actions">
        @can('maintenance_plan_access')
            <a href="{{ route('admin.maintenance-plans.index') }}" class="btn btn-default"><i class="bi bi-arrow-repeat"></i> Maintenance préventive</a>
        @endcan
        @can('maintenance_request_create')
            <a href="{{ route('admin.maintenance-requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle demande</a>
        @endcan
    </div>
</div>

@include('partials.module-overview', ['head' => false])

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.maintenance-requests.index', ['statut' => $key]) }}" @class(['active' => $filter === $key])>
            {{ $label }}<span class="count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-MaintenanceRequest">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Référence</th>
                        <th>Objet</th>
                        <th>Concerne</th>
                        <th>Priorité</th>
                        <th>Demandeur</th>
                        <th>Intervention</th>
                        <th>Statut</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr data-entry-id="{{ $req->id }}">
                            <td></td>
                            <td class="strong nowrap" data-order="{{ $req->id }}">
                                <a href="{{ route('admin.maintenance-requests.show', $req) }}">{{ $req->reference ?: '#'.$req->id }}</a>
                                <div class="muted">{{ $req->created_at?->format('d/m/Y') }}</div>
                            </td>
                            <td class="strong">
                                {{ $req->title ?: \Illuminate\Support\Str::limit($req->description, 60) }}
                                <div class="muted">{{ $req->kind_label }}</div>
                            </td>
                            <td class="muted">{{ $req->target_label }}</td>
                            <td data-order="{{ array_search($req->priority, array_keys(\App\Models\MaintenanceRequest::PRIORITIES)) }}">
                                <span class="pill pill-{{ $req->priority_tone }}">{{ $req->priority_label }}</span>
                            </td>
                            <td class="muted">{{ $req->requester_name }}@if($req->establishment)<div>{{ $req->establishment }}</div>@endif</td>
                            <td class="nowrap">
                                @if($req->planned_for)
                                    {{ $req->planned_for->format('d/m/Y') }}
                                    @if($req->assignedTo)<div class="muted">{{ $req->assignedTo->name }}</div>@endif
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td><span class="pill pill-{{ $req->status_tone }}">{{ $req->status_label }}</span></td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.maintenance-requests.show', $req) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @can('maintenance_request_edit')
                                    @if($req->isOpen())
                                        <a class="btn btn-xs btn-icon" href="{{ route('admin.maintenance-requests.edit', $req) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                    @endif
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
@can('maintenance_request_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.maintenance-requests.massDestroy') }}",
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
    $('.datatable-MaintenanceRequest:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[1, 'desc']], pageLength: 50 })
})
</script>
@endsection
