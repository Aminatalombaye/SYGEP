@extends('layouts.admin')

@section('content')
<div class="page-head">
    <div>
        <h1>Scanner un QR code</h1>
        <p class="sub">Visez l'étiquette d'une matière : sa fiche s'ouvre automatiquement.</p>
    </div>
</div>

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
                    <p>Appuyez sur <strong>Démarrer</strong> et autorisez l'accès à la caméra.</p>
                </div>
            </div>
            <div class="scan-status" id="scan-status" role="status" aria-live="polite"></div>

            <div class="scan-alt">
                <label class="btn btn-default mb-0">
                    <i class="bi bi-image"></i> Prendre / choisir une photo
                    <input type="file" accept="image/*" capture="environment" id="scan-file" hidden>
                </label>
                <span class="hint">Si la caméra en direct n'est pas disponible (site non sécurisé, ancien téléphone).</span>
            </div>
        </div>
    </section>

    <div>
        <section class="sy-card">
            <div class="sy-card-head"><h2>Saisie manuelle</h2></div>
            <div class="sy-card-body">
                @if(session('qr_error'))
                    <div class="alert alert-danger">{{ session('qr_error') }}</div>
                @endif
                <form method="POST" action="{{ route('admin.qr.lookup') }}" id="lookup-form">
                    @csrf
                    <div class="form-group">
                        <label for="code">Code de l'étiquette ou numéro de série</label>
                        <input type="text" name="code" id="code" class="form-control" value="{{ old('code') }}" placeholder="SYGEP-MAT-000123" required autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
                </form>
            </div>
        </section>

        <section class="sy-card">
            <div class="sy-card-head"><h2>Conseils</h2></div>
            <div class="sy-card-body">
                <ul class="scan-tips">
                    <li><i class="bi bi-brightness-high"></i> Un bon éclairage et l'étiquette bien à plat.</li>
                    <li><i class="bi bi-arrows-angle-contract"></i> Tenez le téléphone à 15–25 cm de l'étiquette.</li>
                    <li><i class="bi bi-shield-lock"></i> La caméra en direct nécessite que SYGEP soit ouvert en <strong>https://</strong> (ou sur l'ordinateur local).</li>
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
    var readerEl = document.getElementById('qr-reader');
    var status = document.getElementById('scan-status');
    var startBtn = document.getElementById('scan-start');
    var stopBtn = document.getElementById('scan-stop');
    var fileInput = document.getElementById('scan-file');
    var scanner = null;
    var done = false;

    function say(text, tone) {
        status.textContent = text;
        status.className = 'scan-status' + (tone ? ' is-' + tone : '');
    }

    function handle(text) {
        if (done) return;
        done = true;
        say('Code lu : ' + text + ' — ouverture de la fiche…', 'good');
        if (navigator.vibrate) navigator.vibrate(120);
        if (/\/q\/[^\/?\s]+/.test(text)) {
            window.location.href = text.indexOf('http') === 0 ? text : '{{ url('/') }}' + text.substring(text.indexOf('/q/'));
            return;
        }
        document.getElementById('code').value = text;
        document.getElementById('lookup-form').submit();
    }

    function stop() {
        if (scanner && scanner.isScanning) {
            scanner.stop().catch(function () {});
        }
        startBtn.hidden = false;
        stopBtn.hidden = true;
    }

    startBtn.addEventListener('click', function () {
        if (!window.Html5Qrcode) { say('Le module de lecture n\'a pas pu être chargé.', 'critical'); return; }
        if (!window.isSecureContext) {
            say('La caméra en direct nécessite une connexion sécurisée (https). Utilisez « Prendre une photo ».', 'critical');
            return;
        }
        readerEl.innerHTML = '';
        done = false;
        scanner = scanner || new Html5Qrcode('qr-reader');
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: function (w, h) { var s = Math.floor(Math.min(w, h) * 0.7); return { width: s, height: s }; } },
            handle,
            function () {}
        ).then(function () {
            startBtn.hidden = true;
            stopBtn.hidden = false;
            say('Visez le QR code de l\'étiquette…');
        }).catch(function (err) {
            say('Impossible d\'accéder à la caméra : ' + err, 'critical');
        });
    });

    stopBtn.addEventListener('click', function () { stop(); say(''); });

    fileInput.addEventListener('change', function () {
        if (!fileInput.files.length) return;
        if (!window.Html5Qrcode) { say('Le module de lecture n\'a pas pu être chargé.', 'critical'); return; }
        stop();
        done = false;
        say('Analyse de la photo…');
        var fileScanner = new Html5Qrcode('qr-reader');
        fileScanner.scanFile(fileInput.files[0], false)
            .then(handle)
            .catch(function () { say('Aucun QR code détecté sur la photo. Réessayez plus près et avec plus de lumière.', 'critical'); })
            .finally(function () { fileInput.value = ''; });
    });

    window.addEventListener('beforeunload', stop);
})();
</script>
@endsection
