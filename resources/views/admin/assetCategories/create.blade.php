@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'assetCategory', 'mode' => 'create', 'index' => 'admin.asset-categories.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.asset-categories.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.assetCategory.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', '') }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assetCategory.fields.name_helper') }}</span>
            </div>
            <div class="sy-form-actions">
                <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection