@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'inventaire', 'mode' => 'edit', 'index' => 'admin.inventaires.index', 'record' => $inventaire])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.inventaires.update', $inventaire) }}">
            @csrf
            @method('PUT')
            @include('admin.inventaires.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.inventaires.show', $inventaire) }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> {{ trans('global.save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
