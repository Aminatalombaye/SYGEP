@extends('layouts.admin')

@section('content')
@php($F = \App\Support\Fmt::class)
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.stock-items.index') }}">{{ trans('cruds.stockItem.title') }}</a> › {{ $item->reference }}</div>
        <h1>
            {{ $item->name }}
            <span class="pill pill-{{ $item->level_tone }}" style="vertical-align: middle">{{ $item->level_label }}</span>
        </h1>
        <p class="sub">{{ $item->reference }} · {{ $item->category_label }}{{ $item->location ? ' · '.$item->location->name : '' }}</p>
    </div>
    <div class="page-actions">
        @can('stock_item_edit')
            <a href="{{ route('admin.stock-items.edit', $item) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
    </div>
</div>

<div class="kpi-grid kpi-auto" style="--kpi-cols: 4">
    <div class="kpi kpi-static kpi-{{ $item->level_tone }}">
        <span class="kpi-icon"><i class="bi bi-box2"></i></span>
        <span class="kpi-body">
            <span class="kpi-value">{{ $F::qty($item->quantity) }}</span>
            <span class="kpi-label">{{ $item->unit }}(s) en stock</span>
            <span class="kpi-hint">Seuil d'alerte : {{ $F::qty($item->min_quantity) }}</span>
        </span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-arrow-left-right"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">+{{ $F::qty($stats['in_month']) }} / −{{ $F::qty($stats['out_month']) }}</span>
            <span class="kpi-label">Entrées / sorties ce mois</span>
        </span>
    </div>
    <div class="kpi kpi-static {{ $stats['coverage'] !== null && $stats['coverage'] < 30 ? 'kpi-warning' : '' }}">
        <span class="kpi-icon"><i class="bi bi-calendar-range"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ $stats['coverage'] !== null ? $stats['coverage'].' jours' : '—' }}</span>
            <span class="kpi-label">Couverture estimée</span>
            <span class="kpi-hint">Consommation moyenne : {{ $F::qty($stats['avg_month']) }} / mois</span>
        </span>
    </div>
    <div class="kpi kpi-static">
        <span class="kpi-icon"><i class="bi bi-cash-coin"></i></span>
        <span class="kpi-body">
            <span class="kpi-value" style="font-size: 20px">{{ $F::money($item->stock_value) }}</span>
            <span class="kpi-label">Valeur du stock</span>
            <span class="kpi-hint">Prix unitaire : {{ $F::money($item->unit_price) }}</span>
        </span>
    </div>
</div>

@if($expiring->isNotEmpty())
    <div class="alert alert-warning">
        <i class="bi bi-hourglass-split"></i>
        Lots arrivant à péremption :
        @foreach($expiring as $lot)
            {{ $F::qty($lot->quantity) }} {{ $item->unit }} le {{ $lot->expires_at->format('d/m/Y') }}{{ $lot->expires_at->isPast() ? ' (périmé)' : '' }}@if(! $loop->last), @endif
        @endforeach
    </div>
@endif

<div class="sy-grid-2">
    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Mouvements</h2>
                <a href="{{ route('admin.stock-movements.index', ['article' => $item->id, 'du' => now()->subYear()->toDateString()]) }}" class="btn btn-default btn-sm">Journal complet</a>
            </div>
            <div class="sy-card-body flush">
                @if($movements->isEmpty())
                    <div class="empty-state"><i class="bi bi-arrow-left-right"></i> Aucun mouvement.</div>
                @else
                    <div class="table-responsive" style="max-height: 520px">
                        <table class="dash-table">
                            <thead><tr><th>Date</th><th>Mouvement</th><th>Solde</th><th>Fournisseur / bénéficiaire</th></tr></thead>
                            <tbody>
                                @foreach($movements as $m)
                                    <tr>
                                        <td class="nowrap">{{ $m->moved_at->format('d/m/Y') }}<div class="muted">{{ $m->reference }}</div></td>
                                        <td class="nowrap">
                                            <span class="{{ $m->type === 'entree' ? 'qty-in' : ($m->type === 'sortie' ? 'qty-out' : 'qty-adj') }}">{{ $m->delta > 0 ? '+' : '' }}{{ $F::qty($m->delta) }}</span>
                                            <div class="muted">{{ $m->type_label }}</div>
                                        </td>
                                        <td>{{ $F::qty($m->balance_after) }}</td>
                                        <td class="muted">
                                            {{ $m->party }}
                                            @if($m->document)<div>Pièce : {{ $m->document }}</div>@endif
                                            @if($m->notes)<div>{{ $m->notes }}</div>@endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div>
        @can('stock_movement_create')
            <section class="sy-card">
                <div class="sy-card-head"><h2>Enregistrer un mouvement</h2></div>
                <div class="sy-card-body">
                    <form method="POST" action="{{ route('admin.stock-movements.store') }}">
                        @csrf
                        @include('admin.stockMovements.partials.fields', ['type' => 'sortie', 'fixedItem' => $item])
                        <div class="page-actions" style="justify-content: flex-end; margin-top: 12px">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
                        </div>
                    </form>
                </div>
            </section>
        @endcan

        <section class="sy-card">
            <div class="sy-card-head"><h2>Fiche article</h2></div>
            <div class="sy-card-body">
                <dl class="dl">
                    <dt>Référence</dt><dd>{{ $item->reference }}</dd>
                    <dt>Famille</dt><dd>{{ $item->category_label }}</dd>
                    <dt>Unité</dt><dd>{{ $item->unit }}</dd>
                    <dt>Fournisseur</dt><dd>{{ $item->supplier->name ?? '—' }}</dd>
                    <dt>Magasin</dt><dd>{{ $item->location->name ?? '—' }}</dd>
                    <dt>Périssable</dt><dd>{{ $item->perishable ? 'Oui' : 'Non' }}</dd>
                    @if($item->notes)<dt>Observations</dt><dd style="white-space: pre-line">{{ $item->notes }}</dd>@endif
                </dl>
            </div>
        </section>

        @can('stock_item_delete')
            <form action="{{ route('admin.stock-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer cet article et son historique ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer l'article</button>
            </form>
        @endcan
    </div>
</div>
@endsection

@section('scripts')
@parent
@stack('movement-js')
@endsection
