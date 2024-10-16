@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.chefProjet.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.chef-projets.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nom">{{ trans('cruds.chefProjet.fields.nom') }}</label>
                <input class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}" type="text" name="nom" id="nom" value="{{ old('nom', '') }}">
                @if($errors->has('nom'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nom') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.chefProjet.fields.nom_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="prenom">{{ trans('cruds.chefProjet.fields.prenom') }}</label>
                <input class="form-control {{ $errors->has('prenom') ? 'is-invalid' : '' }}" type="text" name="prenom" id="prenom" value="{{ old('prenom', '') }}">
                @if($errors->has('prenom'))
                    <div class="invalid-feedback">
                        {{ $errors->first('prenom') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.chefProjet.fields.prenom_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="adresse">{{ trans('cruds.chefProjet.fields.adresse') }}</label>
                <input class="form-control {{ $errors->has('adresse') ? 'is-invalid' : '' }}" type="text" name="adresse" id="adresse" value="{{ old('adresse', '') }}">
                @if($errors->has('adresse'))
                    <div class="invalid-feedback">
                        {{ $errors->first('adresse') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.chefProjet.fields.adresse_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="e_mail">{{ trans('cruds.chefProjet.fields.e_mail') }}</label>
                <input class="form-control {{ $errors->has('e_mail') ? 'is-invalid' : '' }}" type="text" name="e_mail" id="e_mail" value="{{ old('e_mail', '') }}">
                @if($errors->has('e_mail'))
                    <div class="invalid-feedback">
                        {{ $errors->first('e_mail') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.chefProjet.fields.e_mail_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="telephone">{{ trans('cruds.chefProjet.fields.telephone') }}</label>
                <input class="form-control {{ $errors->has('telephone') ? 'is-invalid' : '' }}" type="text" name="telephone" id="telephone" value="{{ old('telephone', '') }}">
                @if($errors->has('telephone'))
                    <div class="invalid-feedback">
                        {{ $errors->first('telephone') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.chefProjet.fields.telephone_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('global.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection