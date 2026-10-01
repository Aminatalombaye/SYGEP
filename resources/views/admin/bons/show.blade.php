@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'bon', 'index' => 'admin.bons.index', 'record' => $bon, 'edit' => ['route' => 'admin.bons.edit', 'can' => 'bon_edit']])
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
                            {{ trans('cruds.bon.fields.id') }}
                        </th>
                        <td>
                            {{ $bon->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.date_emission') }}
                        </th>
                        <td>
                            {{ $bon->date_emission }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.organisation') }}
                        </th>
                        <td>
                            {{ $bon->organisation }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.reference_commande') }}
                        </th>
                        <td>
                            {{ $bon->reference_commande }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.nom_destinataire') }}
                        </th>
                        <td>
                            {{ $bon->nom_destinataire }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.bon') }}
                        </th>
                        <td>
                            {{ $bon->bon }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.bon.fields.date_livraison') }}
                        </th>
                        <td>
                            {{ $bon->date_livraison }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <h5 style="margin-top: 24px">Matières reçues avec ce bon ({{ $bon->assets->count() }})</h5>
            @if($bon->assets->isEmpty())
                <p class="muted">Aucune matière n'est encore rattachée à ce bon.</p>
            @else
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr><th>Code</th><th>Nom</th><th>Catégorie</th><th>État</th></tr>
                    </thead>
                    <tbody>
                        @foreach($bon->assets as $asset)
                            <tr>
                                <td>{{ $asset->qr_code }}</td>
                                <td>@can('asset_show')<a href="{{ route('admin.assets.show', $asset->id) }}">{{ $asset->name }}</a>@else{{ $asset->name }}@endcan</td>
                                <td>{{ $asset->category->name ?? '' }}</td>
                                <td>@if($asset->status)<span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>



@endsection