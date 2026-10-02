@extends('layouts.admin')

@section('content')
@php
    $tabs = ['' => 'Tous'] + \App\Models\StockMovement::TYPES;
@endphp

<div class="page-head">
    <div>
        <h1>{{ trans('cruds.stockMovement.title') }}</h1>
        <p class="sub">Journal des entrées, sorties et ajustements du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-items.index') }}" class="btn btn-default"><i class="bi bi-box2"></i> Articles</a>
        <a href="{{ route('admin.stock-vouchers.index') }}" class="btn btn-default"><i class="bi bi-receipt"></i> Bons de stock</a>
        @can('stock_movement_create')
            <a href="{{ route('admin.stock-movements.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau mouvement</a>
        @endcan
    </div>
</div>

<section class="sy-card report-form">
    <div class="sy-card-body">
        <form method="GET" action="{{ route('admin.stock-movements.index') }}" class="form-grid">
            <input type="hidden" name="type" value="{{ $type }}">
            <div class="form-group">
                <label for="du">Du</label>
                <input type="date" name="du" id="du" class="form-control" value="{{ $from->toDateString() }}">
            </div>
            <div class="form-group">
                <label for="au">Au</label>
                <input type="date" name="au" id="au" class="form-control" value="{{ $to->toDateString() }}">
            </div>
            <div class="form-group">
                <label for="article">Article</label>
                <select name="article" id="article" class="form-control select2">
                    <option value="">Tous</option>
                    @foreach($items as $id => $name)
                        <option value="{{ $id }}" @selected(request()->integer('article') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="service">Service</label>
                <select name="service" id="service" class="form-control select2">
                    <option value="">Tous</option>
                    @foreach($services as $id => $name)
                        <option value="{{ $id }}" @selected(request()->integer('service') === $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group full page-actions">
                <button type="submit" class="btn btn-default"><i class="bi bi-funnel"></i> Filtrer</button>
            </div>
        </form>
    </div>
</section>

<nav class="sy-tabs">
    @foreach($tabs as $key => $label)
        <a href="{{ route('admin.stock-movements.index', array_filter(['type' => $key, 'du' => $from->toDateString(), 'au' => $to->toDateString(), 'article' => request('article'), 'service' => request('service')])) }}" @class(['active' => (string) $type === (string) $key])>
            {{ $label }}
        </a>
    @endforeach
</nav>

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-StockMovement">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Article</th>
                        <th>Mouvement</th>
                        <th>Solde après</th>
                        <th>Fournisseur / bénéficiaire</th>
                        <th>Bon</th>
                        <th>Pièce</th>
                        <th>Saisi par</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $m)
                        <tr>
                            <td></td>
                            <td class="nowrap" data-order="{{ $m->moved_at->toDateString() }}{{ str_pad($m->id, 8, '0', STR_PAD_LEFT) }}">{{ $m->moved_at->format('d/m/Y') }}</td>
                            <td class="muted nowrap">{{ $m->reference }}</td>
                            <td class="strong">
                                @if($m->item)<a href="{{ route('admin.stock-items.show', $m->item) }}">{{ $m->item->name }}</a>@else — @endif
                            </td>
                            <td class="nowrap" data-order="{{ $m->delta }}">
                                <span class="{{ $m->type === 'entree' ? 'qty-in' : ($m->type === 'sortie' ? 'qty-out' : 'qty-adj') }}">
                                    {{ $m->delta > 0 ? '+' : '' }}{{ \App\Support\Fmt::qty($m->delta) }}
                                </span>
                                <span class="muted">{{ $m->item->unit ?? '' }} · {{ $m->type_label }}</span>
                            </td>
                            <td>{{ \App\Support\Fmt::qty($m->balance_after) }}</td>
                            <td class="muted">{{ $m->party }}</td>
                            <td class="nowrap">@if($m->voucher)<a href="{{ route('admin.stock-vouchers.show', $m->voucher) }}">{{ $m->voucher->reference }}</a>@else<span class="muted">—</span>@endif</td>
                            <td class="muted">{{ $m->document ?: '—' }}</td>
                            <td class="muted">{{ $m->user->name ?? '—' }}</td>
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
    $('.datatable-StockMovement:not(.ajaxTable)').DataTable({ buttons: $.extend(true, [], $.fn.dataTable.defaults.buttons), order: [[1, 'desc']], pageLength: 50 })
})
</script>
@endsection
