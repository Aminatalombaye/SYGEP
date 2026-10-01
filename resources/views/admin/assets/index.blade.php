@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.asset.title'),
    'create' => ['route' => 'admin.assets.create', 'can' => 'asset_create', 'label' => trans('global.add').' '.trans('cruds.asset.title_singular')],
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Asset">
                <thead>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.id') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.category') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.serial_number') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.name') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.photos') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.status') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.location') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.notes') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.type') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.date_achat') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.date_mise_en_service') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.modele') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.fournisseur') }}
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.bon') }}
                        </th>
                        <th>
                            Détenteur
                        </th>
                        <th>
                            {{ trans('cruds.asset.fields.inventaire_code') }}
                        </th>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $key => $asset)
                        <tr data-entry-id="{{ $asset->id }}">
                            <td>

                            </td>
                            <td>
                                {{ $asset->id ?? '' }}
                            </td>
                            <td>
                                {{ $asset->category->name ?? '' }}
                            </td>
                            <td>
                                {{ $asset->serial_number ?? '' }}
                            </td>
                            <td>
                                {{ $asset->name ?? '' }}
                            </td>
                            <td>
                                @foreach($asset->photos as $key => $media)
                                    <a href="{{ $media->getUrl() }}" target="_blank">
                                        {{ trans('global.view_file') }}
                                    </a>
                                @endforeach
                            </td>
                            <td>
                                @if($asset->status)<span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>@endif
                            </td>
                            <td>
                                {{ $asset->location->name ?? '' }}
                            </td>
                            <td>
                                {{ $asset->notes ?? '' }}
                            </td>
                            <td>
                                {{ $asset->type ?? '' }}
                            </td>
                            <td>
                                {{ $asset->date_achat ?? '' }}
                            </td>
                            <td>
                                {{ $asset->date_mise_en_service ?? '' }}
                            </td>
                            <td>
                                {{ $asset->modele ?? '' }}
                            </td>
                            <td>
                                @foreach($asset->fournisseurs as $key => $item)
                                    <span class="badge badge-info">{{ $item->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @foreach($asset->bons as $key => $item)
                                    <span class="badge badge-info">{{ $item->bon }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($asset->agent)
                                    <a href="{{ route('admin.agents.show', $asset->agent) }}">{{ $asset->agent->full_name }}</a>
                                @elseif($asset->service)
                                    {{ $asset->service->name }}
                                @else
                                    {{ $asset->assigned_to ?? '' }}
                                @endif
                            </td>
                            <td>
                                @foreach($asset->inventaire_codes as $key => $item)
                                    <span class="badge badge-info">{{ $item->reference }}</span>
                                @endforeach
                            </td>
                            <td>
                                @can('asset_show')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.assets.show', $asset->id) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                @endcan

                                @can('asset_edit')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.assets.edit', $asset->id) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan

                                @can('asset_delete')
                                    <form action="{{ route('admin.assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}');" style="display: inline-block;">
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
@can('asset_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.assets.massDestroy') }}",
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
  let table = $('.datatable-Asset:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection