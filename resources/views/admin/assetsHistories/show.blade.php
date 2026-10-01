@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'assetsHistory', 'index' => 'admin.assets-histories.index', 'record' => $assetsHistory, 'edit' => ['route' => 'admin.assets-histories.edit', 'can' => 'assets_history_edit']])
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
                            {{ trans('cruds.assetsHistory.fields.id') }}
                        </th>
                        <td>
                            {{ $assetsHistory->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetsHistory.fields.asset') }}
                        </th>
                        <td>
                            {{ $assetsHistory->asset->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetsHistory.fields.status') }}
                        </th>
                        <td>
                            {{ $assetsHistory->status->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetsHistory.fields.location') }}
                        </th>
                        <td>
                            {{ $assetsHistory->location->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetsHistory.fields.assigned_user') }}
                        </th>
                        <td>
                            {{ $assetsHistory->assigned_user->name ?? '' }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetsHistory.fields.created_at') }}
                        </th>
                        <td>
                            {{ $assetsHistory->created_at }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection