@extends('layouts.admin')

@section('content')
@php
    $user = auth()->user();
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $tiles = [
        ['label' => 'Matières enregistrées',  'value' => $kpis['assets'],         'icon' => 'bi-box-seam',             'route' => 'admin.assets.index',      'can' => 'asset_access'],
        ['label' => 'Disponibles',            'value' => $kpis['available'],      'icon' => 'bi-check-circle',         'route' => 'admin.assets.index',      'can' => 'asset_access', 'tone' => 'good'],
        ['label' => 'Affectées',              'value' => $kpis['assigned'],       'icon' => 'bi-person-check',         'route' => 'admin.assignments.index', 'can' => 'assignment_access'],
        ['label' => 'En panne / réparation',  'value' => $kpis['out_of_service'], 'icon' => 'bi-exclamation-triangle', 'route' => 'admin.assets.index',      'can' => 'asset_access', 'tone' => 'critical'],
        ['label' => 'Retours en retard',      'value' => $kpis['overdue'],        'icon' => 'bi-clock-history',        'route' => 'admin.assignments.index', 'params' => ['statut' => 'en_retard'], 'can' => 'assignment_access', 'tone' => $kpis['overdue'] ? 'critical' : null],
        ['label' => 'Maintenance à traiter',  'value' => $kpis['maintenance'],    'icon' => 'bi-tools',                'route' => 'admin.maintenance-requests.index', 'can' => 'maintenance_request_access', 'tone' => $kpis['maintenance_pending'] ? 'warning' : null],
        ['label' => 'Projets actifs',         'value' => $kpis['projects'],       'icon' => 'bi-kanban',               'route' => 'admin.projects.index',    'can' => 'project_access', 'tone' => $kpis['projects_late'] ? 'critical' : null],
        ['label' => 'Stock sous le seuil',    'value' => $kpis['stock_low'],      'icon' => 'bi-box2',                 'route' => 'admin.stock-items.index', 'params' => ['niveau' => 'bas'], 'can' => 'stock_item_access', 'tone' => $kpis['stock_low'] ? 'warning' : null],
    ];
    $actions = [
        ['label' => 'Ajouter une matière',   'icon' => 'bi-plus-lg',     'route' => 'admin.assets.create',               'can' => 'asset_create'],
        ['label' => 'Nouvelle affectation',  'icon' => 'bi-person-plus', 'route' => 'admin.assignments.create',          'can' => 'assignment_create'],
        ['label' => 'Demande de maintenance','icon' => 'bi-wrench',      'route' => 'admin.maintenance-requests.create', 'can' => 'maintenance_request_create'],
        ['label' => 'Nouveau projet',        'icon' => 'bi-kanban',      'route' => 'admin.projects.create',             'can' => 'project_create'],
        ['label' => 'Sortie de stock',       'icon' => 'bi-box-arrow-up','route' => 'admin.stock-movements.create',      'can' => 'stock_movement_create'],
        ['label' => 'Rapport périodique',    'icon' => 'bi-file-earmark-bar-graph', 'route' => 'admin.periodic-reports.index', 'can' => 'periodic_report_access'],
    ];
@endphp

<div class="dash">
    <div class="dash-head">
        <div>
            <p class="dash-date">{{ ucfirst(now()->locale('fr')->translatedFormat('l j F Y')) }}</p>
            <h1 class="dash-title">{{ $greeting }}, {{ \Illuminate\Support\Str::of($user->name)->before(' ') }}</h1>
            <p class="dash-sub">Voici l'état du patrimoine matériel du MEFPT aujourd'hui.</p>
        </div>
        <div class="dash-actions">
            @foreach($actions as $a)
                @can($a['can'])
                    <a href="{{ route($a['route']) }}" class="dash-btn {{ $loop->first ? 'dash-btn-primary' : '' }}">
                        <i class="bi {{ $a['icon'] }}" aria-hidden="true"></i> {{ $a['label'] }}
                    </a>
                @endcan
            @endforeach
        </div>
    </div>

    @if($unreadMessages > 0)
        @can('contact_message_access')
            <a href="{{ route('admin.contact-messages.index') }}" class="dash-notice">
                <i class="bi bi-envelope-exclamation" aria-hidden="true"></i>
                <span><strong>{{ $unreadMessages }}</strong> {{ $unreadMessages > 1 ? 'nouveaux messages' : 'nouveau message' }} de contact à lire</span>
                <i class="bi bi-arrow-right push-right" aria-hidden="true"></i>
            </a>
        @endcan
    @endif

    <div class="kpi-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr))">
        @foreach($tiles as $t)
            @php($canSee = \Illuminate\Support\Facades\Gate::allows($t['can']))
            <a @if($canSee) href="{{ route($t['route'], $t['params'] ?? []) }}" @endif class="kpi {{ !empty($t['tone']) ? 'kpi-'.$t['tone'] : '' }} {{ $canSee ? '' : 'kpi-static' }}">
                <span class="kpi-icon"><i class="bi {{ $t['icon'] }}" aria-hidden="true"></i></span>
                <span class="kpi-body">
                    <span class="kpi-value">{{ number_format($t['value'], 0, ',', ' ') }}</span>
                    <span class="kpi-label">{{ $t['label'] }}</span>
                </span>
            </a>
        @endforeach
    </div>

    <div class="panel-grid">
        <section class="panel panel-wide">
            <header class="panel-head">
                <div>
                    <h2>Nouvelles matières</h2>
                    <p>Enregistrements par mois, sur les 12 derniers mois</p>
                </div>
            </header>
            <div class="chart-box chart-tall"><canvas id="chart-monthly" aria-label="Nouvelles matières par mois" role="img"></canvas></div>
        </section>

        <section class="panel">
            <header class="panel-head">
                <div>
                    <h2>Matières par statut</h2>
                    <p>Répartition actuelle du parc</p>
                </div>
            </header>
            <div class="chart-box"><canvas id="chart-status" aria-label="Matières par statut" role="img"></canvas></div>
        </section>

        <section class="panel">
            <header class="panel-head">
                <div>
                    <h2>Matières par catégorie</h2>
                    <p>Catégories les plus représentées</p>
                </div>
            </header>
            <div class="chart-box"><canvas id="chart-category" aria-label="Matières par catégorie" role="img"></canvas></div>
        </section>

        <section class="panel">
            <header class="panel-head">
                <div>
                    <h2>Tâches par statut</h2>
                    <p>Suivi des interventions</p>
                </div>
            </header>
            <div class="chart-box"><canvas id="chart-tasks" aria-label="Tâches par statut" role="img"></canvas></div>
        </section>
    </div>

    <div class="panel-grid panel-grid-2">
        <section class="panel panel-wide">
            <header class="panel-head">
                <div>
                    <h2>Derniers mouvements</h2>
                    <p>Affectations, restitutions, transferts et changements de statut</p>
                </div>
                @can('assets_history_access')
                    <a href="{{ route('admin.assets-histories.index') }}" class="panel-link">Tout voir <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endcan
            </header>
            <div class="table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr><th>Date</th><th>Mouvement</th><th>Matière</th><th>Détenteur</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @forelse($latestMovements as $m)
                            <tr>
                                <td class="nowrap muted">{{ $m->created_at?->format('d/m/Y H:i') }}</td>
                                <td><span class="pill pill-{{ $m->action ?? 'modification' }}">{{ $m->action_label }}</span></td>
                                <td class="strong">{{ $m->asset->name ?? '—' }}</td>
                                <td>
                                    @if($m->agent)
                                        {{ $m->agent->full_name }}@if($m->service) <span class="muted">· {{ $m->service->name }}</span>@endif
                                    @else
                                        {{ $m->service->name ?? '—' }}
                                    @endif
                                </td>
                                <td>{{ $statusLabel($m->status->name ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty"><i class="bi bi-inbox" aria-hidden="true"></i> Aucun mouvement enregistré pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <header class="panel-head">
                <div><h2>Projets récents</h2></div>
                @can('project_access')
                    <a href="{{ route('admin.projects.index') }}" class="panel-link">Tout voir <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endcan
            </header>
            <ul class="mini-list">
                @forelse($latestProjects as $p)
                    <li>
                        <span class="mini-icon"><i class="bi bi-kanban" aria-hidden="true"></i></span>
                        <span class="mini-body">
                            <a class="strong" href="{{ route('admin.projects.show', $p) }}">{{ $p->name }}</a>
                            <span class="muted">{{ $p->progress }} % · échéance {{ $p->end_date?->format('d/m/Y') ?? '—' }}</span>
                        </span>
                        <span class="pill pill-{{ $p->status_tone }}">{{ $p->is_late ? 'En retard' : $p->status_label }}</span>
                    </li>
                @empty
                    <li class="empty"><i class="bi bi-inbox" aria-hidden="true"></i> Aucun projet.</li>
                @endforelse
            </ul>
        </section>

        <section class="panel">
            <header class="panel-head">
                <div><h2>Demandes de maintenance</h2></div>
                @can('maintenance_request_access')
                    <a href="{{ route('admin.maintenance-requests.index') }}" class="panel-link">Tout voir <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                @endcan
            </header>
            <ul class="mini-list">
                @forelse($latestRequests as $r)
                    <li>
                        <span class="mini-icon"><i class="bi bi-tools" aria-hidden="true"></i></span>
                        <span class="mini-body">
                            <a class="strong" href="{{ route('admin.maintenance-requests.show', $r) }}">{{ \Illuminate\Support\Str::limit($r->title ?: $r->description, 48) }}</a>
                            <span class="muted">{{ $r->reference }} · {{ $r->requester_name }} · {{ $r->created_at?->format('d/m/Y') }}</span>
                        </span>
                        <span class="pill pill-{{ $r->status_tone }}">{{ $r->status_label }}</span>
                    </li>
                @empty
                    <li class="empty"><i class="bi bi-inbox" aria-hidden="true"></i> Aucune demande.</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
(function () {
    if (!window.Chart) return;

    var ink = {
        series: '#1a3a5c',
        seriesHover: '#2a78d6',
        text: '#52514e',
        muted: '#8a8f98',
        grid: 'rgba(15, 23, 42, 0.06)'
    };

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 13;
    Chart.defaults.color = ink.text;

    var tooltip = {
        backgroundColor: '#0f1f33',
        padding: 10,
        cornerRadius: 8,
        displayColors: false,
        titleFont: { weight: '600' },
        callbacks: {
            label: function (ctx) {
                var v = ctx.parsed.x !== undefined && ctx.chart.options.indexAxis === 'y' ? ctx.parsed.x : ctx.parsed.y;
                return v + (v > 1 ? ' éléments' : ' élément');
            }
        }
    };

    function bar(id, data, horizontal) {
        var el = document.getElementById(id);
        if (!el) return;
        var total = data.values.reduce(function (a, b) { return a + b; }, 0);
        if (!data.values.length || total === 0) {
            el.parentNode.innerHTML = '<p class="chart-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i> Pas encore de données</p>';
            return;
        }
        var valueAxis = {
            beginAtZero: true,
            ticks: { precision: 0, color: ink.muted },
            grid: { color: ink.grid, drawTicks: false },
            border: { display: false }
        };
        var catAxis = {
            ticks: { color: ink.text, autoSkip: !horizontal, maxRotation: 0 },
            grid: { display: false },
            border: { color: 'rgba(15,23,42,0.15)' }
        };
        new Chart(el, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.values,
                    backgroundColor: ink.series,
                    hoverBackgroundColor: ink.seriesHover,
                    borderRadius: 4,
                    borderSkipped: 'start',
                    maxBarThickness: horizontal ? 18 : 28,
                    categoryPercentage: 0.7,
                    barPercentage: 0.9
                }]
            },
            options: {
                indexAxis: horizontal ? 'y' : 'x',
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: tooltip },
                scales: horizontal ? { x: valueAxis, y: catAxis } : { x: catAxis, y: valueAxis }
            }
        });
    }

    var charts = @json($charts);
    bar('chart-monthly',  charts.monthly,  false);
    bar('chart-status',   charts.status,   true);
    bar('chart-category', charts.category, true);
    bar('chart-tasks',    charts.tasks,    true);
})();
</script>
@endsection
