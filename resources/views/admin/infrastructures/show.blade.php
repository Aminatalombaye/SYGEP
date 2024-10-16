@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.infrastructure.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.infrastructures.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.id') }}
                        </th>
                        <td>
                            {{ $infrastructure->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.name') }}
                        </th>
                        <td>
                            {{ $infrastructure->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.description') }}
                        </th>
                        <td>
                            {{ $infrastructure->description }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.status') }}
                        </th>
                        <td>
                            {{ $infrastructure->status }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.location') }}
                        </th>
                        <td>
                            {{ $infrastructure->location }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.construction_date') }}
                        </th>
                        <td>
                            {{ $infrastructure->construction_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.depreciation_plan') }}
                        </th>
                        <td>
                            {{ $infrastructure->depreciation_plan }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.infrastructure.fields.type') }}
                        </th>
                        <td>
                            {{ $infrastructure->type }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.infrastructures.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection