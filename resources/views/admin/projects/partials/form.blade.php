@php
    $selectedInfra = old('infrastructures', $project->relationLoaded('infrastructures') ? $project->infrastructures->pluck('id')->all() : []);
    $selectedChefs = old('chef_projets', $project->relationLoaded('chef_projets') ? $project->chef_projets->pluck('id')->all() : []);
    $autoProgress = $project->exists && $project->relationLoaded('milestones') && $project->milestones->isNotEmpty();
@endphp

<div class="form-group full-row">
    <label class="required" for="name">Intitulé du projet</label>
    <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name"
           value="{{ old('name', $project->name) }}" required placeholder="Ex. : Construction du centre de formation de Kaffrine">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label class="required" for="type">Nature des travaux</label>
    <select class="form-control" name="type" id="type" required>
        @foreach(\App\Models\Project::TYPES as $key => $label)
            <option value="{{ $key }}" @selected(old('type', $project->type) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="required" for="status">Statut</label>
    <select class="form-control" name="status" id="status" required>
        @foreach(\App\Models\Project::STATUSES as $key => $label)
            <option value="{{ $key }}" @selected(old('status', $project->status) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="start_date">Date de démarrage</label>
    <input class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date?->toDateString()) }}">
    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <span class="hint">Obligatoire dès que le projet est en cours, suspendu ou terminé.</span>
</div>

<div class="form-group">
    <label for="end_date">Date de fin prévue</label>
    <input class="form-control {{ $errors->has('end_date') ? 'is-invalid' : '' }}" type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date?->toDateString()) }}">
    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="budget">Budget prévu (FCFA)</label>
    <input class="form-control {{ $errors->has('budget') ? 'is-invalid' : '' }}" type="number" min="0" step="1" name="budget" id="budget" value="{{ old('budget', $project->budget !== null ? (int) $project->budget : null) }}">
    @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="spent">Montant engagé / décaissé (FCFA)</label>
    <input class="form-control {{ $errors->has('spent') ? 'is-invalid' : '' }}" type="number" min="0" step="1" name="spent" id="spent" value="{{ old('spent', $project->spent !== null ? (int) $project->spent : null) }}">
    @error('spent')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="progress">Avancement physique (%)</label>
    @if($autoProgress)
        <input class="form-control" type="text" value="{{ $project->progress }} % — calculé à partir des jalons" disabled>
        <span class="hint">Cochez les jalons atteints sur la fiche du projet pour faire évoluer l'avancement.</span>
    @else
        <input class="form-control" type="number" min="0" max="100" name="progress" id="progress" value="{{ old('progress', $project->progress ?? 0) }}">
        <span class="hint">Calculé automatiquement dès que vous ajoutez des jalons.</span>
    @endif
</div>

<div class="form-group">
    <label for="chef_projets">Chef(s) de projet</label>
    <select class="form-control select2" name="chef_projets[]" id="chef_projets" multiple>
        @foreach($chef_projets as $id => $name)
            <option value="{{ $id }}" @selected(in_array($id, $selectedChefs))>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group full-row">
    <label for="infrastructures">Infrastructure(s) concernée(s)</label>
    <select class="form-control select2" name="infrastructures[]" id="infrastructures" multiple>
        @foreach($infrastructures as $id => $name)
            <option value="{{ $id }}" @selected(in_array($id, $selectedInfra))>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group full-row">
    <label for="description">Description / objectifs</label>
    <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $project->description) }}</textarea>
</div>
