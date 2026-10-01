<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Procès-verbal {{ $inventaire->reference }} | SYGEP</title>
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
        .ref { text-align: center; color: #64748b; margin-bottom: 18px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .box { border: 1px solid #dfe5ec; border-radius: 8px; padding: 10px 12px; line-height: 1.6; }
        .box h2 { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 6px; }
        .kpis { display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px; margin-bottom: 18px; }
        .kpi { border: 1px solid #dfe5ec; border-radius: 8px; padding: 8px; text-align: center; }
        .kpi strong { display: block; font-size: 18px; color: #1a3a5c; }
        .kpi span { font-size: 10px; color: #64748b; }
        h3 { font-size: 13px; color: #1a3a5c; margin: 16px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dfe5ec; padding: 5px 7px; text-align: left; vertical-align: top; }
        th { background: #f4f7fa; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .empty { color: #64748b; font-style: italic; padding: 6px 0; }
        .signs { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 28px; }
        .sign { border-top: 1px solid #94a3b8; padding-top: 8px; min-height: 80px; }
        .foot { margin-top: 26px; font-size: 10px; color: #94a3b8; text-align: center; }
        .toolbar { text-align: center; margin: 18px 0 0; }
        .toolbar button { font: inherit; font-weight: 600; background: #1a3a5c; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer; font-size: 14px; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; box-shadow: none; width: auto; min-height: 0; padding: 0; }
            .toolbar { display: none; }
            @page { size: A4; margin: 12mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
@php
    $conditions = \App\Models\Inventaire::CONDITIONS;
    $missing = $assets->where('pivot.status', 'manquant');
    $pending = $assets->where('pivot.status', 'attendu');
    $outside = $assets->where('pivot.status', 'hors_perimetre');
    $damaged = $assets->filter(fn ($a) => in_array($a->pivot->condition, ['abime', 'hors_service']));
    $moved = $assets->filter(fn ($a) => $a->pivot->found_location_id && $a->pivot->expected_location_id && (int) $a->pivot->found_location_id !== (int) $a->pivot->expected_location_id);
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

        <h1>Procès-verbal d'inventaire</h1>
        <p class="ref">{{ $inventaire->reference }} — {{ $inventaire->nom }}{{ $inventaire->isClosed() ? '' : ' (campagne en cours, document provisoire)' }}</p>

        <div class="grid">
            <div class="box">
                <h2>Campagne</h2>
                Périmètre : {{ $inventaire->scope_label }}<br>
                Période : {{ $inventaire->starts_at?->format('d/m/Y') ?? '…' }} → {{ $inventaire->ends_at?->format('d/m/Y') ?? '…' }}<br>
                Démarrée le : {{ $inventaire->started_at?->format('d/m/Y à H:i') ?? '—' }}
            </div>
            <div class="box">
                <h2>Responsables</h2>
                Créée par : {{ $inventaire->createdBy->name ?? '—' }}<br>
                Clôturée par : {{ $inventaire->closedBy->name ?? '—' }}<br>
                Clôturée le : {{ $inventaire->closed_at?->format('d/m/Y à H:i') ?? '—' }}
            </div>
        </div>

        <div class="kpis">
            <div class="kpi"><strong>{{ $stats['expected'] }}</strong><span>Attendues</span></div>
            <div class="kpi"><strong>{{ $stats['checked'] }}</strong><span>Contrôlées ({{ $stats['progress'] }} %)</span></div>
            <div class="kpi"><strong>{{ $inventaire->isClosed() ? $stats['missing'] : $stats['pending'] }}</strong><span>{{ $inventaire->isClosed() ? 'Manquantes' : 'Non contrôlées' }}</span></div>
            <div class="kpi"><strong>{{ $stats['damaged'] }}</strong><span>Abîmées / HS</span></div>
            <div class="kpi"><strong>{{ $stats['moved'] + $stats['outside'] }}</strong><span>Déplacées / hors périmètre</span></div>
        </div>

        <h3>{{ $inventaire->isClosed() ? 'Matières manquantes' : 'Matières non encore contrôlées' }}</h3>
        @php($list = $inventaire->isClosed() ? $missing : $pending)
        @if($list->isEmpty())
            <p class="empty">Aucune.</p>
        @else
            <table>
                <thead><tr><th>Désignation</th><th>Code</th><th>N° de série</th><th>Emplacement prévu</th></tr></thead>
                <tbody>
                    @foreach($list as $a)
                        <tr><td>{{ $a->name }}</td><td>{{ $a->qr_code }}</td><td>{{ $a->serial_number ?: '—' }}</td><td>{{ $locations[$a->pivot->expected_location_id] ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h3>Matières abîmées ou hors service</h3>
        @if($damaged->isEmpty())
            <p class="empty">Aucune.</p>
        @else
            <table>
                <thead><tr><th>Désignation</th><th>Code</th><th>État constaté</th><th>Observations</th></tr></thead>
                <tbody>
                    @foreach($damaged as $a)
                        <tr><td>{{ $a->name }}</td><td>{{ $a->qr_code }}</td><td>{{ $conditions[$a->pivot->condition] ?? '' }}</td><td>{{ $a->pivot->notes ?: '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h3>Matières déplacées ou trouvées hors périmètre</h3>
        @if($moved->isEmpty() && $outside->isEmpty())
            <p class="empty">Aucune.</p>
        @else
            <table>
                <thead><tr><th>Désignation</th><th>Code</th><th>Emplacement prévu</th><th>Trouvée à</th></tr></thead>
                <tbody>
                    @foreach($moved->merge($outside)->unique('id') as $a)
                        <tr><td>{{ $a->name }}</td><td>{{ $a->qr_code }}</td><td>{{ $locations[$a->pivot->expected_location_id] ?? '—' }}</td><td>{{ $locations[$a->pivot->found_location_id] ?? '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="signs">
            <div class="sign"><strong>Le responsable de l'inventaire</strong></div>
            <div class="sign"><strong>Visa du chef de service</strong></div>
        </div>

        <p class="foot">Édité depuis SYGEP le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
