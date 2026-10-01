@extends('layouts.admin')

@section('content')

<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.assets.index') }}">Matières</a> › {{ $asset->name }}</div>
        <h1>{{ $asset->name ?: 'Matière #'.$asset->id }}</h1>
        <p class="sub">
            {{ $asset->category->name ?? 'Sans catégorie' }}
            @if($asset->serial_number) · N° {{ $asset->serial_number }}@endif
            @if($asset->status) · <span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>@endif
        </p>
    </div>
    <div class="page-actions">
        @can('maintenance_request_create')
            <a href="{{ route('admin.maintenance-requests.create', ['asset' => $asset->id]) }}" class="btn btn-default"><i class="bi bi-tools"></i> Signaler une panne</a>
        @endcan
        @can('asset_edit')
            <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-default"><i class="bi bi-pencil"></i> Modifier</a>
        @endcan
        @if($asset->isAssigned())
            @if($current)
                @can('assignment_return')
                    <a href="{{ route('admin.assignments.return', $current) }}" class="btn btn-default"><i class="bi bi-box-arrow-in-down"></i> Restituer</a>
                @endcan
            @endif
            @can('assignment_return')
                @can('assignment_create')
                    <a href="{{ route('admin.assets.transfer', $asset) }}" class="btn btn-primary"><i class="bi bi-arrow-left-right"></i> Transférer</a>
                @endcan
            @endcan
        @else
            @can('assignment_create')
                <a href="{{ route('admin.assignments.create', ['asset' => $asset->id]) }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> Affecter</a>
            @endcan
        @endif
    </div>
</div>

<div class="sy-grid-2">
    <div>
    <section class="sy-card">
        <div class="sy-card-head"><h2>Situation actuelle</h2></div>
        <div class="sy-card-body">
            @if($asset->isAssigned())
                <div class="holder">
                    <span class="sy-avatar">
                        @if($asset->agent){{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($asset->agent->prenom ?: $asset->agent->nom, 0, 1)) }}@else<i class="bi bi-building"></i>@endif
                    </span>
                    <div>
                        <div class="name">
                            @if($asset->agent)
                                <a href="{{ route('admin.agents.show', $asset->agent) }}">{{ $asset->agent->full_name }}</a>
                            @else
                                {{ $asset->service->name }}
                            @endif
                        </div>
                        <div class="meta">
                            {{ $asset->service->name ?? $asset->agent?->service?->name ?? '' }}
                            @if($current)
                                · bon <a href="{{ route('admin.assignments.show', $current) }}">{{ $current->reference }}</a>
                                depuis le {{ $current->assigned_at?->format('d/m/Y') }}
                                @if($current->expected_return_at) · retour prévu le {{ $current->expected_return_at->format('d/m/Y') }}@endif
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="holder">
                    <span class="sy-avatar" style="background:var(--sy-good-bg);color:var(--sy-good)"><i class="bi bi-check2"></i></span>
                    <div>
                        <div class="name">Non affectée</div>
                        <div class="meta">Emplacement : {{ $asset->location->name ?? '—' }}</div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    <section class="sy-card">
        <div class="sy-card-head">
            <h2>QR code</h2>
            @can('asset_access')
                <a href="{{ route('admin.qr.scanner') }}" class="panel-link"><i class="bi bi-qr-code-scan"></i> Scanner</a>
            @endcan
        </div>
        <div class="sy-card-body qr-panel">
            @if($asset->qr_code)
                <div class="qr-img">{{ $asset->qrSvg(180) }}</div>
                <div class="qr-code-text">{{ $asset->qr_code }}</div>
                <div class="page-actions">
                    <a href="{{ route('admin.assets.label', $asset) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimer l'étiquette</a>
                    <a href="{{ route('admin.assets.qr', ['asset' => $asset, 'telecharger' => 1]) }}" class="btn btn-default"><i class="bi bi-download"></i> SVG</a>
                </div>
            @else
                <div class="empty-state"><i class="bi bi-qr-code"></i> Pas encore de code : lancez « php artisan migrate ».</div>
            @endif
        </div>
    </section>
    </div>

    <section class="sy-card">
        <div class="sy-card-head"><h2>Historique</h2></div>
        <div class="sy-card-body" style="max-height: 320px; overflow-y: auto">
            @if($asset->histories->isEmpty())
                <div class="empty-state"><i class="bi bi-clock"></i> Aucun mouvement.</div>
            @else
                <ul class="timeline">
                    @foreach($asset->histories as $h)
                        <li>
                            <div class="t-date">{{ $h->created_at?->format('d/m/Y à H:i') }}@if($h->user) · {{ $h->user->name }}@endif</div>
                            <div class="t-title">
                                {{ $h->action_label }}
                                @if($h->agent) — {{ $h->agent->full_name }}@elseif($h->service) — {{ $h->service->name }}@endif
                            </div>
                            <div class="t-body">
                                @if($h->status){{ $h->status->label }}@endif
                                @if($h->location) · {{ $h->location->name }}@endif
                                @if($h->assignment) · bon <a href="{{ route('admin.assignments.show', $h->assignment) }}">{{ $h->assignment->reference }}</a>@endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</div>

<div class="card">
    <div class="card-header">
        Fiche technique
    </div>

    <div class="card-body">
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assets.index') }}"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}
            </a>
        </div>
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.id') }}
                    </th>
                    <td>
                        {{ $asset->id }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.category') }}
                    </th>
                    <td>
                        {{ $asset->category->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.serial_number') }}
                    </th>
                    <td>
                        {{ $asset->serial_number }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.name') }}
                    </th>
                    <td>
                        {{ $asset->name }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.photos') }}
                    </th>
                    <td>
                        @foreach($asset->photos as $media)
                            <a href="{{ $media->getUrl() }}" target="_blank">
                                {{ trans('global.view_file') }}
                            </a><br>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.status') }}
                    </th>
                    <td>
                        @if($asset->status)<span class="pill pill-{{ $asset->status->tone }}">{{ $asset->status->label }}</span>@endif
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.location') }}
                    </th>
                    <td>
                        {{ $asset->location->name ?? '' }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.notes') }}
                    </th>
                    <td>
                        {{ $asset->notes }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.type') }}
                    </th>
                    <td>
                        {{ $asset->type }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.date_achat') }}
                    </th>
                    <td>
                        {{ $asset->date_achat }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.date_mise_en_service') }}
                    </th>
                    <td>
                        {{ $asset->date_mise_en_service }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.modele') }}
                    </th>
                    <td>
                        {{ $asset->modele }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.fournisseur') }}
                    </th>
                    <td>
                        @foreach($asset->fournisseurs as $fournisseur)
                            <span class="label label-info">{{ $fournisseur->name }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.bon') }}
                    </th>
                    <td>
                        @foreach($asset->bons as $bon)
                            <span class="label label-info">{{ $bon->bon }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>
                        Détenteur
                    </th>
                    <td>
                        {{ $asset->agent?->full_name ?? $asset->service?->name ?? ($asset->assigned_to ?: '—') }}
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.qr_code') }}
                    </th>
                    <td>
                        <code>{{ $asset->qr_code }}</code>
                    </td>
                </tr>
                <tr>
                    <th>
                        {{ trans('cruds.asset.fields.inventaire_code') }}
                    </th>
                    <td>
                        @foreach($asset->inventaire_codes as $inventaire_code)
                            <span class="label label-info">{{ $inventaire_code->reference }}</span>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="form-group">
            <a class="btn btn-default" href="{{ route('admin.assets.index') }}"><i class="bi bi-arrow-left"></i> {{ trans('global.back_to_list') }}
            </a>
        </div>
    </div>
</div>

@endsection
