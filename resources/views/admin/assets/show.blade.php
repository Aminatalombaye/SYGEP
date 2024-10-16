@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.asset.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assets.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.id') }}
                    </th>
                    <td>
                        {{ $asset->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.category') }}
                    </th>
                    <td>
                        {{ $asset->category->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.serial_number') }}
                    </th>
                    <td>
                        {{ $asset->serial_number }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.name') }}
                    </th>
                    <td>
                        {{ $asset->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.photos') }}
                    </th>
                    <td>
                        @foreach($asset->photos as $media)
                            <a href="{{ $media->getUrl() }}" target="_blank">
                                {{ trans('global.view_file') }}
                            </a><br>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.status') }}
                    </th>
                    <td>
                        {{ $asset->status->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.location') }}
                    </th>
                    <td>
                        {{ $asset->location->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.notes') }}
                    </th>
                    <td>
                        {{ $asset->notes }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.type') }}
                    </th>
                    <td>
                        {{ $asset->type }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.date_achat') }}
                    </th>
                    <td>
                        {{ $asset->date_achat }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.date_mise_en_service') }}
                    </th>
                    <td>
                        {{ $asset->date_mise_en_service }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.modele') }}
                    </th>
                    <td>
                        {{ $asset->modele }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.fournisseur') }}
                    </th>
                    <td>
                        @foreach($asset->fournisseurs as $fournisseur)
                            <span class="label label-info">{{ $fournisseur->name }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.bon') }}
                    </th>
                    <td>
                        @foreach($asset->bons as $bon)
                            <span class="label label-info">{{ $bon->bon }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.assigned_to') }}
                    </th>
                    <td>
                        {{ $asset->assigned_to }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.qr_code') }}
                    </th>
                    <td>
                        {{ $asset->qr_code }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.inventaire_code') }}
                    </th>
                    <td>
                        @foreach($asset->inventaire_codes as $inventaire_code)
                            <span class="label label-info">{{ $inventaire_code->reference }}</span>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assets.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
    </div>
</div>

@endsection
