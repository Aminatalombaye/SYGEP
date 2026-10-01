@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'taskTag', 'index' => 'admin.task-tags.index', 'record' => $taskTag, 'edit' => ['route' => 'admin.task-tags.edit', 'can' => 'task_tag_edit']])
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
                            {{ trans('cruds.taskTag.fields.id') }}
                        </th>
                        <td>
                            {{ $taskTag->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.taskTag.fields.name') }}
                        </th>
                        <td>
                            {{ $taskTag->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection