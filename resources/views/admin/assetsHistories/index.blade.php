@extends('layouts.admin')
@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.assetsHistory.title'),
])
<div class="card">
    <div class="card-header">
        Liste
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-AssetsHistory">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Date</th>
                        <th>Mouvement</th>
                        <th>Matière</th>
                        <th>Détenteur</th>
                        <th>Statut</th>
                        <th>Emplacement</th>
                        <th>Bon</th>
                        <th>Par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assetsHistories as $assetsHistory)
                        <tr data-entry-id="{{ $assetsHistory->id }}">
                            <td></td>
                            <td class="nowrap" data-order="{{ $assetsHistory->created_at?->format('Y-m-d H:i:s') }}">{{ $assetsHistory->created_at?->format('d/m/Y H:i') }}</td>
                            <td><span class="pill pill-{{ $assetsHistory->action ?? 'modification' }}">{{ $assetsHistory->action_label }}</span></td>
                            <td class="strong">
                                @if($assetsHistory->asset)
                                    <a href="{{ route('admin.assets.show', $assetsHistory->asset) }}">{{ $assetsHistory->asset->name }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($assetsHistory->agent)
                                    <a href="{{ route('admin.agents.show', $assetsHistory->agent) }}">{{ $assetsHistory->agent->full_name }}</a>
                                @else
                                    {{ $assetsHistory->service->name ?? '—' }}
                                @endif
                            </td>
                            <td>{{ \App\Support\Tone::status($assetsHistory->status->name ?? null) }}</td>
                            <td>{{ $assetsHistory->location->name ?? '—' }}</td>
                            <td>
                                @if($assetsHistory->assignment)
                                    <a href="{{ route('admin.assignments.show', $assetsHistory->assignment) }}">{{ $assetsHistory->assignment->reference }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="muted">{{ $assetsHistory->user->name ?? '—' }}</td>
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
  
  $.extend(true, $.fn.dataTable.defaults, {
    orderCellsTop: true,
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  let table = $('.datatable-AssetsHistory:not(.ajaxTable)').DataTable({ buttons: dtButtons })
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });
  
})

</script>
@endsection