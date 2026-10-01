@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'stockItem', 'mode' => 'edit', 'index' => 'admin.stock-items.index', 'record' => $item])

<div class="card sy-form">
    <div class="card-body">
        <p class="hint" style="margin-top: 0">La quantité en stock ne se modifie pas ici : enregistrez une entrée, une sortie ou un ajustement.</p>
        <form class="sy-form-grid" method="POST" action="{{ route('admin.stock-items.update', $item) }}">
            @csrf
            @method('PUT')
            @include('admin.stockItems.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.stock-items.show', $item) }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
