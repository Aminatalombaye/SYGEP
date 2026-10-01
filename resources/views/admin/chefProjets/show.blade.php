@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'chefProjet', 'index' => 'admin.chef-projets.index', 'record' => $chefProjet, 'edit' => ['route' => 'admin.chef-projets.edit', 'can' => 'chef_projet_edit']])
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
        </div>
    </div>
</div>



@endsection