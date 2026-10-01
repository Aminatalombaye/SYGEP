@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'maintenanceRequest', 'mode' => 'edit', 'record' => $maintenanceRequest, 'index' => 'admin.maintenance-requests.index'])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.maintenance-requests.update', $maintenanceRequest) }}">
            @csrf
            @method('PUT')
            @include('admin.maintenanceRequests.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.maintenance-requests.show', $maintenanceRequest) }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
@stack('form-js')
@endsection
