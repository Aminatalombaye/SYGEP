@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'taskStatus', 'index' => 'admin.task-statuses.index', 'record' => $taskStatus, 'edit' => ['route' => 'admin.task-statuses.edit', 'can' => 'task_status_edit']])
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
                            {{ trans('cruds.taskStatus.fields.id') }}
                        </th>
                        <td>
                            {{ $taskStatus->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.taskStatus.fields.name') }}
                        </th>
                        <td>
                            {{ $taskStatus->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection