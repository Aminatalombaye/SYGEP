@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'service', 'index' => 'admin.services.index', 'record' => $service, 'edit' => ['route' => 'admin.services.edit', 'can' => 'service_edit']])
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
                            {{ trans('cruds.service.fields.id') }}
                        </th>
                        <td>
                            {{ $service->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.service.fields.name') }}
                        </th>
                        <td>
                            {{ $service->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection