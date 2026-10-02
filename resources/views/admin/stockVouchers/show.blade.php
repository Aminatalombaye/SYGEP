@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.stock-vouchers.index') }}">Bons de stock</a> › {{ $voucher->reference }}</div>
        <h1>{{ $voucher->type_label }} {{ $voucher->reference }}</h1>
        <p class="sub">Établi le {{ $voucher->moved_at->format('d/m/Y') }}{{ $voucher->createdBy ? ' par '.$voucher->createdBy->name : '' }}.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-vouchers.index') }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> Retour</a>
        <a href="{{ route('admin.stock-vouchers.print', $voucher) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimer le bon</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr><th style="width: 220px">Numéro</th><td>{{ $voucher->reference }}</td></tr>
                <tr><th>Type</th><td>{{ $voucher->type_label }}</td></tr>
                <tr><th>Date</th><td>{{ $voucher->moved_at->format('d/m/Y') }}</td></tr>
                <tr>
                    <th>{{ $voucher->type === 'entree' ? 'Fournisseur' : 'Bénéficiaire' }}</th>
                    <td>{{ $voucher->party }}</td>
                </tr>
                <tr><th>N° de pièce</th><td>{{ $voucher->document ?: '—' }}</td></tr>
                @if($voucher->notes)<tr><th>Observations</th><td>{{ $voucher->notes }}</td></tr>@endif
            </tbody>
        </table>

        <h5 style="margin: 22px 0 10px">Articles ({{ $voucher->movements->count() }})</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th><th>Article</th><th>Quantité</th>
                        @if($voucher->type === 'entree')<th>Prix unitaire</th><th>Péremption</th>@endif
                        <th>Solde après</th><th>Mouvement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($voucher->movements as $i => $m)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="strong">@if($m->item)<a href="{{ route('admin.stock-items.show', $m->item) }}">{{ $m->item->name }}</a>@else — @endif</td>
                            <td>{{ \App\Support\Fmt::qty(abs($m->delta)) }} {{ $m->item->unit ?? '' }}</td>
                            @if($voucher->type === 'entree')
                                <td>{{ $m->unit_price !== null ? number_format((float) $m->unit_price, 0, ',', ' ').' FCFA' : '—' }}</td>
                                <td>{{ $m->expires_at?->format('d/m/Y') ?? '—' }}</td>
                            @endif
                            <td>{{ \App\Support\Fmt::qty($m->balance_after) }}</td>
                            <td class="muted">{{ $m->reference }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
