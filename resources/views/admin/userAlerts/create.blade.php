@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'userAlert', 'mode' => 'create', 'index' => 'admin.user-alerts.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.user-alerts.store') }}">
            @csrf

            <div class="form-group full-row">
                <label class="required" for="alert_text">Message</label>
                <input class="form-control {{ $errors->has('alert_text') ? 'is-invalid' : '' }}" type="text" name="alert_text" id="alert_text"
                       value="{{ old('alert_text') }}" maxlength="255" required placeholder="Ex. : Inventaire annuel du 15 au 30 novembre">
                @error('alert_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="help-block">Texte court affiché dans la cloche (255 caractères maximum).</span>
            </div>

            @php($destination = old('destination', ''))
            <div class="form-group">
                <label for="destination">Page à ouvrir au clic</label>
                <select class="form-control select2" name="destination" id="destination">
                    <option value="" @selected($destination === '')>Aucune (simple message)</option>
                    @foreach($destinations as $group => $pages)
                        <optgroup label="{{ $group }}">
                            @foreach($pages as $key => $label)
                                <option value="{{ $key }}" @selected($destination === $key)>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                    <optgroup label="Autre">
                        <option value="custom" @selected($destination === 'custom')>Adresse personnalisée…</option>
                    </optgroup>
                </select>
                <span class="help-block">Le destinataire arrive sur cette page en cliquant sur la notification.</span>
            </div>

            <div class="form-group" data-custom-link @if($destination !== 'custom') hidden @endif>
                <label for="alert_link">Adresse personnalisée</label>
                <input class="form-control {{ $errors->has('alert_link') ? 'is-invalid' : '' }}" type="url" name="alert_link" id="alert_link"
                       value="{{ old('alert_link') }}" placeholder="https://…">
                @error('alert_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="help-block">Lien vers un document ou un site externe.</span>
            </div>

            <div class="form-group full-row">
                <label class="required">Destinataires</label>
                @php($audience = old('audience', 'tous'))
                <div class="audience-choice">
                    <label class="audience-option">
                        <input type="radio" name="audience" value="tous" @checked($audience === 'tous')>
                        <span><i class="bi bi-people"></i><strong>Tous les utilisateurs</strong><small>{{ $users->count() }} compte(s)</small></span>
                    </label>
                    <label class="audience-option">
                        <input type="radio" name="audience" value="roles" @checked($audience === 'roles')>
                        <span><i class="bi bi-person-badge"></i><strong>Par rôle</strong><small>Admin, gestionnaires…</small></span>
                    </label>
                    <label class="audience-option">
                        <input type="radio" name="audience" value="users" @checked($audience === 'users')>
                        <span><i class="bi bi-person-check"></i><strong>Personnes choisies</strong><small>Sélection nominative</small></span>
                    </label>
                </div>
            </div>

            <div class="form-group full-row" data-audience="roles" @if($audience !== 'roles') hidden @endif>
                <label for="roles">Rôles</label>
                <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}" name="roles[]" id="roles" multiple>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(in_array($role->id, old('roles', [])))>{{ $role->title }} ({{ $role->users_count }})</option>
                    @endforeach
                </select>
                @error('roles')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="form-group full-row" data-audience="users" @if($audience !== 'users') hidden @endif>
                <label for="users">Utilisateurs</label>
                <select class="form-control select2 {{ $errors->has('users') ? 'is-invalid' : '' }}" name="users[]" id="users" multiple>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" @selected(in_array($id, old('users', [])))>{{ $name }}</option>
                    @endforeach
                </select>
                @error('users')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="sy-form-actions">
                <a href="{{ route('admin.user-alerts.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Envoyer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    var radios = document.querySelectorAll('input[name="audience"]');
    function sync() {
        var value = document.querySelector('input[name="audience"]:checked').value;
        document.querySelectorAll('[data-audience]').forEach(function (block) {
            block.hidden = block.getAttribute('data-audience') !== value;
        });
    }
    radios.forEach(function (r) { r.addEventListener('change', sync); });
    sync();

    var custom = document.querySelector('[data-custom-link]');
    $('#destination').on('change', function () {
        custom.hidden = this.value !== 'custom';
    });
})();
</script>
@endsection
