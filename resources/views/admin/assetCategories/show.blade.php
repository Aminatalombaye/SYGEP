@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'assetCategory', 'index' => 'admin.asset-categories.index', 'record' => $assetCategory, 'edit' => ['route' => 'admin.asset-categories.edit', 'can' => 'asset_category_edit']])
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
                            {{ trans('cruds.assetCategory.fields.id') }}
                        </th>
                        <td>
                            {{ $assetCategory->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.assetCategory.fields.name') }}
                        </th>
                        <td>
                            {{ $assetCategory->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection