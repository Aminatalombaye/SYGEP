@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
      Liste projet-infrastructure
    </div>

    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover datatable datatable-AssetCategory">
    <thead>
        <tr>
            <th width="10"></th>
            <th>{{ trans('cruds.assetCategory.fields.id') }}</th>
            <th>Nom du projet</th>
            <th>Infrastructures</th>
            <th>Niveau d'exécution</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($project->infrastructures as $index => $infrastructure)
            <tr data-entry-id="">
                @if ($index === 0)
                    <td rowspan="{{ $project->infrastructures->count() }}"></td>
                    <td rowspan="{{ $project->infrastructures->count() }}">{{ $project->id ?? '' }}</td>
                    <td rowspan="{{ $project->infrastructures->count() }}">{{ $project->name ?? '' }}</td>
                @endif
                <td>{{ $infrastructure->name ?? '' }}</td>
                <td>
                    @if($infrastructure->ponderation !== null)
                        <strong>{{ $infrastructure->ponderation }}%</strong>
                    @else
                        <span class="text-muted">Aucune étape</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.etapes.create', ['infrastructure' => $infrastructure->id]) }}" class="btn btn-primary">
                        Ajouter étape d'exécution
                    </a>

                    @if($infrastructure->ponderation !== null)
                        <a class="btn btn-xs btn-info" href="{{ route('admin.etapes.show', ['infrastructure' => $infrastructure->id]) }}">
                            Voir étape d'exécution
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

        </div>
    </div>
    <div class="form-group mt-4">
                <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary">
                   Retour
                </a>
            </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);
        @can('asset_category_delete')
        let deleteButtonTrans = '{{ trans('global.datatables.delete') }}';
        let deleteButton = {
            text: deleteButtonTrans,
            url: "{{ route('admin.asset-categories.massDestroy') }}",
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
        
        let table = $('.datatable-AssetCategory:not(.ajaxTable)').DataTable({ buttons: dtButtons });
        
        $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
            $($.fn.dataTable.tables(true)).DataTable()
                .columns.adjust();
        });
    });
</script>
@endsection
