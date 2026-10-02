{{--
    Champs d'un mouvement de stock.
    $type : type présélectionné ; $fixedItem : article imposé (fiche article) ; $items : liste des articles sinon.
--}}
@php
    $current = old('type', $type ?? 'sortie');
    $canAdjust = auth()->user()->can('stock_movement_adjust');
    $local = \App\Support\Perimetre::isLocal();
    if ($local) { $current = 'sortie'; }
@endphp

<div class="move-switch" role="radiogroup" aria-label="Type de mouvement">
    @unless($local)
        <label><input type="radio" name="type" value="entree" @checked($current === 'entree')><span><i class="bi bi-box-arrow-in-down"></i> Entrée</span></label>
    @endunless
    <label><input type="radio" name="type" value="sortie" @checked($current === 'sortie')><span><i class="bi bi-box-arrow-up"></i> Sortie</span></label>
    @if($canAdjust && ! $local)
        <label><input type="radio" name="type" value="ajustement" @checked($current === 'ajustement')><span><i class="bi bi-sliders"></i> Ajustement</span></label>
    @endif
</div>

<div class="form-grid">
    @if(isset($fixedItem))
        <input type="hidden" name="stock_item_id" value="{{ $fixedItem->id }}">
        <input type="hidden" name="retour" value="article">
    @else
        <div class="form-group full">
            <label for="stock_item_id" class="required">Article</label>
            <select name="stock_item_id" id="stock_item_id" class="form-control select2 {{ $errors->has('stock_item_id') ? 'is-invalid' : '' }}" required>
                <option value="">—</option>
                @foreach($items as $it)
                    <option value="{{ $it->id }}" data-unit="{{ $it->unit }}" @selected((int) old('stock_item_id', $selected ?? null) === $it->id)>
                        {{ $it->name }} — {{ \App\Support\Fmt::qty($it->quantity) }} {{ $it->unit }} en stock
                    </option>
                @endforeach
            </select>
            @error('stock_item_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    @endif

    <div class="form-group">
        <label for="quantity" class="required" data-qty-label>
            {{ $current === 'ajustement' ? 'Quantité comptée' : 'Quantité' }}
        </label>
        <input type="number" min="0" step="0.01" name="quantity" id="quantity" class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}" value="{{ old('quantity') }}" required>
        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <span class="hint" data-show="ajustement" @if($current !== 'ajustement') hidden @endif>Saisissez la quantité réellement présente : l'écart est calculé.</span>
    </div>

    <div class="form-group">
        <label for="moved_at" class="required">Date</label>
        <input type="date" name="moved_at" id="moved_at" class="form-control {{ $errors->has('moved_at') ? 'is-invalid' : '' }}" value="{{ old('moved_at', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required>
        @error('moved_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Entrée --}}
    <div class="form-group" data-show="entree" @if($current !== 'entree') hidden @endif>
        <label for="supplier_id">Fournisseur</label>
        <select name="supplier_id" id="supplier_id" class="form-control select2">
            <option value="">—</option>
            @foreach($suppliers as $id => $name)
                <option value="{{ $id }}" @selected((int) old('supplier_id', isset($fixedItem) ? $fixedItem->supplier_id : null) === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group" data-show="entree" @if($current !== 'entree') hidden @endif>
        <label for="unit_price">Prix unitaire (FCFA)</label>
        <input type="number" min="0" step="1" name="unit_price" id="unit_price" class="form-control" value="{{ old('unit_price') }}">
    </div>
    <div class="form-group" data-show="entree" @if($current !== 'entree') hidden @endif>
        <label for="expires_at">Date de péremption</label>
        <input type="date" name="expires_at" id="expires_at" class="form-control {{ $errors->has('expires_at') ? 'is-invalid' : '' }}" value="{{ old('expires_at') }}">
        @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    {{-- Sortie --}}
    <div class="form-group" data-show="sortie" @if($current !== 'sortie') hidden @endif>
        <label for="service_id" class="required">Service bénéficiaire</label>
        <select name="service_id" id="service_id" class="form-control select2 {{ $errors->has('service_id') ? 'is-invalid' : '' }}">
            <option value="">—</option>
            @foreach($services as $id => $name)
                <option value="{{ $id }}" @selected((int) old('service_id') === $id)>{{ $name }}</option>
            @endforeach
        </select>
        @error('service_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <span class="hint">Ou choisissez seulement l'agent demandeur : son service est alors repris.</span>
    </div>
    <div class="form-group" data-show="sortie" @if($current !== 'sortie') hidden @endif>
        <label for="agent_id">Agent demandeur</label>
        <select name="agent_id" id="agent_id" class="form-control select2">
            <option value="">—</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected((int) old('agent_id') === $agent->id)>{{ $agent->full_name }}{{ $agent->service ? ' — '.$agent->service->name : '' }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group" data-show="entree sortie" @if($current === 'ajustement') hidden @endif>
        <label for="document">N° de pièce</label>
        <input type="text" name="document" id="document" class="form-control" value="{{ old('document') }}" placeholder="Bon de livraison, bon de sortie…">
    </div>

    <div class="form-group full">
        <label for="notes" data-notes-label @class(['required' => $current === 'ajustement'])>Observations</label>
        <textarea name="notes" id="notes" rows="2" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}" style="min-height: 0">{{ old('notes') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

@push('movement-js')
<script>
$(function () {
    function sync() {
        var type = $('input[name="type"]:checked').val();
        $('[data-show]').each(function () {
            this.hidden = this.getAttribute('data-show').split(' ').indexOf(type) === -1;
        });
        $('[data-qty-label]').text(type === 'ajustement' ? 'Quantité comptée' : 'Quantité');
        $('[data-notes-label]').toggleClass('required', type === 'ajustement');
    }
    $('input[name="type"]').on('change', sync);
    sync();
});
</script>
@endpush
