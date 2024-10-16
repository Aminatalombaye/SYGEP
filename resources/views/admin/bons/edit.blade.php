@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.bon.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.bons.update", [$bon->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="date_emission">{{ trans('cruds.bon.fields.date_emission') }}</label>
                <input class="form-control date {{ $errors->has('date_emission') ? 'is-invalid' : '' }}" type="text" name="date_emission" id="date_emission" value="{{ old('date_emission', $bon->date_emission) }}" required>
                @if($errors->has('date_emission'))
                    <div class="invalid-feedback">
                        {{ $errors->first('date_emission') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.date_emission_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="organisation">{{ trans('cruds.bon.fields.organisation') }}</label>
                <input class="form-control {{ $errors->has('organisation') ? 'is-invalid' : '' }}" type="text" name="organisation" id="organisation" value="{{ old('organisation', $bon->organisation) }}" required>
                @if($errors->has('organisation'))
                    <div class="invalid-feedback">
                        {{ $errors->first('organisation') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.organisation_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="reference_commande">{{ trans('cruds.bon.fields.reference_commande') }}</label>
                <input class="form-control {{ $errors->has('reference_commande') ? 'is-invalid' : '' }}" type="text" name="reference_commande" id="reference_commande" value="{{ old('reference_commande', $bon->reference_commande) }}" required>
                @if($errors->has('reference_commande'))
                    <div class="invalid-feedback">
                        {{ $errors->first('reference_commande') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.reference_commande_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="nom_destinataire">{{ trans('cruds.bon.fields.nom_destinataire') }}</label>
                <input class="form-control {{ $errors->has('nom_destinataire') ? 'is-invalid' : '' }}" type="text" name="nom_destinataire" id="nom_destinataire" value="{{ old('nom_destinataire', $bon->nom_destinataire) }}" required>
                @if($errors->has('nom_destinataire'))
                    <div class="invalid-feedback">
                        {{ $errors->first('nom_destinataire') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.nom_destinataire_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="bon">{{ trans('cruds.bon.fields.bon') }}</label>
                <input class="form-control {{ $errors->has('bon') ? 'is-invalid' : '' }}" type="text" name="bon" id="bon" value="{{ old('bon', $bon->bon) }}">
                @if($errors->has('bon'))
                    <div class="invalid-feedback">
                        {{ $errors->first('bon') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.bon_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="date_livraison">{{ trans('cruds.bon.fields.date_livraison') }}</label>
                <input class="form-control date {{ $errors->has('date_livraison') ? 'is-invalid' : '' }}" type="text" name="date_livraison" id="date_livraison" value="{{ old('date_livraison', $bon->date_livraison) }}">
                @if($errors->has('date_livraison'))
                    <div class="invalid-feedback">
                        {{ $errors->first('date_livraison') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.bon.fields.date_livraison_helper') }}</span>
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