@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.maintenanceRequest.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.maintenance-requests.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.id') }}
                        </th>
                        <td>
                            {{ $maintenanceRequest->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.description') }}
                        </th>
                        <td>
                            {{ $maintenanceRequest->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.status') }}
                        </th>
                        <td>
                            {{ $maintenanceRequest->status }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.created_by') }}
                        </th>
                        <td>
                            {{ $maintenanceRequest->created_by }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.maintenanceRequest.fields.request') }}
                        </th>
                        <td>
                            @foreach($maintenanceRequest->requests as $key => $request)
                                <span class="label label-info">{{ $request->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.maintenance-requests.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection