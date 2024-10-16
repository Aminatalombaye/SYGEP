@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.bon.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.bons.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
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
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.bons.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection