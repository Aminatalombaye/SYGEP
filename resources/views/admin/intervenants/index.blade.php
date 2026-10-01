@extends('layouts.admin')

@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.intervenant.title'),
    'create' => ['route' => 'admin.intervenants.create', 'can' => 'intervenant_create', 'label' => 'Nouvel intervenant'],
])

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-Intervenant">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Intervenant</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Projets</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($intervenants as $i)
                        <tr data-entry-id="{{ $i->id }}">
                            <td></td>
                            <td class="strong"><a href="{{ route('admin.intervenants.show', $i) }}">{{ $i->name }}</a></td>
                            <td><span class="pill pill-neutral">{{ $i->role_label }}</span></td>
                            <td class="muted">{{ collect([$i->telephone, $i->email])->filter()->implode(' · ') ?: '—' }}</td>
                            <td data-order="{{ $i->projects_count }}">
                                {{ $i->projects_count }}
                                @if($i->active_projects_count)<span class="muted">· {{ $i->active_projects_count }} en cours</span>@endif
                            </td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.intervenants.show', $i) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @can('intervenant_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.intervenants.edit', $i) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
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
@can('intervenant_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.intervenants.massDestroy') }}",
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
    $('.datatable-Intervenant:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[1, 'asc']], pageLength: 50 })
})
</script>
@endsection
