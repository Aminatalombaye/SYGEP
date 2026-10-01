<div class="form-grid">
    <div class="form-group">
        <label for="agent_id">Agent bénéficiaire</label>
        <select name="agent_id" id="agent_id" class="form-control select2 {{ $errors->has('agent_id') ? 'is-invalid' : '' }}">
            <option value="">— Aucun agent (affectation à un service) —</option>
            @foreach($agents as $agent)
                <option value="{{ $agent->id }}" data-service="{{ $agent->service_id }}" @selected((int) old('agent_id', $selectedAgent ?? null) === $agent->id)>
                    {{ $agent->full_name }}{{ $agent->service ? ' — '.$agent->service->name : '' }}
                </option>
            @endforeach
        </select>
        @error('agent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="service_id">Service</label>
        <select name="service_id" id="service_id" class="form-control select2 {{ $errors->has('service_id') ? 'is-invalid' : '' }}">
            <option value="">— Service de l'agent —</option>
            @foreach($services as $id => $name)
                <option value="{{ $id }}" @selected((int) old('service_id', $selectedService ?? null) === $id)>{{ $name }}</option>
            @endforeach
        </select>
        <div class="hint">Rempli automatiquement avec le service de l'agent.</div>
        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="location_id">Emplacement</label>
        <select name="location_id" id="location_id" class="form-control select2">
            <option value="">— Inchangé —</option>
            @foreach($locations as $id => $name)
                <option value="{{ $id }}" @selected((int) old('location_id') === $id)>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
            <label for="type">Type d'affectation</label>
            <select name="type" id="type" class="form-control">
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(old('type', \App\Models\Assignment::TYPE_DEFAULT) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

    <div class="form-group">
        <label for="assigned_at" class="required">Date d'affectation</label>
        <input type="date" name="assigned_at" id="assigned_at" class="form-control {{ $errors->has('assigned_at') ? 'is-invalid' : '' }}"
               value="{{ old('assigned_at', now()->toDateString()) }}" required>
        @error('assigned_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="expected_return_at">Retour prévu</label>
        <input type="date" name="expected_return_at" id="expected_return_at" class="form-control {{ $errors->has('expected_return_at') ? 'is-invalid' : '' }}"
               value="{{ old('expected_return_at') }}">
        <div class="hint">Laisser vide pour une dotation sans date de retour.</div>
        @error('expected_return_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group full">
        <label for="notes">Motif / observations</label>
        <textarea name="notes" id="notes" rows="3" class="form-control" style="min-height:0">{{ old('notes') }}</textarea>
    </div>
</div>

@push('beneficiary-js')
<script>
$(function () {
    var $agent = $('#agent_id'), $service = $('#service_id');
    $agent.on('change', function () {
        var serviceId = $agent.find('option:selected').data('service');
        if (serviceId) { $service.val(String(serviceId)).trigger('change'); }
    });
    if ($agent.val() && !$service.val()) { $agent.trigger('change'); }
});
</script>
@endpush
