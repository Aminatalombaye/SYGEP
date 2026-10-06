<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Bon {{ $assignment->reference }} | SYGEP</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,800&display=swap" rel="stylesheet" />
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; color: #1a1f2b; background: #eef1f5; font-size: 13px; }
        .sheet { width: 210mm; min-height: 297mm; margin: 24px auto; background: #fff; padding: 18mm 16mm; box-shadow: 0 10px 30px rgba(15,23,42,.12); }
        .flag { display: flex; height: 4px; margin-bottom: 18px; }
        .flag span { flex: 1; }
        .flag span:nth-child(1) { background: #00853f; } .flag span:nth-child(2) { background: #fdcb0a; } .flag span:nth-child(3) { background: #e31b23; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; }
        .gov { font-size: 12px; line-height: 1.5; }
        .gov strong { font-size: 13px; }
        .gov em { color: #64748b; }
        .logo img { height: 46px; }
        h1 { text-align: center; font-size: 20px; letter-spacing: .06em; text-transform: uppercase; color: #c2610f; margin: 26px 0 4px; }
        .ref { text-align: center; color: #64748b; margin-bottom: 22px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px; }
        .box { border: 1px solid #dfe5ec; border-radius: 8px; padding: 12px 14px; }
        .box h2 { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 8px; }
        .box p { line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th, td { border: 1px solid #dfe5ec; padding: 8px 10px; text-align: left; }
        th { background: #f4f7fa; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .notes { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 14px; margin-bottom: 22px; white-space: pre-line; }
        .engagement { font-size: 12px; color: #475569; line-height: 1.6; margin-bottom: 30px; }
        .signs { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .sign { border-top: 1px solid #94a3b8; padding-top: 8px; min-height: 90px; }
        .sign strong { display: block; margin-bottom: 2px; }
        .sign span { color: #64748b; font-size: 12px; }
        .foot { margin-top: 36px; font-size: 11px; color: #94a3b8; text-align: center; }
        .toolbar { text-align: center; margin: 18px 0 0; }
        .toolbar button { font: inherit; font-weight: 600; background: #c2610f; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .sheet { margin: 0; box-shadow: none; width: auto; min-height: 0; padding: 0; }
            .toolbar { display: none; }
            @page { size: A4; margin: 15mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Imprimer</button></div>

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

        <h1>Bon d'affectation de matériel</h1>
        <p class="ref">N° {{ $assignment->reference }} — {{ $assignment->assigned_at?->format('d/m/Y') }}</p>

        <div class="grid">
            <div class="box">
                <h2>Bénéficiaire</h2>
                <p>
                    <strong>{{ $assignment->beneficiary }}</strong><br>
                    Service : {{ $assignment->service->name ?? $assignment->agent?->service?->name ?? '—' }}<br>
                    @if($assignment->agent?->telephone)Tél. : {{ $assignment->agent->telephone }}<br>@endif
                    @if($assignment->agent?->email)E-mail : {{ $assignment->agent->email }}@endif
                </p>
            </div>
            <div class="box">
                <h2>Conditions</h2>
                <p>
                    Type : {{ $assignment->type_label }}<br>
                    Date d'affectation : {{ $assignment->assigned_at?->format('d/m/Y') ?? '—' }}<br>
                    Retour prévu : {{ $assignment->expected_return_at?->format('d/m/Y') ?? 'Sans date' }}<br>
                    Emplacement : {{ $assignment->location->name ?? '—' }}
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr><th style="width:36px">#</th><th>Code</th><th>Désignation</th><th>Catégorie</th><th>N° de série</th><th>Modèle</th></tr>
            </thead>
            <tbody>
                @foreach($assignment->matieres as $i => $asset)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $asset->qr_code ?: '—' }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '—' }}</td>
                        <td>{{ $asset->serial_number ?: '—' }}</td>
                        <td>{{ $asset->modele ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($assignment->notes)
            <div class="notes">{{ $assignment->notes }}</div>
        @endif

        <p class="engagement">
            Le bénéficiaire reconnaît avoir reçu le matériel ci-dessus en bon état de fonctionnement et s'engage
            à en assurer la garde, à l'utiliser exclusivement dans le cadre du service et à le restituer
            sur demande ou à la date prévue.
        </p>

        <div class="signs">
            <div class="sign">
                <strong>Remis par</strong>
                <span>{{ $assignment->createdBy->name ?? '' }}</span>
            </div>
            <div class="sign">
                <strong>Reçu par</strong>
                <span>{{ $assignment->beneficiary }}</span>
            </div>
        </div>

        <p class="foot">Édité depuis SYGEP le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
