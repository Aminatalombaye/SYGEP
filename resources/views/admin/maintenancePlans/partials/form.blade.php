@php($target = old('target_type', $plan->target_type))
<div class="form-group full-row">
    <label class="required" for="title">Opération d'entretien</label>
    <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $plan->title) }}" required placeholder="Ex. : Révision des climatiseurs, contrôle des extincteurs…">
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label class="required" for="target_type">Concerne</label>
    <select class="form-control" name="target_type" id="target_type" required>
        <option value="infrastructure" @selected($target === 'infrastructure')>Bâtiment / infrastructure</option>
        <option value="asset" @selected($target === 'asset')>Matière / équipement</option>
    </select>
</div>

<div class="form-group" data-target="infrastructure" @if($target !== 'infrastructure') hidden @endif>
    <label class="required" for="infrastructure_id">Infrastructure</label>
    <select class="form-control select2" name="infrastructure_id" id="infrastructure_id">
        <option value="">—</option>
        @foreach($infrastructures as $id => $name)
            <option value="{{ $id }}" @selected((int) old('infrastructure_id', $plan->infrastructure_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
    @error('infrastructure_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group" data-target="asset" @if($target !== 'asset') hidden @endif>
    <label class="required" for="asset_id">Matière</label>
    <select class="form-control select2" name="asset_id" id="asset_id">
        <option value="">—</option>
        @foreach($assets as $id => $name)
            <option value="{{ $id }}" @selected((int) old('asset_id', $plan->asset_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
    @error('asset_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label class="required" for="frequency_months">Périodicité</label>
    <select class="form-control" name="frequency_months" id="frequency_months" required>
        @foreach(\App\Models\MaintenancePlan::FREQUENCIES as $months => $label)
            <option value="{{ $months }}" @selected((int) old('frequency_months', $plan->frequency_months) === $months)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="required" for="next_due_at">Prochaine échéance</label>
    <input class="form-control" type="date" name="next_due_at" id="next_due_at" value="{{ old('next_due_at', $plan->next_due_at?->toDateString()) }}" required>
</div>

<div class="form-group">
    <label class="required" for="lead_days">Créer la demande … jours avant</label>
    <input class="form-control" type="number" min="0" max="90" name="lead_days" id="lead_days" value="{{ old('lead_days', $plan->lead_days) }}" required>
    <span class="hint">La demande est créée automatiquement avec l'avis technique, puis soumise à l'approbation du Directeur.</span>
</div>

<div class="form-group">
    <label for="responsible_id">Responsable</label>
    <select class="form-control select2" name="responsible_id" id="responsible_id">
        <option value="">— Responsables de la maintenance —</option>
        @foreach($users as $id => $name)
            <option value="{{ $id }}" @selected((int) old('responsible_id', $plan->responsible_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
</div>

<div class="form-group full-row">
    <label for="description">Points de contrôle</label>
    <textarea class="form-control" name="description" id="description" rows="3" placeholder="Liste des vérifications à effectuer…">{{ old('description', $plan->description) }}</textarea>
</div>

<div class="form-group full-row">
    <label class="perm-other" style="font-weight: 500">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" name="active" value="1" @checked(old('active', $plan->active))> Plan actif
    </label>
</div>

@push('form-js')
<script>
$(function () {
    var $type = $('#target_type');
    function toggle() { $('[data-target]').each(function () { this.hidden = this.getAttribute('data-target') !== $type.val(); }); }
    $type.on('change', toggle); toggle();
});
</script>
@endpush
