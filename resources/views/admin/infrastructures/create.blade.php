@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'infrastructure', 'mode' => 'create', 'index' => 'admin.infrastructures.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.infrastructures.store') }}">
            @csrf
            @include('admin.infrastructures.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.infrastructures.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
