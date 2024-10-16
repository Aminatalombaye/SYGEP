@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.assignment.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.assignments.update", [$assignment->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label for="quantity">{{ trans('cruds.assignment.fields.quantity') }}</label>
                <input class="form-control {{ $errors->has('quantity') ? 'is-invalid' : '' }}" type="text" name="quantity" id="quantity" value="{{ old('quantity', $assignment->quantity) }}">
                @if($errors->has('quantity'))
                    <div class="invalid-feedback">
                        {{ $errors->first('quantity') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assignment.fields.quantity_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="matieres">{{ trans('cruds.assignment.fields.matiere') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('matieres') ? 'is-invalid' : '' }}" name="matieres[]" id="matieres" multiple>
                    @foreach($matieres as $id => $matiere)
                        <option value="{{ $id }}" {{ (in_array($id, old('matieres', [])) || $assignment->matieres->contains($id)) ? 'selected' : '' }}>{{ $matiere }}</option>
                    @endforeach
                </select>
                @if($errors->has('matieres'))
                    <div class="invalid-feedback">
                        {{ $errors->first('matieres') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assignment.fields.matiere_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="atributions">{{ trans('cruds.assignment.fields.atribution') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('atributions') ? 'is-invalid' : '' }}" name="atributions[]" id="atributions" multiple>
                    @foreach($atributions as $id => $atribution)
                        <option value="{{ $id }}" {{ (in_array($id, old('atributions', [])) || $assignment->atributions->contains($id)) ? 'selected' : '' }}>{{ $atribution }}</option>
                    @endforeach
                </select>
                @if($errors->has('atributions'))
                    <div class="invalid-feedback">
                        {{ $errors->first('atributions') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assignment.fields.atribution_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="utilisateur">{{ trans('cruds.assignment.fields.utilisateur') }}</label>
                <input class="form-control {{ $errors->has('utilisateur') ? 'is-invalid' : '' }}" type="text" name="utilisateur" id="utilisateur" value="{{ old('utilisateur', $assignment->utilisateur) }}">
                @if($errors->has('utilisateur'))
                    <div class="invalid-feedback">
                        {{ $errors->first('utilisateur') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assignment.fields.utilisateur_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="atributions">{{ trans('cruds.assignment.fields.service') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('service') ? 'is-invalid' : '' }}" name="service[]" id="services" multiple>
                    @foreach($services as $id => $service)
                        <option value="{{ $id }}" {{ (in_array($id, old('services', [])) || $assignment->atributions->contains($id)) ? 'selected' : '' }}>{{ $service }}</option>
                    @endforeach
                </select>
                @if($errors->has('services'))
                    <div class="invalid-feedback">
                        {{ $errors->first('services') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assignment.fields.service_helper') }}</span>
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