<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport {{ $label }} | SYGEP</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; color: #1a1f2b; background: #eef1f5; font-size: 12px; }
        .sheet { width: 210mm; min-height: 297mm; margin: 24px auto; background: #fff; padding: 16mm 14mm; box-shadow: 0 10px 30px rgba(15,23,42,.12); }
        .flag { display: flex; height: 4px; margin-bottom: 16px; }
        .flag span { flex: 1; }
        .flag span:nth-child(1) { background: #00853f; } .flag span:nth-child(2) { background: #fdcb0a; } .flag span:nth-child(3) { background: #e31b23; }
        .top { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; }
        .gov { line-height: 1.5; }
        .gov em { color: #64748b; }
        .logo img { height: 44px; }
        h1 { text-align: center; font-size: 19px; letter-spacing: .06em; text-transform: uppercase; color: #1a3a5c; margin: 22px 0 4px; }
        .ref { text-align: center; color: #64748b; margin-bottom: 20px; }
        h2 { font-size: 14px; color: #fff; background: #1a3a5c; padding: 7px 10px; border-radius: 6px; margin: 22px 0 10px; }
        h3 { font-size: 12.5px; color: #1a3a5c; margin: 14px 0 6px; }
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 8px; }
        .kpi { border: 1px solid #dfe5ec; border-radius: 8px; padding: 8px; text-align: center; }
        .kpi strong { display: block; font-size: 17px; color: #1a3a5c; }
        .kpi span { font-size: 10px; color: #64748b; }
        .kpi.bad strong { color: #b42318; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        th, td { border: 1px solid #dfe5ec; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f4f7fa; font-size: 9.5px; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        td.num, th.num { text-align: right; white-space: nowrap; }
        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .empty { color: #64748b; font-style: italic; padding: 4px 0; }
        .bar { height: 6px; background: #e6ebf1; border-radius: 4px; overflow: hidden; min-width: 60px; }
        .bar span { display: block; height: 100%; background: #1a3a5c; }
        .bad { color: #b42318; font-weight: 600; }
        .signs { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px; }
        .sign { border-top: 1px solid #94a3b8; padding-top: 8px; min-height: 80px; }
        .foot { margin-top: 26px; font-size: 10px; color: #94a3b8; text-align: center; }
        .toolbar { text-align: center; margin: 18px 0 0; }
        .toolbar button { font: inherit; font-weight: 600; background: #1a3a5c; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-size: 14px; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; box-shadow: none; width: auto; min-height: 0; padding: 0; }
            .toolbar { display: none; }
            @page { size: A4; margin: 12mm; }
            tr, .kpis { page-break-inside: avoid; }
            h2 { page-break-after: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $F = \App\Support\Fmt::class;
    $mrStatuses = \App\Models\MaintenanceRequest::STATUSES;
    $mrPriorities = \App\Models\MaintenanceRequest::PRIORITIES;
@endphp
<div class="toolbar"><button type="button" onclick="window.print()">Imprimer / enregistrer en PDF</button></div>

<div class="sheet">
    <div class="flag"><span></span><span></span><span></span></div>
    <div class="top">
        <div class="gov">
            <strong>République du Sénégal</strong><br>
            <em>Un Peuple – Un But – Une Foi</em><br>
            {{ $contact['organisation'] ?? '' }}<br>
            {{ $contact['service'] ?? '' }}
        </div>
        <div class="logo"><img src="{{ asset('img/logo.png') }}" alt="SYGEP"></div>
    </div>

    <h1>Rapport de gestion du patrimoine</h1>
    <p class="ref">{{ $label }} · {{ implode(' · ', $sections) }}</p>

    {{-- Matières --}}
    @isset($data['patrimoine'])
        @php($p = $data['patrimoine'])
        <h2>1. Matières et affectations</h2>
        <div class="kpis">
            <div class="kpi"><strong>{{ $p['total'] }}</strong><span>Matières au parc</span></div>
            <div class="kpi"><strong>{{ $p['new'] }}</strong><span>Nouvelles sur la période</span></div>
            <div class="kpi"><strong>{{ $p['assigned'] }}</strong><span>Affectées actuellement</span></div>
            <div class="kpi {{ $p['broken'] ? 'bad' : '' }}"><strong>{{ $p['broken'] }}</strong><span>En panne / en réparation</span></div>
        </div>
        <div class="kpis">
            <div class="kpi"><strong>{{ $p['assignments'] }}</strong><span>Bons d'affectation émis</span></div>
            <div class="kpi"><strong>{{ $p['returns'] }}</strong><span>Restitutions</span></div>
            <div class="kpi"><strong>{{ $p['transfers'] }}</strong><span>Transferts</span></div>
            <div class="kpi {{ $p['overdue'] ? 'bad' : '' }}"><strong>{{ $p['overdue'] }}</strong><span>Retours en retard</span></div>
        </div>
        <div class="two">
            <div>
                <h3>Répartition par état</h3>
                <table><tbody>
                    @foreach($p['by_status'] as $row)<tr><td>{{ $row['label'] }}</td><td class="num">{{ $row['total'] }}</td></tr>@endforeach
                </tbody></table>
            </div>
            <div>
                <h3>Principales catégories</h3>
                <table><tbody>
                    @foreach($p['by_category'] as $row)<tr><td>{{ $row->label }}</td><td class="num">{{ $row->total }}</td></tr>@endforeach
                </tbody></table>
            </div>
        </div>
        <h3>Inventaires clôturés sur la période</h3>
        @if($p['inventaires']->isEmpty())
            <p class="empty">Aucune campagne clôturée.</p>
        @else
            <table>
                <thead><tr><th>Campagne</th><th class="num">Attendues</th><th class="num">Contrôlées</th><th class="num">Manquantes</th><th class="num">Abîmées</th></tr></thead>
                <tbody>
                    @foreach($p['inventaires'] as $inv)
                        <tr>
                            <td>{{ $inv['reference'] }} — {{ $inv['nom'] }}</td>
                            <td class="num">{{ $inv['stats']['expected'] }}</td>
                            <td class="num">{{ $inv['stats']['checked'] }}</td>
                            <td class="num {{ $inv['stats']['missing'] ? 'bad' : '' }}">{{ $inv['stats']['missing'] }}</td>
                            <td class="num">{{ $inv['stats']['damaged'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endisset

    {{-- Stock --}}
    @isset($data['stock'])
        @php($s = $data['stock'])
        <h2>2. Stock des consommables</h2>
        <div class="kpis">
            <div class="kpi"><strong>{{ $s['items']->count() }}</strong><span>Articles gérés</span></div>
            <div class="kpi {{ $s['low']->count() ? 'bad' : '' }}"><strong>{{ $s['low']->count() }}</strong><span>Sous le seuil d'alerte</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($s['in_value']) }}</strong><span>Achats de la période</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($s['value']) }}</strong><span>Valeur du stock</span></div>
        </div>
        <h3>Mouvements par article</h3>
        @if($s['moving']->isEmpty())
            <p class="empty">Aucun mouvement sur la période.</p>
        @else
            <table>
                <thead><tr><th>Article</th><th class="num">Entrées</th><th class="num">Sorties</th><th class="num">Ajustements</th><th class="num">Stock actuel</th></tr></thead>
                <tbody>
                    @foreach($s['moving'] as $row)
                        <tr>
                            <td>{{ $row['item']->name }}</td>
                            <td class="num">{{ $F::qty($row['in']) }}</td>
                            <td class="num">{{ $F::qty($row['out']) }}</td>
                            <td class="num">{{ $row['adjust'] ? $F::qty($row['adjust']) : '—' }}</td>
                            <td class="num {{ $row['item']->level !== 'ok' ? 'bad' : '' }}">{{ $F::qty($row['item']->quantity) }} {{ $row['item']->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <div class="two">
            <div>
                <h3>Sorties par service</h3>
                @if($s['by_service']->isEmpty())
                    <p class="empty">Aucune sortie.</p>
                @else
                    <table><tbody>
                        @foreach($s['by_service'] as $row)<tr><td>{{ $row->label }}</td><td class="num">{{ $row->total }} sortie(s)</td></tr>@endforeach
                    </tbody></table>
                @endif
            </div>
            <div>
                <h3>Articles à réapprovisionner</h3>
                @if($s['low']->isEmpty())
                    <p class="empty">Aucun.</p>
                @else
                    <table><tbody>
                        @foreach($s['low'] as $item)<tr><td>{{ $item->name }}</td><td class="num bad">{{ $F::qty($item->quantity) }} / {{ $F::qty($item->min_quantity) }}</td></tr>@endforeach
                    </tbody></table>
                @endif
            </div>
        </div>
    @endisset

    {{-- Maintenance --}}
    @isset($data['maintenance'])
        @php($m = $data['maintenance'])
        <h2>3. Maintenance</h2>
        <div class="kpis">
            <div class="kpi"><strong>{{ $m['received'] }}</strong><span>Demandes reçues ({{ $m['corrective'] }} corr. / {{ $m['preventive'] }} prév.)</span></div>
            <div class="kpi"><strong>{{ $m['completed'] }}</strong><span>Interventions terminées</span></div>
            <div class="kpi"><strong>{{ $m['lead_time'] !== null ? $m['lead_time'].' j' : '—' }}</strong><span>Délai moyen de traitement</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($m['cost']) }}</strong><span>Coût des interventions</span></div>
        </div>
        <div class="kpis">
            <div class="kpi {{ $m['backlog'] ? 'bad' : '' }}"><strong>{{ $m['backlog'] }}</strong><span>Demandes en cours (à date)</span></div>
            <div class="kpi"><strong>{{ $m['to_validate'] }}</strong><span>En attente de validation</span></div>
            <div class="kpi {{ $m['plans_late'] ? 'bad' : '' }}"><strong>{{ $m['plans_late'] }}</strong><span>Entretiens préventifs en retard</span></div>
            <div class="kpi"><strong>{{ $m['by_priority']['urgente'] ?? 0 }}</strong><span>Demandes urgentes</span></div>
        </div>
        <h3>Demandes de la période</h3>
        @if($m['list']->isEmpty())
            <p class="empty">Aucune demande.</p>
        @else
            <table>
                <thead><tr><th>Réf.</th><th>Objet</th><th>Concerne</th><th>Priorité</th><th>Statut</th></tr></thead>
                <tbody>
                    @foreach($m['list'] as $r)
                        <tr>
                            <td style="white-space: nowrap">{{ $r->reference }}</td>
                            <td>{{ $r->title }}</td>
                            <td>{{ $r->target_label }}</td>
                            <td>{{ $mrPriorities[$r->priority] ?? $r->priority }}</td>
                            <td>{{ $mrStatuses[$r->status] ?? $r->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endisset

    {{-- Projets --}}
    @isset($data['projets'])
        @php($pr = $data['projets'])
        <h2>4. Projets de construction et de réhabilitation</h2>
        <div class="kpis">
            <div class="kpi"><strong>{{ $pr['active']->count() }}</strong><span>Projets en cours / planifiés</span></div>
            <div class="kpi {{ $pr['late'] ? 'bad' : '' }}"><strong>{{ $pr['late'] }}</strong><span>En retard</span></div>
            <div class="kpi"><strong>{{ $pr['completed']->count() }}</strong><span>Terminés sur la période</span></div>
            <div class="kpi"><strong>{{ $pr['milestones'] }}</strong><span>Jalons atteints</span></div>
        </div>
        @if($pr['active']->isEmpty())
            <p class="empty">Aucun projet en cours.</p>
        @else
            <table>
                <thead><tr><th>Projet</th><th>Échéance</th><th>Avancement</th><th class="num">Budget</th><th class="num">Engagé</th></tr></thead>
                <tbody>
                    @foreach($pr['active'] as $proj)
                        <tr>
                            <td>{{ $proj->reference }} — {{ $proj->name }}<br><span style="color:#64748b">{{ $proj->status_label }}{{ $proj->infrastructures->isNotEmpty() ? ' · '.$proj->infrastructures->pluck('name')->implode(', ') : '' }}</span></td>
                            <td class="{{ $proj->is_late ? 'bad' : '' }}" style="white-space: nowrap">{{ $proj->end_date?->format('d/m/Y') ?? '—' }}</td>
                            <td style="min-width: 90px">
                                <div class="bar"><span style="width: {{ $proj->progress }}%"></span></div>
                                {{ $proj->progress }} %@if($proj->expected_progress !== null) <span style="color:#64748b">(attendu {{ $proj->expected_progress }} %)</span>@endif
                            </td>
                            <td class="num">{{ $F::money($proj->budget) }}</td>
                            <td class="num">{{ $F::money($proj->spent) }}</td>
                        </tr>
                    @endforeach
                    <tr><th colspan="3">Total</th><th class="num">{{ $F::money($pr['budget']) }}</th><th class="num">{{ $F::money($pr['spent']) }}</th></tr>
                </tbody>
            </table>
        @endif
    @endisset

    {{-- Infrastructures --}}
    @isset($data['infrastructures'])
        @php($in = $data['infrastructures'])
        <h2>5. Infrastructures et amortissement</h2>
        <div class="kpis">
            <div class="kpi"><strong>{{ $in['total'] }}</strong><span>Infrastructures</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($in['gross']) }}</strong><span>Valeur d'origine</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($in['net']) }}</strong><span>Valeur nette comptable</span></div>
            <div class="kpi"><strong style="font-size: 13px">{{ $F::money($in['dotation']) }}</strong><span>Dotation de la période</span></div>
        </div>
        <div class="two">
            <div>
                <h3>Par situation</h3>
                <table><tbody>@foreach($in['by_status'] as $lbl => $n)<tr><td>{{ $lbl }}</td><td class="num">{{ $n }}</td></tr>@endforeach</tbody></table>
            </div>
            <div>
                <h3>Par état constaté</h3>
                <table><tbody>@foreach($in['by_condition'] as $lbl => $n)<tr><td>{{ $lbl }}</td><td class="num">{{ $n }}</td></tr>@endforeach</tbody></table>
            </div>
        </div>
        @if($in['degraded']->isNotEmpty())
            <h3>Infrastructures dégradées ou critiques</h3>
            <table><tbody>
                @foreach($in['degraded'] as $i)<tr><td>{{ $i->name }}</td><td>{{ $i->location }}</td><td class="bad">{{ $i->condition_label }}</td></tr>@endforeach
            </tbody></table>
        @endif
        @if($in['ending']->isNotEmpty())
            <h3>Fin d'amortissement dans les deux ans</h3>
            <table><tbody>
                @foreach($in['ending'] as $i)<tr><td>{{ $i->name }}</td><td class="num">{{ $i->depreciation_end->format('d/m/Y') }}</td></tr>@endforeach
            </tbody></table>
        @endif
    @endisset

    <div class="signs">
        <div class="sign"><strong>Établi par</strong><br>{{ auth()->user()->name }}</div>
        <div class="sign"><strong>Visa du Directeur</strong></div>
    </div>

    <p class="foot">Édité depuis SYGEP le {{ now()->format('d/m/Y à H:i') }}</p>
</div>
</body>
</html>
