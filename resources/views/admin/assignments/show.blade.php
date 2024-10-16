@extends('layouts.admin')

@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.show') }} {{ trans('cruds.assignment.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assignments.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.id') }}
                    </th>
                    <td>
                        {{ $assignment->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.quantity') }}
                    </th>
                    <td>
                        {{ $assignment->quantity ?? 'Non renseigné' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.matiere') }}
                    </th>
                    <td>
                        @if($assignment->matieres && $assignment->matieres->isNotEmpty())
                            @foreach($assignment->matieres as $matiere)
                                <span class="badge badge-info">{{ $matiere->serial_number }}</span>
                            @endforeach
                        @else
                            <span>Aucune matière assignée</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.atribution') }}
                    </th>
                    <td>
                        @if($assignment->atributions && $assignment->atributions->isNotEmpty())
                            @foreach($assignment->atributions as $atribution)
                                <span class="badge badge-info">{{ $atribution->type_atribution }}</span>
                            @endforeach
                        @else
                            <span>Aucune attribution</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.utilisateur') }}
                    </th>
                    <td>
                        {{ $assignment->utilisateur ?? 'Aucun utilisateur assigné' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.assignment.fields.service') }}
                    </th>
                    <td>
                        @if($assignment->services && $assignment->services->isNotEmpty())
                            @foreach($assignment->services as $service)
                                <span class="badge badge-info">{{ $service->name }}</span>
                            @endforeach
                        @else
                            <span>Aucun service assigné</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assignments.index') }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>
    </div>
</div>

@endsection
