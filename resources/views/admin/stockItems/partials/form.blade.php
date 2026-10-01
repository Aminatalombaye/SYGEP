<div class="form-group full-row">
    <label class="required" for="name">Désignation</label>
    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $item->name) }}" required placeholder="Ex. : Ramette papier A4 80 g, Toner HP 85A…">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="category">Famille</label>
    <select class="form-control" name="category" id="category">
        <option value="">—</option>
        @foreach(\App\Models\StockItem::CATEGORIES as $key => $label)
            <option value="{{ $key }}" @selected(old('category', $item->category) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="required" for="unit">Unité de gestion</label>
    <input class="form-control" type="text" name="unit" id="unit" list="units" value="{{ old('unit', $item->unit) }}" required>
    <datalist id="units">
        @foreach(\App\Models\StockItem::UNITS as $unit)<option value="{{ $unit }}">@endforeach
    </datalist>
</div>

@unless($item->exists)
    <div class="form-group">
        <label for="initial_quantity">Quantité en stock aujourd'hui</label>
        <input class="form-control" type="number" min="0" step="0.01" name="initial_quantity" id="initial_quantity" value="{{ old('initial_quantity', 0) }}">
        <span class="hint">Enregistrée comme première entrée de stock.</span>
    </div>
@endunless

<div class="form-group">
    <label class="required" for="min_quantity">Seuil d'alerte</label>
    <input class="form-control {{ $errors->has('min_quantity') ? 'is-invalid' : '' }}" type="number" min="0" step="0.01" name="min_quantity" id="min_quantity" value="{{ old('min_quantity', (float) $item->min_quantity) }}" required>
    <span class="hint">Une alerte est envoyée quand le stock descend à ce niveau.</span>
    @error('min_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="unit_price">Prix unitaire (FCFA)</label>
    <input class="form-control" type="number" min="0" step="1" name="unit_price" id="unit_price" value="{{ old('unit_price', $item->unit_price !== null ? (int) $item->unit_price : null) }}">
</div>

<div class="form-group">
    <label for="supplier_id">Fournisseur habituel</label>
    <select class="form-control select2" name="supplier_id" id="supplier_id">
        <option value="">—</option>
        @foreach($suppliers as $id => $name)
            <option value="{{ $id }}" @selected((int) old('supplier_id', $item->supplier_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="location_id">Magasin / lieu de stockage</label>
    <select class="form-control select2" name="location_id" id="location_id">
        <option value="">—</option>
        @foreach($locations as $id => $name)
            <option value="{{ $id }}" @selected((int) old('location_id', $item->location_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group full-row">
    <label class="perm-other" style="font-weight: 500">
        <input type="hidden" name="perishable" value="0">
        <input type="checkbox" name="perishable" value="1" @checked(old('perishable', $item->perishable))> Matière périssable (suivre les dates de péremption)
    </label>
</div>

<div class="form-group full-row">
    <label for="notes">Observations</label>
    <textarea class="form-control" name="notes" id="notes" rows="2">{{ old('notes', $item->notes) }}</textarea>
</div>
