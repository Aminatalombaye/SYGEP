@extends('layouts.admin')
@section('content')
@can('bon_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.bons.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.bon.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.bon.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Bon">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.date_emission') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.organisation') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.reference_commande') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.nom_destinataire') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.bon') }}
                        </th>
                        <th>
                            {{ trans('cruds.bon.fields.date_livraison') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bons as $key => $bon)
                        <tr data-entry-id="{{ $bon->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $bon->id ?? '' }}
                            </td>
                            <td>
                                {{ $bon->date_emission ?? '' }}
                            </td>
                            <td>
                                {{ $bon->organisation ?? '' }}
                            </td>
                            <td>
                                {{ $bon->reference_commande ?? '' }}
                            </td>
                            <td>
                                {{ $bon->nom_destinataire ?? '' }}
                            </td>
                            <td>
                                {{ $bon->bon ?? '' }}
                            </td>
                            <td>
                                {{ $bon->date_livraison ?? '' }}
                            </td>
                            <td>
                                @can('bon_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.bons.show', $bon->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('bon_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.bons.edit', $bon->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('bon_delete')
                                    <form action="{{ route('admin.bons.destroy', $bon->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-xs btn-danger" value="{{ trans('global.delete') }}">
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
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('bon_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.bons.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-Bon:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection