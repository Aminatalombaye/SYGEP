@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'project', 'mode' => 'edit', 'index' => 'admin.projects.index', 'record' => $project])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.projects.update', $project) }}">
            @csrf
            @method('PUT')
            @include('admin.projects.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
