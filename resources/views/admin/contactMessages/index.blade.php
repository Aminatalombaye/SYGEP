@extends('layouts.admin')
@section('content')
@include('partials.module-overview', ['title' => 'Messages de contact'])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-ContactMessage">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>ID</th>
                        <th>Reçu le</th>
                        <th>Nom</th>
                        <th>E-mail</th>
                        <th>Objet</th>
                        <th>Statut</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contactMessages as $contactMessage)
                        <tr data-entry-id="{{ $contactMessage->id }}" @if(!$contactMessage->isRead()) style="font-weight:600" @endif>
                            <td></td>
                            <td>{{ $contactMessage->id }}</td>
                            <td>{{ $contactMessage->created_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $contactMessage->name }}</td>
                            <td><a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a></td>
                            <td>{{ $contactMessage->subject_label }}</td>
                            <td>
                                @if($contactMessage->isRead())
                                    <span class="badge badge-secondary">Lu</span>
                                @else
                                    <span class="badge badge-danger">Nouveau</span>
                                @endif
                            </td>
                            <td>
                                @can('contact_message_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.contact-messages.show', $contactMessage->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan
                                @can('contact_message_delete')
                                    <form action="{{ route('admin.contact-messages.destroy', $contactMessage->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
                                        @method('DELETE')
                                        @csrf
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
@can('contact_message_delete')
  let deleteButton = {
    text: '{{ trans('global.datatables.delete') }}',
    url: "{{ route('admin.contact-messages.massDestroy') }}",
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
  $('.datatable-ContactMessage:not(.ajaxTable)').DataTable({ buttons: dtButtons })
})
</script>
@endsection
