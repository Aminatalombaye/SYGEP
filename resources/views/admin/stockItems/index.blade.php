@extends('layouts.admin')

@section('content')
@php
    $tabs = ['tous' => 'Tous les articles', 'bas' => 'Sous le seuil', 'rupture' => 'En rupture'];
@endphp

<div class="page-head">
    <div>
        <h1>{{ trans('cruds.stockItem.title') }}</h1>
        <p class="sub">Matières consommables : niveaux de stock, seuils d'alerte et valeur.</p>
    </div>
    <div class="page-actions">
        @can('stock_movement_create')
            <a href="{{ route('admin.stock-movements.create', ['type' => 'entree']) }}" class="btn btn-default"><i class="bi bi-box-arrow-in-down"></i> Entrée</a>
            <a href="{{ route('admin.stock-movements.create', ['type' => 'sortie']) }}" class="btn btn-default"><i class="bi bi-box-arrow-up"></i> Sortie</a>
        @endcan
        @can('stock_item_create')
            <a href="{{ route('admin.stock-items.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvel article</a>
        @endcan
    </div>
</div>

@include('partials.module-overview', ['head' => false])

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.stock-items.index', array_filter(['niveau' => $key, 'famille' => $category])) }}" @class(['active' => $filter === $key])>
            {{ $label }}<span class="count">{{ $counts[$key] }}</span>
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-StockItem">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Référence</th>
                        <th>Article</th>
                        <th>Famille</th>
                        <th>Stock</th>
                        <th>Seuil</th>
                        <th>Valeur</th>
                        <th>Magasin</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr data-entry-id="{{ $item->id }}">
                            <td></td>
                            <td class="strong nowrap"><a href="{{ route('admin.stock-items.show', $item) }}">{{ $item->reference }}</a></td>
                            <td class="strong">
                                {{ $item->name }}
                                @if($item->perishable)<div class="muted"><i class="bi bi-hourglass-split"></i> Périssable</div>@endif
                            </td>
                            <td class="muted">{{ $item->category_label }}</td>
                            <td data-order="{{ (float) $item->quantity }}">
                                <div class="stock-level">
                                    <div class="read-bar"><span class="{{ $item->level === 'ok' ? '' : ($item->level === 'bas' ? 'warn' : 'low') }}" style="width: {{ $item->gauge }}%"></span></div>
                                    <strong>{{ \App\Support\Fmt::qty($item->quantity) }}</strong>&nbsp;<span class="muted">{{ $item->unit }}</span>
                                </div>
                                @if($item->level !== 'ok')<span class="pill pill-{{ $item->level_tone }}">{{ $item->level_label }}</span>@endif
                            </td>
                            <td class="muted">{{ \App\Support\Fmt::qty($item->min_quantity) }}</td>
                            <td class="nowrap" data-order="{{ (float) $item->stock_value }}">{{ \App\Support\Fmt::money($item->stock_value) }}</td>
                            <td class="muted">{{ $item->location->name ?? '—' }}</td>
                            <td class="nowrap">
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.stock-items.show', $item) }}" title="Ouvrir" aria-label="Ouvrir"><i class="bi bi-eye"></i></a>
                                @can('stock_movement_create')
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.stock-movements.create', ['type' => 'sortie', 'article' => $item->id]) }}" title="Sortie" aria-label="Sortie"><i class="bi bi-box-arrow-up"></i></a>
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
@can('stock_item_delete')
    dtButtons.push({
        text: '{{ trans('global.datatables.delete') }}',
        url: "{{ route('admin.stock-items.massDestroy') }}",
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
    $('.datatable-StockItem:not(.ajaxTable)').DataTable({ buttons: dtButtons, order: [[2, 'asc']], pageLength: 50 })
})
</script>
@endsection
