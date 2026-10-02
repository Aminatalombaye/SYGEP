@extends('layouts.admin')

@section('content')
@php
    $isEntry = old('type', $type) === 'entree';
    $local = \App\Support\Perimetre::isLocal();
    $oldLines = old('lines', [['stock_item_id' => '', 'quantity' => '', 'unit_price' => '', 'expires_at' => '']]);
@endphp

<div class="page-head form-page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.stock-vouchers.index') }}">Bons de stock</a> › Nouveau</div>
        <h1>Nouveau bon de stock</h1>
        <p class="sub">Un bon regroupe plusieurs articles : chaque ligne met à jour le stock et l'historique.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-vouchers.index') }}" class="btn btn-default"><i class="bi bi-x-lg"></i> Annuler</a>
    </div>
</div>

<div class="card sy-form">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.stock-vouchers.store') }}" id="voucher-form">
            @csrf
            <div class="move-switch" role="radiogroup" aria-label="Type de bon">
                @unless($local)
                    <label><input type="radio" name="type" value="entree" @checked($isEntry)><span><i class="bi bi-box-arrow-in-down"></i> Bon d'entrée</span></label>
                @endunless
                <label><input type="radio" name="type" value="sortie" @checked(! $isEntry || $local)><span><i class="bi bi-box-arrow-up"></i> Bon de sortie</span></label>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="required" for="moved_at">Date</label>
                    <input type="date" name="moved_at" id="moved_at" class="form-control {{ $errors->has('moved_at') ? 'is-invalid' : '' }}" value="{{ old('moved_at', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
                    @error('moved_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="document">N° de pièce</label>
                    <input type="text" name="document" id="document" class="form-control" value="{{ old('document') }}" placeholder="Bon de livraison du fournisseur, demande du service…">
                    <span class="hint">Le numéro du bon (BE- ou BS-) est attribué automatiquement.</span>
                </div>

                <div class="form-group" data-show="entree">
                    <label for="supplier_id">Fournisseur</label>
                    <select name="supplier_id" id="supplier_id" class="form-control select2">
                        <option value="">—</option>
                        @foreach($suppliers as $id => $name)
                            <option value="{{ $id }}" @selected((int) old('supplier_id') === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" data-show="sortie">
                    <label class="required" for="service_id">Service bénéficiaire</label>
                    <select name="service_id" id="service_id" class="form-control select2 {{ $errors->has('service_id') ? 'is-invalid' : '' }}">
                        <option value="">—</option>
                        @foreach($services as $id => $name)
                            <option value="{{ $id }}" @selected((int) old('service_id') === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('service_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <span class="hint">Ou choisissez seulement l'agent demandeur : son service est alors repris.</span>
                </div>
                <div class="form-group" data-show="sortie">
                    <label for="agent_id">Agent demandeur</label>
                    <select name="agent_id" id="agent_id" class="form-control select2">
                        <option value="">—</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" @selected((int) old('agent_id') === $agent->id)>{{ $agent->full_name }}{{ $agent->service ? ' — '.$agent->service->name : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h5 style="margin: 22px 0 10px">Articles</h5>
            @error('lines')<div class="alert alert-danger">{{ $message }}</div>@enderror
            <div class="table-responsive">
                <table class="table table-bordered" id="lines-table">
                    <thead>
                        <tr>
                            <th style="min-width: 280px">Article</th>
                            <th style="width: 130px">Quantité</th>
                            <th style="width: 90px">Unité</th>
                            <th style="width: 110px">En stock</th>
                            <th style="width: 140px" data-show="entree">Prix unitaire</th>
                            <th style="width: 170px" data-show="entree">Péremption</th>
                            <th style="width: 44px"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        @foreach($oldLines as $i => $line)
                            <tr data-line>
                                <td>
                                    <select name="lines[{{ $i }}][stock_item_id]" class="form-control line-item {{ $errors->has("lines.$i.stock_item_id") ? 'is-invalid' : '' }}" required>
                                        <option value="">— Choisir un article —</option>
                                        @foreach($items as $it)
                                            <option value="{{ $it->id }}" data-unit="{{ $it->unit }}" data-stock="{{ rtrim(rtrim((string) $it->quantity, '0'), '.') }}" data-price="{{ $it->unit_price !== null ? (int) $it->unit_price : '' }}" data-perishable="{{ $it->perishable ? 1 : 0 }}" @selected((int) ($line['stock_item_id'] ?? 0) === $it->id)>{{ $it->name }}</option>
                                        @endforeach
                                    </select>
                                    @error("lines.$i.stock_item_id")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0.01" name="lines[{{ $i }}][quantity]" class="form-control {{ $errors->has("lines.$i.quantity") ? 'is-invalid' : '' }}" value="{{ $line['quantity'] ?? '' }}" required>
                                    @error("lines.$i.quantity")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                                <td class="muted line-unit">—</td>
                                <td class="muted line-stock">—</td>
                                <td data-show="entree"><input type="number" step="1" min="0" name="lines[{{ $i }}][unit_price]" class="form-control line-price" value="{{ $line['unit_price'] ?? '' }}"></td>
                                <td data-show="entree">
                                    <input type="date" name="lines[{{ $i }}][expires_at]" class="form-control line-expiry {{ $errors->has("lines.$i.expires_at") ? 'is-invalid' : '' }}" value="{{ $line['expires_at'] ?? '' }}">
                                    @error("lines.$i.expires_at")<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                                <td><button type="button" class="btn btn-xs btn-icon btn-icon-danger line-remove" title="Retirer la ligne" aria-label="Retirer la ligne"><i class="bi bi-x-lg"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-default" id="add-line"><i class="bi bi-plus-lg"></i> Ajouter un article</button>

            <div class="form-group" style="margin-top: 20px">
                <label for="notes">Observations</label>
                <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
            </div>

            <div class="sy-form-actions">
                <a href="{{ route('admin.stock-vouchers.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer le bon</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var form = document.getElementById('voucher-form');
    var body = document.getElementById('lines-body');
    var next = {{ count($oldLines) }};

    function currentType() { return form.querySelector('input[name=type]:checked').value; }

    function applyType() {
        var type = currentType();
        form.querySelectorAll('[data-show]').forEach(function (el) {
            el.hidden = el.getAttribute('data-show').split(' ').indexOf(type) === -1;
        });
        body.querySelectorAll('tr[data-line]').forEach(refresh);
    }

    function refresh(row) {
        var select = row.querySelector('.line-item');
        var opt = select.options[select.selectedIndex];
        row.querySelector('.line-unit').textContent = opt && opt.value ? opt.dataset.unit : '—';
        row.querySelector('.line-stock').textContent = opt && opt.value ? opt.dataset.stock : '—';
        var expiry = row.querySelector('.line-expiry');
        expiry.required = currentType() === 'entree' && opt && opt.dataset.perishable === '1';
    }

    body.addEventListener('change', function (e) {
        if (e.target.classList.contains('line-item')) {
            var row = e.target.closest('tr');
            var opt = e.target.options[e.target.selectedIndex];
            var price = row.querySelector('.line-price');
            if (price && !price.value && opt && opt.dataset.price) { price.value = opt.dataset.price; }
            refresh(row);
        }
    });

    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.line-remove');
        if (!btn) return;
        if (body.querySelectorAll('tr[data-line]').length > 1) { btn.closest('tr').remove(); }
    });

    document.getElementById('add-line').addEventListener('click', function () {
        var first = body.querySelector('tr[data-line]');
        var row = first.cloneNode(true);
        row.querySelectorAll('input, select').forEach(function (el) {
            el.name = el.name.replace(/lines\[\d+\]/, 'lines[' + next + ']');
            if (el.tagName === 'SELECT') { el.selectedIndex = 0; } else { el.value = ''; }
            el.classList.remove('is-invalid');
        });
        row.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
        next++;
        body.appendChild(row);
        refresh(row);
        applyType();
    });

    form.querySelectorAll('input[name=type]').forEach(function (r) { r.addEventListener('change', applyType); });
    applyType();
})();
</script>
@endsection
