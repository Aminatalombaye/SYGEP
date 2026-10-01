@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb">
            <a href="{{ route('admin.assignments.index') }}">Affectations</a> ›
            <a href="{{ route('admin.assignments.show', $assignment) }}">{{ $assignment->reference }}</a> › Restitution
        </div>
        <h1>Restitution du matériel</h1>
        <p class="sub">Bon {{ $assignment->reference }} — {{ $assignment->beneficiary }}</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.assignments.return.store', $assignment) }}">
    @csrf
    <div class="sy-grid-2">
        <section class="sy-card">
            <div class="sy-card-head">
                <div>
                    <h2>Matières rendues</h2>
                    <p>Décochez celles qui restent chez le bénéficiaire (restitution partielle).</p>
                </div>
            </div>
            <div class="sy-card-body">
                @error('assets')<div class="alert alert-danger">{{ $message }}</div>@enderror
                <div class="asset-picker" data-picker>
                    <div class="asset-picker-list">
                        @foreach($outstanding as $asset)
                            @php($checked = in_array($asset->id, array_map('intval', (array) old('assets', $outstanding->pluck('id')->all())), true))
                            <label class="asset-row {{ $checked ? 'is-checked' : '' }}">
                                <input type="checkbox" name="assets[]" value="{{ $asset->id }}" @checked($checked)>
                                <span class="asset-main">
                                    <span class="asset-name">{{ $asset->name ?: 'Sans nom' }}</span>
                                    <span class="asset-meta">
                                        {{ $asset->category->name ?? 'Sans catégorie' }}
                                        @if($asset->serial_number) · N° {{ $asset->serial_number }}@endif
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="asset-picker-foot"><span data-picker-count>0</span> / {{ $outstanding->count() }} sélectionnée(s)</div>
                </div>
            </div>
        </section>

        <div>
            <section class="sy-card">
                <div class="sy-card-head"><h2>Retour</h2></div>
                <div class="sy-card-body">
                    <div class="form-group">
                        <label for="returned_at" class="required">Date de retour</label>
                        <input type="date" name="returned_at" id="returned_at" max="{{ now()->toDateString() }}"
                               class="form-control {{ $errors->has('returned_at') ? 'is-invalid' : '' }}"
                               value="{{ old('returned_at', now()->toDateString()) }}" required>
                        @error('returned_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label for="condition" class="required">État constaté</label>
                        <select name="condition" id="condition" class="form-control" required>
                            @foreach($conditions as $key => $label)
                                <option value="{{ $key }}" @selected(old('condition', 'bon') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="hint">« Endommagé » ou « Hors service » passe les matières en panne ; sinon elles redeviennent disponibles.</div>
                    </div>
                    <div class="form-group">
                        <label for="location_id">Emplacement de rangement</label>
                        <select name="location_id" id="location_id" class="form-control select2">
                            <option value="">— Inchangé —</option>
                            @foreach($locations as $id => $name)
                                <option value="{{ $id }}" @selected((int) old('location_id') === $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label for="notes">Observations</label>
                        <input type="text" name="notes" id="notes" maxlength="255" class="form-control" value="{{ old('notes') }}">
                    </div>
                </div>
            </section>

            <div class="page-actions" style="justify-content:flex-end">
                <a href="{{ route('admin.assignments.show', $assignment) }}" class="btn btn-default">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-box-arrow-in-down"></i> Valider la restitution</button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var picker = document.querySelector('[data-picker]');
    if (!picker) return;
    var rows = picker.querySelectorAll('.asset-row');
    var counter = picker.querySelector('[data-picker-count]');
    function count() {
        var n = 0;
        rows.forEach(function (row) {
            var cb = row.querySelector('input');
            row.classList.toggle('is-checked', cb.checked);
            if (cb.checked) n++;
        });
        counter.textContent = n;
    }
    picker.addEventListener('change', count);
    count();
})();
</script>
@endsection
