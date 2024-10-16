@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('cruds.infrastructure.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.infrastructures.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.infrastructure.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="description">{{ trans('cruds.infrastructure.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', '') }}" required>
                @if($errors->has('description'))
                    <div class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="status">{{ trans('cruds.infrastructure.fields.status') }}</label>
                <input class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" type="text" name="status" id="status" value="{{ old('status', '') }}" required>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="location">{{ trans('cruds.infrastructure.fields.location') }}</label>
                <input class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" type="text" name="location" id="location" value="{{ old('location', '') }}" required>
                @if($errors->has('location'))
                    <div class="invalid-feedback">
                        {{ $errors->first('location') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.location_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="construction_date">{{ trans('cruds.infrastructure.fields.construction_date') }}</label>
                <input class="form-control date {{ $errors->has('construction_date') ? 'is-invalid' : '' }}" type="text" name="construction_date" id="construction_date" value="{{ old('construction_date') }}" required>
                @if($errors->has('construction_date'))
                    <div class="invalid-feedback">
                        {{ $errors->first('construction_date') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.construction_date_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="depreciation_plan">{{ trans('cruds.infrastructure.fields.depreciation_plan') }}</label>
                <input class="form-control {{ $errors->has('depreciation_plan') ? 'is-invalid' : '' }}" type="text" name="depreciation_plan" id="depreciation_plan" value="{{ old('depreciation_plan', '') }}">
                @if($errors->has('depreciation_plan'))
                    <div class="invalid-feedback">
                        {{ $errors->first('depreciation_plan') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.depreciation_plan_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="type">{{ trans('cruds.infrastructure.fields.type') }}</label>
                <input class="form-control {{ $errors->has('type') ? 'is-invalid' : '' }}" type="text" name="type" id="type" value="{{ old('type', '') }}">
                @if($errors->has('type'))
                    <div class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.infrastructure.fields.type_helper') }}</span>
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