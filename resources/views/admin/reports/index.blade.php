@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.report.title'),
    'create' => ['route' => 'admin.reports.create', 'can' => 'report_create', 'label' => trans('global.add').' '.trans('cruds.report.title_singular')],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Report">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.report.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.report.fields.title') }}
                        </th>
                        <th>
                            {{ trans('cruds.report.fields.content') }}
                        </th>
                        <th>
                            {{ trans('cruds.report.fields.report_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.report.fields.project') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $key => $report)
                        <tr data-entry-id="{{ $report->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $report->id ?? '' }}
                            </td>
                            <td>
                                {{ $report->title ?? '' }}
                            </td>
                            <td>
                                @if($report->content)
                                    <a href="{{ $report->content->getUrl() }}" target="_blank">
                                        {{ trans('global.view_file') }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ $report->report_date ?? '' }}
                            </td>
                            <td>
                                @foreach($report->projects as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @can('report_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.reports.show', $report->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan

                                @can('report_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.reports.edit', $report->id) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan

                                @can('report_delete')
                                    <form action="{{ route('admin.reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
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
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('report_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.reports.massDestroy') }}",
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
  let table = $('.datatable-Report:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection