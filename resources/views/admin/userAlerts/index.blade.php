@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.userAlert.title'),
    'create' => ['route' => 'admin.user-alerts.create', 'can' => 'user_alert_create', 'label' => 'Envoyer une notification'],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-UserAlert">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Destinataires</th>
                        <th>Lecture</th>
                        <th>Envoyée le</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userAlerts as $userAlert)
                        @php($pct = $userAlert->users_count ? round($userAlert->read_count * 100 / $userAlert->users_count) : 0)
                        <tr data-entry-id="{{ $userAlert->id }}">
                            <td></td>
                            <td class="strong">
                                <i class="bi {{ $userAlert->icon }} muted"></i>
                                <a href="{{ route('admin.user-alerts.show', $userAlert) }}">{{ \Illuminate\Support\Str::limit($userAlert->alert_text, 90) }}</a>
                            </td>
                            <td><span class="pill pill-info">{{ $userAlert->kind_label }}</span></td>
                            <td>{{ $userAlert->users_count }}</td>
                            <td data-order="{{ $pct }}">
                                <div class="read-bar" title="{{ $userAlert->read_count }} / {{ $userAlert->users_count }}"><span style="width: {{ $pct }}%"></span></div>
                                <span class="muted">{{ $userAlert->read_count }} / {{ $userAlert->users_count }}</span>
                            </td>
                            <td class="nowrap" data-order="{{ $userAlert->created_at?->format('Y-m-d H:i:s') }}">{{ $userAlert->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="nowrap">
                                @can('user_alert_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.user-alerts.show', $userAlert) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan
                                @can('user_alert_delete')
                                    <form action="{{ route('admin.user-alerts.destroy', $userAlert) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('user_alert_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.user-alerts.massDestroy') }}",
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
    order: [[ 5, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-UserAlert:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection