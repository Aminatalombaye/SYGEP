@extends('layouts.admin')
@section('content')
@can('maintenance_request_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.maintenance-requests.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.maintenanceRequest.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.maintenanceRequest.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-MaintenanceRequest">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.created_by') }}
                        </th>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.request') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($maintenanceRequests as $key => $maintenanceRequest)
                        <tr data-entry-id="{{ $maintenanceRequest->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $maintenanceRequest->id ?? '' }}
                            </td>
                            <td>
                                {{ $maintenanceRequest->description ?? '' }}
                            </td>
                            <td>
                                {{ $maintenanceRequest->status ?? '' }}
                            </td>
                            <td>
                                {{ $maintenanceRequest->created_by ?? '' }}
                            </td>
                            <td>
                                @foreach($maintenanceRequest->requests as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @can('maintenance_request_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.maintenance-requests.show', $maintenanceRequest->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('maintenance_request_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.maintenance-requests.edit', $maintenanceRequest->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('maintenance_request_delete')
                                    <form action="{{ route('admin.maintenance-requests.destroy', $maintenanceRequest->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('maintenance_request_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.maintenance-requests.massDestroy') }}",
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
  let table = $('.datatable-MaintenanceRequest:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection