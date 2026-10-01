@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'maintenancePlan', 'mode' => 'create', 'index' => 'admin.maintenance-plans.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.maintenance-plans.store') }}">
            @csrf
            @include('admin.maintenancePlans.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.maintenance-plans.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
@stack('form-js')
@endsection
