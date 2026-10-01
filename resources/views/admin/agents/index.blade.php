@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.agent.title'),
    'create' => ['route' => 'admin.agents.create', 'can' => 'agent_create', 'label' => trans('global.add').' '.trans('cruds.agent.title_singular')],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-agent">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>{{ trans('cruds.agent.fields.id') }}</th>
                        <th>{{ trans('cruds.agent.fields.nom') }}</th>
                        <th>{{ trans('cruds.agent.fields.prenom') }}</th>
                        <th>Matériel détenu</th>
                        <th>{{ trans('cruds.agent.fields.email') }}</th>
                        <th>{{ trans('cruds.agent.fields.telephone') }}</th>
                        <th>{{ trans('cruds.agent.fields.service') }}</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $key => $agent)
                        <tr data-entry-id="{{ $agent->id }}">
                            <td></td>
                            <td>{{ $agent->id ?? '' }}</td>
                            <td class="strong"><a href="{{ route('admin.agents.show', $agent) }}">{{ $agent->nom ?? '' }}</a></td>
                            <td>{{ $agent->prenom ?? '' }}</td>
                            <td>{{ $agent->assets_count ?: '—' }}</td>
                            <td>{{ $agent->email ?? '' }}</td>
                            <td>{{ $agent->telephone ?? '' }}</td>
                            <td>{{ $agent->service->name ?? '' }}</td>
                            <td>
                                @can('agent_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.agents.show', $agent->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan

                                @can('agent_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.agents.edit', $agent->id) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan

                                @can('agent_delete')
                                    <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-icon btn-icon-danger" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash3"></i></button>
                                    </form>
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
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);
        @can('agent_delete')
            let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
            let deleteButton = {
                text: deleteButtonTrans,
                url: "{{ route('admin.agents.massDestroy') }}",
                className: 'btn-danger',
                action: function (e, dt, node, config) {
                    var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
                        return $(entry).data('entry-id');
                    });

                    if (ids.length === 0) {
                        alert('{{ trans('global.datatables.zero_selected') }}');
                        return;
                    }

                    if (confirm('{{ trans('global.areYouSure') }}')) {
                        $.ajax({
                            headers: {'x-csrf-token': _token},
                            method: 'POST',
                            url: config.url,
                            data: { ids: ids, _method: 'DELETE' }
                        }).done(function () { location.reload(); });
                    }
                }
            };
            dtButtons.push(deleteButton);
        @endcan

        $.extend(true, $.fn.dataTable.defaults, {
            orderCellsTop: true,
            order: [[ 1, 'desc' ]],
            pageLength: 100,
        });
        let table = $('.datatable-agent:not(.ajaxTable)').DataTable({ buttons: dtButtons });
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });
    });
</script>
@endsection
