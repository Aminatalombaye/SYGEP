@extends('layouts.admin')

@section('content')
<div class="page-head form-page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.stock-movements.index') }}">{{ trans('cruds.stockMovement.title') }}</a> › Nouveau</div>
        <h1>Mouvement de stock</h1>
        <p class="sub">Entrée (réception), sortie (dotation d'un service) ou ajustement après comptage.</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-default"><i class="bi bi-x-lg"></i> Annuler</a>
    </div>
</div>

<div class="card sy-form">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.stock-movements.store') }}">
            @csrf
            @include('admin.stockMovements.partials.fields')
            <div class="sy-form-actions">
                <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer le mouvement</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
@stack('movement-js')
@endsection
