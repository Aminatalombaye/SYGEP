@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <h1>{{ trans('cruds.periodicReport.title') }}</h1>
        <p class="sub">Bilans imprimables destinés à la direction : patrimoine, stock, maintenance, projets et infrastructures.</p>
    </div>
</div>

<section class="sy-card report-form">
    <div class="sy-card-head"><div><h2>Composer le rapport</h2><p>Le rapport s'ouvre dans un nouvel onglet, prêt à imprimer ou à enregistrer en PDF.</p></div></div>
    <div class="sy-card-body">
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif
        <form method="GET" action="{{ route('admin.periodic-reports.show') }}" target="_blank">
            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2">
                    <label for="periode" class="required">Période</label>
                    <select name="periode" id="periode" class="form-control">
                        @foreach($periods as $key => $label)
                            <option value="{{ $key }}" @selected(old('periode', 'mois_precedent') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" data-custom hidden>
                    <label for="du">Du</label>
                    <input type="date" name="du" id="du" class="form-control" value="{{ now()->startOfMonth()->toDateString() }}">
                </div>
                <div class="form-group" data-custom hidden>
                    <label for="au">Au</label>
                    <input type="date" name="au" id="au" class="form-control" value="{{ today()->toDateString() }}">
                </div>
                <div class="form-group full">
                    <label>Rubriques</label>
                    <div class="report-sections">
                        @foreach($sections as $key => $label)
                            <label><input type="checkbox" name="sections[]" value="{{ $key }}" checked> {{ $label }}</label>
                        @endforeach
                    </div>
                </div>
                <div class="form-group full page-actions">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-bar-graph"></i> Générer le rapport</button>
                </div>
            </div>
        </form>
    </div>
</section>

<div class="sy-grid-2">
    @foreach([
        ['bi-calendar-month', 'Rapport mensuel', 'mois_precedent'],
        ['bi-calendar3', 'Rapport trimestriel', 'trimestre_precedent'],
        ['bi-calendar-range', 'Bilan semestriel', 'semestre'],
        ['bi-calendar-check', 'Bilan annuel', 'annee_precedente'],
    ] as [$icon, $title, $period])
        <a class="sy-card" style="display:block; text-decoration:none" target="_blank"
           href="{{ route('admin.periodic-reports.show', ['periode' => $period, 'sections' => array_keys($sections)]) }}">
            <div class="sy-card-body" style="display:flex; gap: 14px; align-items:center">
                <span class="kpi-icon"><i class="bi {{ $icon }}"></i></span>
                <span>
                    <strong style="display:block; color: var(--sy-text)">{{ $title }}</strong>
                    <span class="muted">{{ \App\Services\PeriodicReport::range($period)[2] }} · toutes rubriques</span>
                </span>
            </div>
        </a>
    @endforeach
</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    function sync() { $('[data-custom]').prop('hidden', $('#periode').val() !== 'personnalisee'); }
    $('#periode').on('change', sync); sync();
});
</script>
@endsection
