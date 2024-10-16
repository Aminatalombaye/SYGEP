@extends('layouts.admin')
@section('content')
@can('attribution_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.attributions.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.attribution.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.attribution.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Attribution">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.attribution.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.attribution.fields.nom') }}
                        </th>
                        <th>
                            {{ trans('cruds.attribution.fields.type_atribution') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributions as $key => $attribution)
                        <tr data-entry-id="{{ $attribution->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $attribution->id ?? '' }}
                            </td>
                            <td>
                                {{ $attribution->nom ?? '' }}
                            </td>
                            <td>
                                {{ $attribution->type_atribution ?? '' }}
                            </td>
                            <td>
                                @can('attribution_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.attributions.show', $attribution->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('attribution_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.attributions.edit', $attribution->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('attribution_delete')
                                    <form action="{{ route('admin.attributions.destroy', $attribution->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('attribution_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.attributions.massDestroy') }}",
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
  let table = $('.datatable-Attribution:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection