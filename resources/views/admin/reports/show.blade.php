@extends('layouts.admin')
@section('content')

@include('partials.show-head', ['module' => 'report', 'index' => 'admin.reports.index', 'record' => $report, 'edit' => ['route' => 'admin.reports.edit', 'can' => 'report_edit']])
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
                            {{ trans('cruds.report.fields.id') }}
                        </th>
                        <td>
                            {{ $report->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.report.fields.title') }}
                        </th>
                        <td>
                            {{ $report->title }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.report.fields.content') }}
                        </th>
                        <td>
                            @if($report->content)
                                <a href="{{ $report->content->getUrl() }}" target="_blank">
                                    {{ trans('global.view_file') }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.report.fields.report_date') }}
                        </th>
                        <td>
                            {{ $report->report_date }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('cruds.report.fields.project') }}
                        </th>
                        <td>
                            @foreach($report->projects as $key => $project)
                                <span class="label label-info">{{ $project->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection