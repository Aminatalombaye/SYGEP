@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb">
            <a href="{{ route('admin.assets.index') }}">Matières</a> ›
            <a href="{{ route('admin.assets.show', $asset) }}">{{ $asset->name }}</a> › Transfert
        </div>
        <h1>Transférer « {{ $asset->name }} »</h1>
        <p class="sub">
            @if($asset->agent || $asset->service)
                Détenteur actuel :
                <strong>{{ $asset->agent?->full_name ?? $asset->service?->name }}</strong>
                @if($current) (bon {{ $current->reference }}) @endif.
                Le bon actuel est clôturé pour cette matière et un nouveau bon est créé.
            @else
                Cette matière n'est pas affectée : le transfert crée simplement une nouvelle affectation.
            @endif
        </p>
    </div>
</div>

<form method="POST" action="{{ route('admin.assets.transfer.store', $asset) }}">
    @csrf
    <section class="sy-card" style="max-width: 900px; margin-left: auto; margin-right: auto">
        <div class="sy-card-head"><h2>Nouveau bénéficiaire</h2></div>
        <div class="sy-card-body">
            @include('admin.assignments.partials.beneficiary', ['selectedAgent' => null, 'selectedService' => null])
        </div>
    </section>

    <div class="page-actions" style="max-width: 900px; margin: 0 auto; justify-content:flex-end">
        <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-default">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-arrow-left-right"></i> Transférer</button>
    </div>
</form>
@endsection

@section('scripts')
@parent
@stack('beneficiary-js')
@endsection
