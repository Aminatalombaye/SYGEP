@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'stockItem', 'mode' => 'create', 'index' => 'admin.stock-items.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.stock-items.store') }}">
            @csrf
            @include('admin.stockItems.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.stock-items.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer l'article</button>
            </div>
        </form>
    </div>
</div>
@endsection
