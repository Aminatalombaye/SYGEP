@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.attribution.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.attributions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.attribution.fields.id') }}
                        </th>
                        <td>
                            {{ $attribution->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attribution.fields.details') }}
                        </th>
                        <td>
                            {{ $attribution->details }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attribution.fields.nom') }}
                        </th>
                        <td>
                            {{ $attribution->nom }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.attribution.fields.type_atribution') }}
                        </th>
                        <td>
                            {{ $attribution->type_atribution }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.attributions.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection