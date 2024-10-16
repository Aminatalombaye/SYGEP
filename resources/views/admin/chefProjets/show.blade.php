@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.chefProjet.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.chef-projets.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.id') }}
                        </th>
                        <td>
                            {{ $chefProjet->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.nom') }}
                        </th>
                        <td>
                            {{ $chefProjet->nom }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.prenom') }}
                        </th>
                        <td>
                            {{ $chefProjet->prenom }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.adresse') }}
                        </th>
                        <td>
                            {{ $chefProjet->adresse }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.e_mail') }}
                        </th>
                        <td>
                            {{ $chefProjet->e_mail }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.chefProjet.fields.telephone') }}
                        </th>
                        <td>
                            {{ $chefProjet->telephone }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.chef-projets.index') }}">
                    {{ trans('global.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection