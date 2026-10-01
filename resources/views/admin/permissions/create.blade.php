@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'permission', 'mode' => 'create', 'index' => 'admin.permissions.index'])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.permissions.store") }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="required" for="title">{{ trans('cruds.permission.fields.title') }}</label>
                <input class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}" type="text" name="title" id="title" value="{{ old('title', '') }}" required>
                @if($errors->has('title'))
                    <div class="invalid-feedback">
                        {{ $errors->first('title') }}
                    </div>
                @endif
                <span class="help-block">Code technique au format <code>module_action</code> (ex. : <code>stock_item_edit</code>) : le droit est alors classé automatiquement dans la bonne section.</span>
            </div>
            <div class="sy-form-actions">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection