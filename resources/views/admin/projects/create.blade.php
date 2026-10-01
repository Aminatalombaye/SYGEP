@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'project', 'mode' => 'create', 'index' => 'admin.projects.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.projects.store') }}">
            @csrf
            @include('admin.projects.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.projects.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Créer le projet</button>
            </div>
        </form>
    </div>
</div>
@endsection
