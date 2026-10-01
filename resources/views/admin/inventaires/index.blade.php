@extends('layouts.admin')

@section('content')
@include('partials.module-overview', [
    'title' => trans('cruds.inventaire.title'),
    'create' => ['route' => 'admin.inventaires.create', 'can' => 'inventaire_create', 'label' => 'Nouvelle campagne'],
])

<div class="card">
    <div class="card-header">Campagnes d'inventaire</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-Inventaire">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Référence</th>
                        <th>Campagne</th>
                        <th>Périmètre</th>
                        <th>Avancement</th>
                        <th>Écarts</th>
                        <th>Période</th>
                        <th>Statut</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventaires as $inventaire)
                        @php($st = $inventaire->stats)
                        <tr data-entry-id="{{ $inventaire->id }}">
                            <td></td>
                            <td class="strong nowrap"><a href="{{ route('admin.inventaires.show', $inventaire) }}">{{ $inventaire->reference }}</a></td>
                            <td class="strong">{{ $inventaire->nom ?: '—' }}</td>
                            <td class="muted">{{ $inventaire->scope_label }}</td>
                            <td data-order="{{ $st['progress'] }}">
                                @if($inventaire->isDraft())
                                    <span class="muted">Non démarrée</span>
                                @else
                                    <div class="read-bar" style="width: 130px"><span style="width: {{ $st['progress'] }}%"></span></div>
                                    <span class="muted">{{ $st['checked'] }} / {{ $st['expected'] }} · {{ $st['progress'] }} %</span>
                                @endif
                            </td>
                            <td>
                                @php($gaps = $st['missing'] + $st['outside'] + $st['damaged'] + $st['moved'])
                                @if($gaps)
                                    <span class="pill pill-warning">{{ $gaps }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="nowrap muted">
                                {{ $inventaire->starts_at?->format('d/m/Y') ?? '…' }} → {{ $inventaire->ends_at?->format('d/m/Y') ?? '…' }}
                            </td>
                            <td>
                                <span class="pill pill-{{ ['brouillon' => 'neutral', 'en_cours' => 'info', 'cloture' => 'good'][$inventaire->status] ?? 'neutral' }}">{{ $inventaire->status_label }}</span>
                            </td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.inventaires.show', $inventaire) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @if($inventaire->isRunning())
                                    @can('inventaire_edit')
                                        <a class="btn btn-xs btn-icon" href="{{ route('admin.inventaires.scan', $inventaire) }}" title="Scanner" aria-label="Scanner"><i class="bi bi-qr-code-scan"></i></a>
                                    @endcan
                                @endif
                                @if(! $inventaire->isDraft())
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.inventaires.report', $inventaire) }}" target="_blank" title="Procès-verbal" aria-label="Procès-verbal"><i class="bi bi-file-earmark-text"></i></a>
                                @endif
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
@can('inventaire_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.inventaires.massDestroy') }}",
        className: 'btn-danger',
        action: function (e, dt, node, config) {
            var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) { return $(entry).data('entry-id') });
            if (ids.length === 0) { alert('{{ trans('global.datatables.zero_selected') }}'); return }
            if (confirm('{{ trans('global.areYouSure') }}')) {
                $.ajax({ headers: {'x-csrf-token': _token}, method: 'POST', url: config.url, data: { ids: ids, _method: 'DELETE' } })
                    .done(function () { location.reload() })
            }
        }
    })
@endcan
    $('.datatable-Inventaire:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[1, 'desc']], pageLength: 50 })
})
</script>
@endsection
