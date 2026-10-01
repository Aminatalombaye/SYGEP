@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb">
            <a href="{{ route('admin.assignments.index') }}">Affectations</a> ›
            <a href="{{ route('admin.assignments.show', $assignment) }}">{{ $assignment->reference }}</a> › Modifier
        </div>
        <h1>Modifier le bon {{ $assignment->reference }}</h1>
        <p class="sub">
            Bénéficiaire : {{ $assignment->beneficiary }}.
            Pour changer de bénéficiaire, restituez ou transférez le matériel.
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.assignments.update', $assignment) }}">
    @csrf
    @method('PUT')
    <section class="sy-card" style="max-width: 820px; margin-left: auto; margin-right: auto">
        <div class="sy-card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label for="assigned_at" class="required">Date d'affectation</label>
                    <input type="date" name="assigned_at" id="assigned_at" class="form-control {{ $errors->has('assigned_at') ? 'is-invalid' : '' }}"
                           value="{{ old('assigned_at', $assignment->assigned_at?->toDateString()) }}" required>
                    @error('assigned_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="expected_return_at">Retour prévu</label>
                    <input type="date" name="expected_return_at" id="expected_return_at" class="form-control {{ $errors->has('expected_return_at') ? 'is-invalid' : '' }}"
                           value="{{ old('expected_return_at', $assignment->expected_return_at?->toDateString()) }}">
                    @error('expected_return_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="location_id">Emplacement</label>
                    <select name="location_id" id="location_id" class="form-control select2">
                        <option value="">—</option>
                        @foreach($locations as $id => $name)
                            <option value="{{ $id }}" @selected((int) old('location_id', $assignment->location_id) === $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">Type d'affectation</label>
                    <select name="type" id="type" class="form-control">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', $assignment->type) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group full">
                    <label for="notes">Motif / observations</label>
                    <textarea name="notes" id="notes" rows="4" class="form-control" style="min-height:0">{{ old('notes', $assignment->notes) }}</textarea>
                </div>
            </div>
        </div>
    </section>

    <div class="page-actions" style="max-width: 820px; margin: 0 auto; justify-content:flex-end">
        <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-default">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Enregistrer</button>
    </div>
</form>
@endsection
