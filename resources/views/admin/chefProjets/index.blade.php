@extends('layouts.admin')
@section('content')
@can('chef_projet_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.chef-projets.create') }}">
                {{ trans('global.add') }} {{ trans('cruds.chefProjet.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('cruds.chefProjet.title_singular') }} {{ trans('global.list') }}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-ChefProjet">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.nom') }}
                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.prenom') }}
                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.adresse') }}
                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.e_mail') }}
                        </th>
                        <th>
                            {{ trans('cruds.chefProjet.fields.telephone') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chefProjets as $key => $chefProjet)
                        <tr data-entry-id="{{ $chefProjet->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $chefProjet->id ?? '' }}
                            </td>
                            <td>
                                {{ $chefProjet->nom ?? '' }}
                            </td>
                            <td>
                                {{ $chefProjet->prenom ?? '' }}
                            </td>
                            <td>
                                {{ $chefProjet->adresse ?? '' }}
                            </td>
                            <td>
                                {{ $chefProjet->e_mail ?? '' }}
                            </td>
                            <td>
                                {{ $chefProjet->telephone ?? '' }}
                            </td>
                            <td>
                                @can('chef_projet_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.chef-projets.show', $chefProjet->id) }}">
                                        {{ trans('global.view') }}
                                    </a>
                                @endcan

                                @can('chef_projet_edit')
                                    <a class="btn btn-xs btn-info" href="{{ route('admin.chef-projets.edit', $chefProjet->id) }}">
                                        {{ trans('global.edit') }}
                                    </a>
                                @endcan

                                @can('chef_projet_delete')
                                    <form action="{{ route('admin.chef-projets.destroy', $chefProjet->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('chef_projet_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.chef-projets.massDestroy') }}",
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
  let table = $('.datatable-ChefProjet:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection