@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'inventaire', 'mode' => 'create', 'index' => 'admin.inventaires.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.inventaires.store') }}">
            @csrf
            @include('admin.inventaires.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.inventaires.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Créer la campagne</button>
            </div>
        </form>
    </div>
</div>
@endsection
