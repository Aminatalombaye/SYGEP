@extends('layouts.admin')

@section('content')
@php
    $tabs = ['' => 'Toutes'] + ['structure' => 'Structures', 'batiment' => 'Bâtiments', 'bloc' => 'Blocs'];
@endphp

@include('partials.module-overview', [
    'title' => trans('cruds.infrastructure.title'),
    'create' => ['route' => 'admin.infrastructures.create', 'can' => 'infrastructure_create', 'label' => 'Nouvelle infrastructure'],
])

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.infrastructures.index', array_filter(['nature' => $key])) }}" @class(['active' => (string) $nature === (string) $key])>
            {{ $label }}<span class="count">{{ $key === '' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-Infrastructure">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Infrastructure</th>
                        <th>Localisation</th>
                        <th>Situation</th>
                        <th>État</th>
                        <th>Valeur nette</th>
                        <th>Projets</th>
                        <th>Maintenance</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($infrastructures as $infra)
                        <tr data-entry-id="{{ $infra->id }}">
                            <td></td>
                            <td class="strong">
                                <a href="{{ route('admin.infrastructures.show', $infra) }}">{{ $infra->name }}</a>
                                <div class="muted">{{ $infra->nature_label }}{{ $infra->type ? ' · '.$infra->type : '' }}{{ $infra->parent ? ' · '.$infra->parent->name : '' }}</div>
                            </td>
                            <td class="muted">{{ $infra->location ?: '—' }}</td>
                            <td><span class="pill pill-{{ $infra->status_tone }}">{{ $infra->status_label }}</span></td>
                            <td>
                                @if($infra->condition)
                                    <span class="pill pill-{{ $infra->condition_tone }}">{{ $infra->condition_label }}</span>
                                @else
                                    <span class="muted">Non évalué</span>
                                @endif
                            </td>
                            <td class="nowrap" data-order="{{ (float) $infra->net_book_value }}">
                                @if($infra->canDepreciate())
                                    {{ \App\Support\Fmt::money($infra->net_book_value) }}
                                    <div class="muted">fin {{ $infra->depreciation_end->format('Y') }}</div>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>{{ $infra->projects_count ?: '—' }}</td>
                            <td>
                                @if($infra->open_requests_count)
                                    <span class="pill pill-warning">{{ $infra->open_requests_count }} en cours</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.infrastructures.show', $infra) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @can('infrastructure_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.infrastructures.edit', $infra) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
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
@can('infrastructure_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.infrastructures.massDestroy') }}",
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
    $('.datatable-Infrastructure:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[1, 'asc']], pageLength: 50 })
})
</script>
@endsection
