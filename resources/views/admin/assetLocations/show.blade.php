@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'assetLocation', 'index' => 'admin.asset-locations.index', 'record' => $assetLocation, 'edit' => ['route' => 'admin.asset-locations.edit', 'can' => 'asset_location_edit']])
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
                            {{ trans('cruds.assetLocation.fields.id') }}
                        </th>
                        <td>
                            {{ $assetLocation->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetLocation.fields.name') }}
                        </th>
                        <td>
                            {{ $assetLocation->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection