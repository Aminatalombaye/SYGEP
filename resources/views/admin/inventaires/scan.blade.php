@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <div class="crumb">
            <a href="{{ route('admin.inventaires.index') }}">{{ trans('cruds.inventaire.title') }}</a> ›
            <a href="{{ route('admin.inventaires.show', $inventaire) }}">{{ $inventaire->reference }}</a> › Scanner
        </div>
        <h1>Contrôle par scan</h1>
        <p class="sub">{{ $inventaire->nom }} · {{ $inventaire->scope_label }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('admin.inventaires.show', $inventaire) }}" class="btn btn-default"><i class="bi bi-arrow-left"></i> Retour à la campagne</a>
    </div>
</div>

<section class="sy-card">
    <div class="sy-card-body">
        <div class="inv-progress-head">
            <strong>Avancement</strong>
            <span><span data-checked>{{ $stats['checked'] }}</span> / <span data-expected>{{ $stats['expected'] }}</span> · <span data-progress>{{ $stats['progress'] }}</span> %</span>
        </div>
        <div class="inv-progress"><span data-bar style="width: {{ $stats['progress'] }}%"></span></div>
    </div>
</section>

<div class="sy-grid-2 scanner-layout">
    <section class="sy-card">
        <div class="sy-card-head">
            <h2>Caméra</h2>
            <div class="page-actions">
                <button type="button" class="btn btn-primary" id="scan-start"><i class="bi bi-camera-video"></i> Démarrer</button>
                <button type="button" class="btn btn-default" id="scan-stop" hidden><i class="bi bi-stop-circle"></i> Arrêter</button>
            </div>
        </div>
        <div class="sy-card-body">
            <div id="qr-reader" class="qr-reader">
                <div class="qr-placeholder">
                    <i class="bi bi-qr-code-scan"></i>
                    <p>Scannez les étiquettes les unes après les autres : chaque matière est pointée automatiquement.</p>
                </div>
            </div>
            <div class="scan-alt">
                <label class="btn btn-default mb-0">
                    <i class="bi bi-image"></i> Prendre / choisir une photo
                    <input type="file" accept="image/*" capture="environment" id="scan-file" hidden>
                </label>
                <span class="hint">Si la caméra en direct n'est pas disponible.</span>
            </div>
        </div>
    </section>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Constat</h2></div>
            <div class="sy-card-body">
                <div class="form-group">
                    <label for="condition">État constaté</label>
                    <select id="condition" class="form-control">
                        @foreach(\App\Models\Inventaire::CONDITIONS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="hint">Appliqué aux prochains scans. Repassez sur « Bon état » ensuite.</span>
                </div>
                <div class="form-group">
                    <label for="location_id">Lieu du contrôle</label>
                    <select id="location_id" class="form-control select2">
                        <option value="">{{ $inventaire->location ? $inventaire->location->name.' (périmètre)' : 'Emplacement enregistré de la matière' }}</option>
                        @foreach($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="hint">Indiquez la salle où vous êtes pour repérer les matières déplacées.</span>
                </div>
                <form id="manual-form" class="form-group mb-0">
                    <label for="manual-code">Saisie manuelle</label>
                    <div class="input-group">
                        <input type="text" id="manual-code" class="form-control" placeholder="Code, n° de série ou ID" autocomplete="off">
                        <div class="input-group-append"><button class="btn btn-primary" type="submit">Pointer</button></div>
                    </div>
                </form>
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Derniers scans</h2></div>
            <div class="sy-card-body flush">
                <ul class="scan-feed" id="scan-feed">
                    <li class="scan-feed-empty">Aucun scan pour le moment.</li>
                </ul>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="{{ asset('vendor/html5-qrcode/html5-qrcode.min.js') }}"></script>
<script>
(function () {
    var endpoint = @json(route('admin.inventaires.record', $inventaire));
    var startBtn = document.getElementById('scan-start');
    var stopBtn = document.getElementById('scan-stop');
    var feed = document.getElementById('scan-feed');
    var scanner = null;
    var busy = false;
    var lastCode = null, lastTime = 0;

    var icons = { ok: 'bi-check-circle-fill', duplicate: 'bi-info-circle-fill', outside: 'bi-exclamation-triangle-fill', unknown: 'bi-x-circle-fill', error: 'bi-x-circle-fill' };

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]; });
    }

    function addFeed(result, message) {
        var empty = feed.querySelector('.scan-feed-empty');
        if (empty) empty.remove();
        var li = document.createElement('li');
        li.className = 'scan-feed-item is-' + result;
        li.innerHTML = '<i class="bi ' + (icons[result] || 'bi-dot') + '"></i><span>' + escapeHtml(message) +
            '<small>' + new Date().toLocaleTimeString('fr-FR') + '</small></span>';
        feed.insertBefore(li, feed.firstChild);
        while (feed.children.length > 25) feed.removeChild(feed.lastChild);
    }

    function updateStats(stats) {
        if (!stats) return;
        document.querySelector('[data-checked]').textContent = stats.checked;
        document.querySelector('[data-expected]').textContent = stats.expected;
        document.querySelector('[data-progress]').textContent = stats.progress;
        document.querySelector('[data-bar]').style.width = stats.progress + '%';
    }

    function send(code) {
        code = (code || '').trim();
        if (!code || busy) return;
        var now = Date.now();
        if (code === lastCode && now - lastTime < 3000) return;
        lastCode = code; lastTime = now;
        busy = true;

        $.ajax({
            url: endpoint,
            method: 'POST',
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': _token, 'Accept': 'application/json' },
            data: { code: code, condition: $('#condition').val(), location_id: $('#location_id').val() }
        }).done(function (res) {
            addFeed(res.result, res.message);
            updateStats(res.stats);
            if (navigator.vibrate) navigator.vibrate(res.result === 'ok' ? 80 : [60, 60, 60]);
        }).fail(function (xhr) {
            addFeed('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Erreur lors de l\'enregistrement.');
        }).always(function () { busy = false; });
    }

    startBtn.addEventListener('click', function () {
        if (!window.Html5Qrcode) { addFeed('error', 'Le module de lecture n\'a pas pu être chargé.'); return; }
        if (!window.isSecureContext) { addFeed('error', 'La caméra en direct nécessite https. Utilisez « Prendre une photo ».'); return; }
        document.getElementById('qr-reader').innerHTML = '';
        scanner = scanner || new Html5Qrcode('qr-reader');
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: function (w, h) { var s = Math.floor(Math.min(w, h) * 0.7); return { width: s, height: s }; } },
            send,
            function () {}
        ).then(function () {
            startBtn.hidden = true; stopBtn.hidden = false;
        }).catch(function (err) { addFeed('error', 'Caméra inaccessible : ' + err); });
    });

    stopBtn.addEventListener('click', function () {
        if (scanner && scanner.isScanning) scanner.stop().catch(function () {});
        startBtn.hidden = false; stopBtn.hidden = true;
    });

    document.getElementById('scan-file').addEventListener('change', function () {
        var input = this;
        if (!input.files.length || !window.Html5Qrcode) return;
        new Html5Qrcode('qr-reader').scanFile(input.files[0], false)
            .then(send)
            .catch(function () { addFeed('unknown', 'Aucun QR code détecté sur la photo.'); })
            .finally(function () { input.value = ''; });
    });

    document.getElementById('manual-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var field = document.getElementById('manual-code');
        lastCode = null;
        send(field.value);
        field.value = '';
        field.focus();
    });

    window.addEventListener('beforeunload', function () {
        if (scanner && scanner.isScanning) scanner.stop().catch(function () {});
    });
})();
</script>
@endsection
