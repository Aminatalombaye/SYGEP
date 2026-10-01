@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'intervenant', 'mode' => 'edit', 'index' => 'admin.intervenants.index', 'record' => $intervenant])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.intervenants.update', $intervenant) }}">
            @csrf
            @method('PUT')
            @include('admin.intervenants.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.intervenants.show', $intervenant) }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
