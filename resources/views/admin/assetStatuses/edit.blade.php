@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'assetStatus', 'mode' => 'edit', 'index' => 'admin.asset-statuses.index', 'record' => $assetStatus])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.asset-statuses.update", [$assetStatus->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('cruds.assetStatus.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $assetStatus->name) }}" required>
                @if($errors->has('name'))
                    <div class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.assetStatus.fields.name_helper') }}</span>
            </div>
            <div class="sy-form-actions">
                <a href="{{ route('admin.asset-statuses.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection