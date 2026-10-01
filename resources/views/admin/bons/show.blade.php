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
        </div>
    </div>
</div>



@endsection