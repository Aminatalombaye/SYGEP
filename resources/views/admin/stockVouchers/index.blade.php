@extends('layouts.admin')

@section('content')
@php
    $tabs = ['' => 'Tous'] + \App\Models\StockVoucher::TYPES;
@endphp

<div class="page-head">
    <div>
        <h1>Bons de stock</h1>
        <p class="sub">Bons d'entrée et de sortie des consommables, du {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-default"><i class="bi bi-arrow-left-right"></i> Mouvements</a>
        @can('stock_movement_create')
            @unless(\App\Support\Perimetre::isLocal())
                <a href="{{ route('admin.stock-vouchers.create', ['type' => 'entree']) }}" class="btn btn-default"><i class="bi bi-box-arrow-in-down"></i> Bon d'entrée</a>
            @endunless
            <a href="{{ route('admin.stock-vouchers.create', ['type' => 'sortie']) }}" class="btn btn-primary"><i class="bi bi-box-arrow-up"></i> Bon de sortie</a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-header">
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.stock-vouchers.index', ['type' => $key ?: null, 'du' => $from->toDateString(), 'au' => $to->toDateString()]) }}" class="btn btn-xs {{ (string) $type === (string) $key ? 'btn-primary' : 'btn-default' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-StockVoucher">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Date</th>
                        <th>Numéro</th>
                        <th>Type</th>
                        <th>Fournisseur / bénéficiaire</th>
                        <th>Articles</th>
                        <th>Pièce</th>
                        <th>Saisi par</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vouchers as $v)
                        <tr>
                            <td></td>
                            <td class="nowrap" data-order="{{ $v->moved_at->toDateString() }}{{ str_pad($v->id, 8, '0', STR_PAD_LEFT) }}">{{ $v->moved_at->format('d/m/Y') }}</td>
                            <td class="strong nowrap">{{ $v->reference }}</td>
                            <td><span class="pill pill-{{ $v->type === 'entree' ? 'good' : 'info' }}">{{ $v->type_label }}</span></td>
                            <td class="muted">{{ $v->party }}</td>
                            <td>{{ $v->movements_count }}</td>
                            <td class="muted">{{ $v->document ?: '—' }}</td>
                            <td class="muted">{{ $v->createdBy->name ?? '—' }}</td>
                            <td>
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.stock-vouchers.show', $v) }}" title="Voir" aria-label="Voir"><i class="bi bi-eye"></i></a>
                                <a class="btn btn-xs btn-icon" href="{{ route('admin.stock-vouchers.print', $v) }}" target="_blank" title="Imprimer le bon" aria-label="Imprimer le bon"><i class="bi bi-printer"></i></a>
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
        $.extend(true, $.fn.dataTable.defaults, { orderCellsTop: true, order: [[ 1, 'desc' ]], pageLength: 50 });
        $('.datatable-StockVoucher:not(.ajaxTable)').DataTable({ buttons: $.extend(true, [], $.fn.dataTable.defaults.buttons) });
    });
</script>
@endsection
