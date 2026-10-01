@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'agent', 'mode' => 'create', 'index' => 'admin.agents.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.agents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nom">{{ trans('cruds.agent.fields.nom') }}</label>
                <input class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}" type="text" name="nom" id="nom" value="{{ old('nom', '') }}">
                @if($errors->has('nom'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nom') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.agent.fields.nom_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="prenom">{{ trans('cruds.agent.fields.prenom') }}</label>
                <input class="form-control {{ $errors->has('prenom') ? 'is-invalid' : '' }}" type="text" name="prenom" id="prenom" value="{{ old('prenom', '') }}">
                @if($errors->has('prenom'))
                    <div class="invalid-feedback">
                        {{ $errors->first('prenom') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.agent.fields.prenom_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="adresse">{{ trans('cruds.agent.fields.adresse') }}</label>
                <input class="form-control {{ $errors->has('adresse') ? 'is-invalid' : '' }}" type="text" name="adresse" id="adresse" value="{{ old('adresse', '') }}">
                @if($errors->has('adresse'))
                    <div class="invalid-feedback">
                        {{ $errors->first('adresse') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.agent.fields.adresse_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="email">{{ trans('cruds.agent.fields.email') }}</label>
                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" type="text" name="email" id="email" value="{{ old('email', '') }}">
                @if($errors->has('email'))
                    <div class="invalid-feedback">
                        {{ $errors->first('email') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.agent.fields.email_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="telephone">{{ trans('cruds.agent.fields.telephone') }}</label>
                <input class="form-control {{ $errors->has('telephone') ? 'is-invalid' : '' }}" type="text" name="telephone" id="telephone" value="{{ old('telephone', '') }}">
                @if($errors->has('telephone'))
                    <div class="invalid-feedback">
                        {{ $errors->first('telephone') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.agent.fields.telephone_helper') }}</span>
            </div>
            <div class="form-group">
    <label for="service">{{ trans('cruds.agent.fields.service') }}</label>
    <select class="form-control {{ $errors->has('service_id') ? 'is-invalid' : '' }}" name="service_id" id="service">
        <option value="">{{ trans('global.pleaseSelect') }}</option>
        @foreach($services as $id => $nom)
            <option value="{{ $id }}" {{ old('service_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
        @endforeach
    </select>
    @if($errors->has('service_id'))
        <div class="invalid-feedback">
            {{ $errors->first('service_id') }}
        </div>
    @endif
    <span class="help-block">{{ trans('cruds.agent.fields.service_helper') }}</span>

</div>

            <div class="sy-form-actions">
                <a href="{{ route('admin.agents.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
