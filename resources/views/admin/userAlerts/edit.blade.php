@extends('layouts.admin')
@section('content')

@include('partials.form-head', ['module' => 'userAlert', 'mode' => 'edit', 'index' => 'admin.user-alerts.index', 'record' => $userAlert])
<div class="card sy-form">

    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route("admin.user-alerts.update", [$userAlert->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="sy-form-actions">
                <a href="{{ route('admin.user-alerts.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>



@endsection