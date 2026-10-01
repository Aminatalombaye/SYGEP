@php($target = old('target_type', $maintenanceRequest->target_type))
<div class="form-group full-row">
    <label class="required" for="title">Objet de la demande</label>
    <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', $maintenanceRequest->title) }}" required placeholder="Ex. : Fuite d'eau dans la salle B2, imprimante en panne…">
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label class="required" for="kind">Type de maintenance</label>
    <select class="form-control" name="kind" id="kind" required>
        @foreach(\App\Models\MaintenanceRequest::KINDS as $key => $label)
            <option value="{{ $key }}" @selected(old('kind', $maintenanceRequest->kind) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="required" for="priority">Priorité</label>
    <select class="form-control" name="priority" id="priority" required>
        @foreach(\App\Models\MaintenanceRequest::PRIORITIES as $key => $label)
            <option value="{{ $key }}" @selected(old('priority', $maintenanceRequest->priority) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label class="required" for="target_type">Concerne</label>
    <select class="form-control" name="target_type" id="target_type" required>
        @foreach(\App\Models\MaintenanceRequest::TARGETS as $key => $label)
            <option value="{{ $key }}" @selected($target === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group" data-target="infrastructure" @if($target !== 'infrastructure') hidden @endif>
    <label class="required" for="infrastructure_id">Infrastructure</label>
    <select class="form-control select2 {{ $errors->has('infrastructure_id') ? 'is-invalid' : '' }}" name="infrastructure_id" id="infrastructure_id">
        <option value="">—</option>
        @foreach($infrastructures as $id => $name)
            <option value="{{ $id }}" @selected((int) old('infrastructure_id', $maintenanceRequest->infrastructure_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
    @error('infrastructure_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group" data-target="asset" @if($target !== 'asset') hidden @endif>
    <label class="required" for="asset_id">Matière / équipement</label>
    <select class="form-control select2 {{ $errors->has('asset_id') ? 'is-invalid' : '' }}" name="asset_id" id="asset_id">
        <option value="">—</option>
        @foreach($assets as $id => $name)
            <option value="{{ $id }}" @selected((int) old('asset_id', $maintenanceRequest->asset_id) === $id)>{{ $name }}</option>
        @endforeach
    </select>
    @error('asset_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="establishment">Établissement / bureau demandeur</label>
    <input class="form-control" type="text" name="establishment" id="establishment" value="{{ old('establishment', $maintenanceRequest->establishment) }}" placeholder="Ex. : CFP de Thiès, Direction de l'administration générale…">
</div>

<div class="form-group full-row">
    <label class="required" for="description">Description du problème</label>
    <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="4" required placeholder="Constat, localisation précise, depuis quand…">{{ old('description', $maintenanceRequest->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

@push('form-js')
<script>
$(function () {
    var $type = $('#target_type');
    function toggle() {
        $('[data-target]').each(function () {
            this.hidden = this.getAttribute('data-target') !== $type.val();
        });
    }
    $type.on('change', toggle);
    toggle();
});
</script>
@endpush
