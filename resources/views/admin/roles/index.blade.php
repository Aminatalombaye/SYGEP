@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.role.title'),
    'create' => ['route' => 'admin.roles.create', 'can' => 'role_create', 'label' => trans('global.add').' '.trans('cruds.role.title_singular')],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Role">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.role.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.role.fields.title') }}
                        </th>
                        <th>
                            Description
                        </th>
                        <th>
                            Droits par domaine
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $key => $role)
                        <tr data-entry-id="{{ $role->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $role->id ?? '' }}
                            </td>
                            <td>
                                <strong>{{ $role->title ?? '' }}</strong>
                            </td>
                            <td style="min-width: 260px; max-width: 420px; white-space: normal">
                                {{ $role->description ?: '—' }}
                            </td>
                            <td data-order="{{ $role->permissions->count() }}">
                                @php
                                    $catalog = \App\Support\PermissionCatalog::class;
                                    $mine = $role->permissions->countBy(fn ($p) => $catalog::sectionOf($catalog::parse($p->title)[0]));
                                    $total = $sectionTotals->sum();
                                    $short = ['admin' => 'Administration', 'matieres' => 'Matières', 'stock' => 'Stock', 'infrastructures' => 'Infrastructures', 'maintenance' => 'Maintenance', 'rapports' => 'Rapports', 'autres' => 'Autres'];
                                @endphp
                                <div class="role-perms">
                                    @if($role->permissions->isEmpty())
                                        <span class="muted">Aucun droit</span>
                                    @elseif($role->permissions->count() >= $total)
                                        <span class="role-perm is-full"><i class="bi bi-stars"></i> Tous les droits</span>
                                    @else
                                        @foreach($catalog::SECTIONS as $key => [$label, $icon])
                                            @continue(empty($mine[$key]))
                                            <span class="role-perm {{ $mine[$key] >= ($sectionTotals[$key] ?? 0) ? 'is-full' : '' }}" title="{{ $label }} : {{ $mine[$key] }} droit(s) sur {{ $sectionTotals[$key] ?? 0 }}">
                                                <i class="bi {{ $icon }}"></i> {{ $short[$key] }}
                                                <strong>{{ $mine[$key] }}<small>/{{ $sectionTotals[$key] ?? 0 }}</small></strong>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="muted" style="margin-top: 4px">
                                    {{ $role->permissions->count() }} droit(s) · {{ $role->users_count }} utilisateur(s)
                                </div>
                            </td>
                            <td>
                                @can('role_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.roles.show', $role->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
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
@can('role_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.roles.massDestroy') }}",
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
  let table = $('.datatable-Role:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection