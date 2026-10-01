@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.maintenance-requests.index') }}">{{ trans('cruds.maintenanceRequest.title') }}</a> › Préventive</div>
        <h1>{{ trans('cruds.maintenancePlan.title') }}</h1>
        <p class="sub">Entretiens périodiques : les demandes sont créées automatiquement à l'approche de chaque échéance.</p>
    </div>
    <div class="page-actions">
        @can('maintenance_plan_create')
            <a href="{{ route('admin.maintenance-plans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau plan</a>
        @endcan
    </div>
</div>

@include('partials.module-overview', ['head' => false])

<div class="sy-card">
    <div class="sy-card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable datatable-MaintenancePlan">
                <thead>
                    <tr>
                        <th width="10"></th>
                        <th>Opération</th>
                        <th>Concerne</th>
                        <th>Périodicité</th>
                        <th>Prochaine échéance</th>
                        <th>Dernière réalisation</th>
                        <th>Responsable</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                        <tr data-entry-id="{{ $plan->id }}" @unless($plan->active) style="opacity: .55" @endunless>
                            <td></td>
                            <td class="strong">
                                {{ $plan->title }}
                                @unless($plan->active)<div class="muted">Plan suspendu</div>@endunless
                            </td>
                            <td class="muted">{{ $plan->target_label }}</td>
                            <td>{{ $plan->frequency_label }}</td>
                            <td class="nowrap" data-order="{{ $plan->next_due_at?->toDateString() }}">
                                {{ $plan->next_due_at?->format('d/m/Y') }}
                                @if($plan->is_overdue)
                                    <div><span class="pill pill-critical">Dépassée</span></div>
                                @elseif($plan->is_due)
                                    <div><span class="pill pill-warning">À programmer</span></div>
                                @endif
                            </td>
                            <td class="muted nowrap">{{ $plan->last_done_at?->format('d/m/Y') ?? 'Jamais' }}</td>
                            <td class="muted">{{ $plan->responsible->name ?? '—' }}</td>
                            <td class="nowrap">
                                @can('maintenance_plan_edit')
                                    @if($plan->active)
                                        <form method="POST" action="{{ route('admin.maintenance-plans.generate', $plan) }}" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-icon" title="Lancer : créer la demande maintenant" aria-label="Lancer"><i class="bi bi-lightning-charge"></i></button>
                                        </form>
                                    @endif
                                    <a class="btn btn-xs btn-icon" href="{{ route('admin.maintenance-plans.edit', $plan) }}" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    $('.datatable-MaintenancePlan:not(.ajaxTable)').DataTable({ buttons: $.extend(true, [], $.fn.dataTable.defaults.buttons), order: [[4, 'asc']], pageLength: 50 })
})
</script>
@endsection
