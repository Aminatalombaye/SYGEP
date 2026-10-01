@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'intervenant', 'mode' => 'create', 'index' => 'admin.intervenants.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.intervenants.store') }}">
            @csrf
            @if(request('project'))<input type="hidden" name="project" value="{{ (int) request('project') }}">@endif
            @include('admin.intervenants.partials.form')
            <div class="sy-form-actions">
                <a href="{{ request('project') ? route('admin.projects.show', (int) request('project')) : route('admin.intervenants.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
