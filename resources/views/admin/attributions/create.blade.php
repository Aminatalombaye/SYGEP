@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.attribution.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.attributions.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="nom">{{ trans('cruds.attribution.fields.nom') }}</label>
                <input class="form-control {{ $errors->has('nom') ? 'is-invalid' : '' }}" type="text" name="nom" id="nom" value="{{ old('nom', '') }}">
                @if($errors->has('nom'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nom') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attribution.fields.nom_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="type_atribution">{{ trans('cruds.attribution.fields.type_atribution') }}</label>
                <input class="form-control {{ $errors->has('type_atribution') ? 'is-invalid' : '' }}" type="text" name="type_atribution" id="type_atribution" value="{{ old('type_atribution', '') }}">
                @if($errors->has('type_atribution'))
                    <div class="invalid-feedback">
                        {{ $errors->first('type_atribution') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.attribution.fields.type_atribution_helper') }}</span>
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