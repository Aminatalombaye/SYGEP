@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.edit') }} {{ trans('cruds.maintenanceRequest.title_singular') }}
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.maintenance-requests.update", [$maintenanceRequest->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="description">{{ trans('cruds.maintenanceRequest.fields.description') }}</label>
                <input class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}" type="text" name="description" id="description" value="{{ old('description', $maintenanceRequest->description) }}" required>
                @if($errors->has('description'))
                    <div class="invalid-feedback">
                        {{ $errors->first('description') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.maintenanceRequest.fields.description_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="status">{{ trans('cruds.maintenanceRequest.fields.status') }}</label>
                <input class="form-control {{ $errors->has('status') ? 'is-invalid' : '' }}" type="text" name="status" id="status" value="{{ old('status', $maintenanceRequest->status) }}" required>
                @if($errors->has('status'))
                    <div class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.maintenanceRequest.fields.status_helper') }}</span>
            </div>
            <div class="form-group">
                <label class="required" for="created_by">{{ trans('cruds.maintenanceRequest.fields.created_by') }}</label>
                <input class="form-control {{ $errors->has('created_by') ? 'is-invalid' : '' }}" type="text" name="created_by" id="created_by" value="{{ old('created_by', $maintenanceRequest->created_by) }}" required>
                @if($errors->has('created_by'))
                    <div class="invalid-feedback">
                        {{ $errors->first('created_by') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.maintenanceRequest.fields.created_by_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="requests">{{ trans('cruds.maintenanceRequest.fields.request') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('global.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('global.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('requests') ? 'is-invalid' : '' }}" name="requests[]" id="requests" multiple>
                    @foreach($requests as $id => $request)
                        <option value="{{ $id }}" {{ (in_array($id, old('requests', [])) || $maintenanceRequest->requests->contains($id)) ? 'selected' : '' }}>{{ $request }}</option>
                    @endforeach
                </select>
                @if($errors->has('requests'))
                    <div class="invalid-feedback">
                        {{ $errors->first('requests') }}
                    </div>
                @endif
                <span class="help-block">{{ trans('cruds.maintenanceRequest.fields.request_helper') }}</span>
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