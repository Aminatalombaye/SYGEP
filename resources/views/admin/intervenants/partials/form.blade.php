<div class="form-group">
    <label class="required" for="role">Type d'intervenant</label>
    <select class="form-control" name="role" id="role" required>
        @foreach(\App\Models\Intervenant::ROLES as $key => $label)
            <option value="{{ $key }}" @selected(old('role', $intervenant->role) === $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="organisation">Entreprise / organisme</label>
    <input class="form-control" type="text" name="organisation" id="organisation" value="{{ old('organisation', $intervenant->organisation) }}" placeholder="Ex. : CSE, Bureau Veritas…">
</div>

<div class="form-group">
    <label class="required" for="nom">Nom du contact</label>
    <input class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}" type="text" name="nom" id="nom" value="{{ old('nom', $intervenant->nom) }}" required>
    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <label for="prenom">Prénom</label>
    <input class="form-control" type="text" name="prenom" id="prenom" value="{{ old('prenom', $intervenant->prenom) }}">
</div>

<div class="form-group">
    <label for="telephone">Téléphone</label>
    <input class="form-control" type="text" name="telephone" id="telephone" value="{{ old('telephone', $intervenant->telephone) }}">
</div>

<div class="form-group">
    <label for="email">E-mail</label>
    <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" id="email" value="{{ old('email', $intervenant->email) }}">
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group full-row">
    <label for="adresse">Adresse</label>
    <input class="form-control" type="text" name="adresse" id="adresse" value="{{ old('adresse', $intervenant->adresse) }}">
</div>

<div class="form-group full-row">
    <label for="notes">Observations</label>
    <textarea class="form-control" name="notes" id="notes" rows="3">{{ old('notes', $intervenant->notes) }}</textarea>
</div>
