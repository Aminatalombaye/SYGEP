@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.task.title'),
    'create' => ['route' => 'admin.tasks.create', 'can' => 'task_create', 'label' => trans('global.add').' '.trans('cruds.task.title_singular')],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Task">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.task.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.description') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.tag') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.attachment') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.due_date') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.assigned_to') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.equipement') }}
                        </th>
                        <th>
                            {{ trans('cruds.task.fields.scheduled_date') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $key => $task)
                        <tr data-entry-id="{{ $task->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $task->id ?? '' }}
                            </td>
                            <td>
                                {{ $task->name ?? '' }}
                            </td>
                            <td>
                                {{ $task->description ?? '' }}
                            </td>
                            <td>
                                {{ \App\Support\Tone::status($task->status->name ?? null) }}
                            </td>
                            <td>
                                @foreach($task->tags as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($task->attachment)
                                    <a href="{{ $task->attachment->getUrl() }}" target="_blank">
                                        {{ trans('global.view_file') }}
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ $task->due_date ?? '' }}
                            </td>
                            <td>
                                {{ $task->assigned_to->name ?? '' }}
                            </td>
                            <td>
                                @foreach($task->equipements as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}{{ $item->serial_number ? " (".$item->serial_number.")" : "" }}</span>
                                @endforeach
                            </td>
                            <td>
                                {{ $task->scheduled_date ?? '' }}
                            </td>
                            <td>
                                @can('task_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.tasks.show', $task->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan

                                @can('task_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.tasks.edit', $task->id) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan

                                @can('task_delete')
                                    <form action="{{ route('admin.tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('task_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.tasks.massDestroy') }}",
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
  let table = $('.datatable-Task:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection