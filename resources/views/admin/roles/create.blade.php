@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'role', 'mode' => 'create', 'index' => 'admin.roles.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.roles.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="title">{{ trans('cruds.role.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', '') }}" required>
                @if($errors->has('title'))
                    <div class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.role.fields.title_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="description">Description du rôle</label>
                <textarea class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" name="description" id="description" rows="3" placeholder="Missions et responsabilités de ce rôle : ce qu'il peut faire, sur quel périmètre…">{{ old('description', '') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <span class="help-block">Affichée dans la liste des rôles pour aider à choisir le bon rôle lors de la création d'un compte.</span>
            </div>
            @include('admin.roles.partials.permissions', ['selected' => old('permissions', [])])
            <div class="sy-form-actions">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection

@section('scripts')
@parent
@stack('perm-js')
@endsection
