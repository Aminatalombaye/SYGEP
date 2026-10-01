<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Étiquette {{ $asset->qr_code }} | SYGEP</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', Arial, sans-serif; background: #eef1f5; color: #1a1f2b; }
        .toolbar { text-align: center; padding: 18px; }
        .toolbar button { font: inherit; font-weight: 600; background: #1a3a5c; color: #fff; border: 0; border-radius: 8px; padding: 10px 18px; cursor: pointer; }
        .label {
            width: 70mm; margin: 10px auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 3mm;
            padding: 4mm; display: flex; gap: 3mm; align-items: center;
        }
        .label .qr svg { width: 26mm; height: 26mm; display: block; }
        .label .info { flex: 1; min-width: 0; }
        .label .flag { display: flex; height: 1.2mm; margin-bottom: 1.5mm; }
        .label .flag span { flex: 1; }
        .label .flag span:nth-child(1) { background: #00853f; } .label .flag span:nth-child(2) { background: #fdcb0a; } .label .flag span:nth-child(3) { background: #e31b23; }
        .label .org { font-size: 6.5pt; font-weight: 700; color: #1a3a5c; text-transform: uppercase; letter-spacing: .04em; }
        .label .name { font-size: 9pt; font-weight: 800; margin: 1mm 0; line-height: 1.15; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .label .meta { font-size: 6.5pt; color: #475569; line-height: 1.35; }
        .label .code { font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 7pt; font-weight: 700; color: #1a3a5c; margin-top: 1mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .label { margin: 0; border-color: #94a3b8; }
            @page { size: auto; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Imprimer l'étiquette</button></div>
    <div class="label">
        <div class="qr">{{ $asset->qrSvg(200) }}</div>
        <div class="info">
            <div class="flag"><span></span><span></span><span></span></div>
            <div class="org">Propriété du MEFPT</div>
            <div class="name">{{ $asset->name }}</div>
            <div class="meta">
                {{ $asset->category->name ?? '' }}
                @if($asset->serial_number)<br>N° {{ $asset->serial_number }}@endif
            </div>
            <div class="code">{{ $asset->qr_code }}</div>
        </div>
    </div>
</body>
</html>
