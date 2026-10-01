@extends('layouts.admin')

@section('content')

    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
     

        </div>
    </div>

<div class="container">
    <div class="card">
        <div class="card-header">
            {{ trans('global.show') }} {{ trans('cruds.project.title_singular') }}
        </div>

        <div class="card-body">
            <!-- Informations de base du projet -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <h3>{{ $project->name ?? 'N/A' }}</h3>
                    <p class="text-muted">{{ $project->description ?? 'N/A'}}</p>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>{{ trans('cruds.project.fields.company') }}:</strong>
                            {{ $project->company ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>{{ trans('cruds.project.fields.funding_source') }}:</strong>
                            {{ $project->source ?? 'N/A' }}
                        </div>
                        <div class="col-md-4">
                            <strong>{{ trans('cruds.project.fields.provisional_reception_date') }}:</strong>
                            {{ $project->cost ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header bg-primary text-white">
                <h3>Etape d'exécution
     </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th rowspan="2">Batiment associés</th>
                                    <th colspan="3" class="text-center">Gros œuvre</th>
                                    <th colspan="7" class="text-center">Second œuvre</th>
                                </tr>
                                <tr>
                                    <!-- Sous-titres Gros œuvre -->
                                    <th>Fondation</th>
                                    <th>Élévation</th>
                                    <th>Plancher haut</th>
                                    <th>Enduit</th>
                                    <th>Carrelage</th>
                                    <th>Électricité</th>
                                    <th>Amenagement</th>
                                    <th>Plomberie</th>
                                    <th>Peinture</th>
                                    <th>Pondération</th>
                                </tr>
                            </thead>
                            <tbody>
 
                            @if($etape)
<tr>
    <td>{{ $infra->name ?? 'Non défini' }}</td>
    <td>{{ $etape->fondation ?? 'Non défini' }}</td>
    <td>{{ $etape->elevation ?? 'Non défini' }}</td>
    <td>{{ $etape->plancher ?? 'Non défini' }}</td>
    <td>{{ $etape->enduit ?? 'Non défini' }}</td>
    <td>{{ $etape->carrelage ?? 'Non défini' }}</td>
    <td>{{ $etape->electricite ?? 'Non défini' }}</td>
    <td>{{ $etape->amenagement ?? 'Non défini' }}</td>
    <td>{{ $etape->plomberie ?? 'Non défini' }}</td>
    <td>{{ $etape->peinture ?? 'Non défini' }}</td>
   
    <td>{{ $etape->ponderation ?? 'Non défini' }}</td>
</tr>
@else
<tr>
    <td colspan="11" class="text-center">Aucune étape enregistrée</td>
</tr>
@endif



</tbody>

                        </table>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4">
                <a href="{{ route('admin.etapes.index', $project->id) }}" class="btn btn-secondary">
                   Retour
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .status-realise { background-color: #d4edda; }
    .status-non-realise { background-color: #f8d7da; }
    .status-en-preparation { background-color: #fff3cd; }
    .status-en-cours { background-color: #cce5ff; }
</style>
@endpush
