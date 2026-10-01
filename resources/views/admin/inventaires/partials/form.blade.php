@php($locked = $inventaire->exists && ! $inventaire->isDraft())
<div class="form-group full-row">
    <label class="required" for="nom">Nom de la campagne</label>
    <input class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}" type="text" name="nom" id="nom"
           value="{{ old('nom', $inventaire->nom) }}" required placeholder="Ex. : Inventaire annuel 2026 – Direction">
    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="starts_at">Début prévu</label>
    <input class="form-control" type="date" name="starts_at" id="starts_at" value="{{ old('starts_at', $inventaire->starts_at?->toDateString()) }}">
</div>
<div class="form-group">
    <label for="ends_at">Fin prévue</label>
    <input class="form-control {{ $errors->has('ends_at') ? 'is-invalid' : '' }}" type="date" name="ends_at" id="ends_at" value="{{ old('ends_at', $inventaire->ends_at?->toDateString()) }}">
    @error('ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group full-row">
    <label>Périmètre</label>
    <span class="help-block" style="margin: -2px 0 10px">
        @if($locked)
            Le périmètre est figé depuis le démarrage de la campagne.
        @else
            Laissez vide pour inventorier tout le parc. Les filtres se cumulent.
        @endif
    </span>
    <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0, 1fr))">
        <div class="form-group">
            <label for="location_id">Emplacement</label>
            <select class="form-control select2" name="location_id" id="location_id" @disabled($locked)>
                <option value="">Tous</option>
                @foreach($locations as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('location_id', $inventaire->location_id) === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="service_id">Service</label>
            <select class="form-control select2" name="service_id" id="service_id" @disabled($locked)>
                <option value="">Tous</option>
                @foreach($services as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('service_id', $inventaire->service_id) === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Catégorie</label>
            <select class="form-control select2" name="category_id" id="category_id" @disabled($locked)>
                <option value="">Toutes</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" @selected((int) old('category_id', $inventaire->category_id) === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="form-group full-row">
    <label for="notes">Consignes / observations</label>
    <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Équipe, organisation, points d'attention…">{{ old('notes', $inventaire->notes) }}</textarea>
</div>
