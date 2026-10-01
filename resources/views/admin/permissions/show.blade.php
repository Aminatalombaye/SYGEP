@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'permission', 'index' => 'admin.permissions.index', 'record' => $permission, 'edit' => ['route' => 'admin.permissions.edit', 'can' => 'permission_edit']])
<div class="card">
    <div class="card-header">
        Détails
    </div>

    <div class="card-body">
        <div class="form-group">
            <table class="table sy-details">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.permission.fields.id') }}
                        </th>
                        <td>
                            {{ $permission->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.permission.fields.title') }}
                        </th>
                        <td>
                            {{ $permission->title }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection