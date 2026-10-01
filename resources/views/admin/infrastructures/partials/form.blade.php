<div class="form-group full-row">
    <label class="required" for="name">Nom</label>
    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $infrastructure->name) }}" required placeholder="Ex. : Centre de formation professionnelle de Thiès">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label class="required" for="nature">Nature</label>
    <select class="form-control" name="nature" id="nature" required>
        @foreach(\App\Models\Infrastructure::NATURES as $key => $label)
            <option value="{{ $key }}" @selected(old('nature', $infrastructure->nature) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="parent_id">Rattachée à</label>
    <select class="form-control select2 {{ $errors->has('parent_id') ? 'is-invalid' : '' }}" name="parent_id" id="parent_id">
        <option value="">— Aucune (site principal) —</option>
        @foreach($parents as $id => $name)
            <option value="{{ $id }}" @selected((int) old('parent_id', $infrastructure->parent_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
    <span class="hint">Un bloc appartient à un bâtiment, un bâtiment à une structure.</span>
    @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="type">Usage</label>
    <input class="form-control" type="text" name="type" id="type" value="{{ old('type', $infrastructure->type) }}" placeholder="Ex. : Salle de formation, bureaux, atelier…">
</div>

<div class="form-group">
    <label for="location">Localisation</label>
    <input class="form-control" type="text" name="location" id="location" value="{{ old('location', $infrastructure->location) }}" placeholder="Ville, quartier">
</div>

<div class="form-group">
    <label class="required" for="status">Situation</label>
    <select class="form-control" name="status" id="status" required>
        @foreach(\App\Models\Infrastructure::STATUSES as $key => $label)
            <option value="{{ $key }}" @selected(old('status', $infrastructure->status) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="condition">État constaté</label>
    <select class="form-control" name="condition" id="condition">
        <option value="">Non évalué</option>
        @foreach(\App\Models\Infrastructure::CONDITIONS as $key => $label)
            <option value="{{ $key }}" @selected(old('condition', $infrastructure->condition) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="last_inspection_at">Date de la dernière visite</label>
    <input class="form-control {{ $errors->has('last_inspection_at') ? 'is-invalid' : '' }}" type="date" name="last_inspection_at" id="last_inspection_at" value="{{ old('last_inspection_at', $infrastructure->last_inspection_at?->toDateString()) }}">
    @error('last_inspection_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="surface">Surface (m²)</label>
    <input class="form-control" type="number" min="0" step="0.01" name="surface" id="surface" value="{{ old('surface', $infrastructure->surface) }}">
</div>

<div class="form-group full-row">
    <label>Amortissement</label>
    <span class="hint" style="display:block; margin: -2px 0 10px">Amortissement linéaire à partir de la date de mise en service. Laissez vide si l'infrastructure n'est pas valorisée.</span>
    <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr))">
        <div class="form-group">
            <label for="construction_date">Mise en service</label>
            <input class="form-control" type="date" name="construction_date" id="construction_date" value="{{ old('construction_date', $infrastructure->construction_date?->toDateString()) }}">
        </div>
        <div class="form-group">
            <label for="acquisition_value">Valeur d'origine (FCFA)</label>
            <input class="form-control {{ $errors->has('acquisition_value') ? 'is-invalid' : '' }}" type="number" min="0" step="1" name="acquisition_value" id="acquisition_value" value="{{ old('acquisition_value', $infrastructure->acquisition_value !== null ? (int) $infrastructure->acquisition_value : null) }}">
            @error('acquisition_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="depreciation_years">Durée (années)</label>
            <input class="form-control {{ $errors->has('depreciation_years') ? 'is-invalid' : '' }}" type="number" min="1" max="100" name="depreciation_years" id="depreciation_years" value="{{ old('depreciation_years', $infrastructure->depreciation_years) }}" placeholder="Ex. : 20">
            @error('depreciation_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group full-row">
    <label for="description">Description</label>
    <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $infrastructure->description) }}</textarea>
</div>
