@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb"><a href="{{ route('admin.assignments.index') }}">Affectations</a> › Nouvelle</div>
        <h1>Nouvelle affectation</h1>
        <p class="sub">Remettre du matériel à un agent ou à un service. Un bon numéroté est créé automatiquement.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.assignments.store') }}">
    @csrf
    <div class="sy-grid-2">
        <div>
            <section class="sy-card">
                <div class="sy-card-head">
                    <div>
                        <h2>Matériel à affecter</h2>
                        <p>Seules les matières disponibles (ni affectées, ni en panne) sont proposées.</p>
                    </div>
                </div>
                <div class="sy-card-body">
                    @php($assetErrors = collect($errors->get('assets'))->merge(collect($errors->get('assets.*'))->flatten()))
                    @if($assetErrors->isNotEmpty())
                        <div class="alert alert-danger">
                            @foreach($assetErrors as $msg)<div>{{ $msg }}</div>@endforeach
                        </div>
                    @endif

                    @include('admin.assignments.partials.asset-picker', [
                        'assets'   => $availableAssets,
                        'selected' => old('assets', $selectedAssets),
                    ])
                </div>
            </section>
        </div>

        <div>
            <section class="sy-card">
                <div class="sy-card-head"><h2>Bénéficiaire et conditions</h2></div>
                <div class="sy-card-body">
                    @include('admin.assignments.partials.beneficiary')
                </div>
            </section>

            <div class="page-actions" style="justify-content:flex-end">
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-default">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Enregistrer l'affectation</button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
@parent
@stack('beneficiary-js')
@stack('picker-js')
@endsection
