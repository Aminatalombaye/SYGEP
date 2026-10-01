@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'assetStatus', 'index' => 'admin.asset-statuses.index', 'record' => $assetStatus, 'edit' => ['route' => 'admin.asset-statuses.edit', 'can' => 'asset_status_edit']])
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
                            {{ trans('cruds.assetStatus.fields.id') }}
                        </th>
                        <td>
                            {{ $assetStatus->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetStatus.fields.name') }}
                        </th>
                        <td>
                            {{ $assetStatus->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection