@extends('layouts.admin')
@section('content')

<div class="page-head">
    <div>
        <h1>{{ trans('cruds.tasksCalendar.title') }}</h1>
        <p class="sub">Échéances des tâches planifiées.</p>
    </div>
    <div class="page-actions">
        @can('task_create')
            <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouvelle tâche</a>
        @endcan
    </div>
</div>
<div class="card sy-calendar">

    <div class="card-body">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.css" />
        <div id="calendar"></div>

    </div>
</div>



@endsection

@section('scripts')
@parent
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/fullcalendar.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@3.10.5/dist/locale/fr.js'></script>
<script>
    $(document).ready(function() {
            // page is now ready, initialize the calendar...
            $('#calendar').fullCalendar({
                // put your options and callbacks here
                events : [
@foreach($events as $event)
@if($event->due_date)
                            {
                                title : '{{ $event->name }}',
                                start : '{{ \Carbon\Carbon::createFromFormat(config('panel.date_format'),$event->due_date)->format('Y-m-d') }}',
                                url : '{{ url('admin/tasks').'/'.$event->id.'/edit' }}'
                            },
@endif
@endforeach
                ]
            })
        });
</script>

@stop