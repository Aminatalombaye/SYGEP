@extends('layouts.admin')

@section('content')
@include('partials.form-head', ['module' => 'maintenancePlan', 'mode' => 'edit', 'index' => 'admin.maintenance-plans.index', 'record' => $plan])

<div class="card sy-form">
    <div class="card-body">
        <form class="sy-form-grid" method="POST" action="{{ route('admin.maintenance-plans.update', $plan) }}">
            @csrf
            @method('PUT')
            @include('admin.maintenancePlans.partials.form')
            <div class="sy-form-actions">
                <a href="{{ route('admin.maintenance-plans.index') }}" class="btn btn-default">Annuler</a>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check2"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

@if($plan->requests->isNotEmpty())
    <section class="sy-card">
        <div class="sy-card-head"><h2>Dernières interventions générées</h2></div>
        <div class="sy-card-body flush">
            <table class="dash-table">
                <tbody>
                    @foreach($plan->requests as $req)
                        <tr>
                            <td class="strong"><a href="{{ route('admin.maintenance-requests.show', $req) }}">{{ $req->reference }}</a></td>
                            <td class="muted">Prévue le {{ $req->planned_for?->format('d/m/Y') ?? '—' }}</td>
                            <td><span class="pill pill-{{ $req->status_tone }}">{{ $req->status_label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

@can('maintenance_plan_delete')
    <form action="{{ route('admin.maintenance-plans.destroy', $plan) }}" method="POST" onsubmit="return confirm('Supprimer ce plan ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-link text-danger p-0"><i class="bi bi-trash3"></i> Supprimer le plan</button>
    </form>
@endcan
@endsection

@section('scripts')
@parent
@stack('form-js')
@endsection
