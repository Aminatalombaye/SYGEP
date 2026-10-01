@php
    $overview = $moduleOverview ?? null;
    $create = $create ?? null;
@endphp

@if($head ?? true)
    <div class="page-head">
        <div>
            <h1>{{ $title }}</h1>
            @if(!empty($overview['subtitle']))
                <p class="sub">{{ $overview['subtitle'] }}</p>
            @endif
        </div>
        <div class="page-actions">
            @if($create)
                @can($create['can'])
                    <a href="{{ route($create['route']) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> {{ $create['label'] }}
                    </a>
                @endcan
            @endif
        </div>
    </div>
@endif

@if($overview)
    @if(!empty($overview['alerts']))
        <div class="sy-alerts">
            @foreach($overview['alerts'] as $alert)
                @if($alert['url'])
                    <a href="{{ $alert['url'] }}" class="sy-alert sy-alert-{{ $alert['tone'] }}">
                        <i class="bi {{ $alert['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $alert['text'] }}</span>
                        <i class="bi bi-arrow-right push-right" aria-hidden="true"></i>
                    </a>
                @else
                    <div class="sy-alert sy-alert-{{ $alert['tone'] }}">
                        <i class="bi {{ $alert['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $alert['text'] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if(!empty($overview['kpis']))
        <div class="kpi-grid kpi-auto" style="--kpi-cols: {{ count($overview['kpis']) }}">
            @foreach($overview['kpis'] as $kpi)
                <div class="kpi kpi-static {{ $kpi['tone'] ? 'kpi-'.$kpi['tone'] : '' }}">
                    <span class="kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                    <span class="kpi-body">
                        <span class="kpi-value">{{ number_format($kpi['value'], 0, ',', ' ') }}</span>
                        <span class="kpi-label">{{ $kpi['label'] }}</span>
                        @if($kpi['hint'])<span class="kpi-hint">{{ $kpi['hint'] }}</span>@endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($overview['charts']))
        <details class="overview-toggle" id="ov-toggle">
            <summary><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Graphiques <span class="muted">({{ count($overview['charts']) }})</span></summary>
            <div class="panel-grid overview-charts">
                @foreach($overview['charts'] as $chart)
                    <section class="panel {{ $chart['wide'] ? 'panel-wide' : '' }}">
                        <header class="panel-head">
                            <div>
                                <h2>{{ $chart['title'] }}</h2>
                                @if($chart['subtitle'])<p>{{ $chart['subtitle'] }}</p>@endif
                            </div>
                        </header>
                        <div class="chart-box" style="height: {{ $chart['type'] === 'hbar' ? max(140, min(count($chart['labels']), 10) * 26 + 36) : 180 }}px">
                            <canvas id="ov-{{ $chart['id'] }}" role="img" aria-label="{{ $chart['title'] }}"></canvas>
                        </div>
                    </section>
                @endforeach
            </div>
        </details>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var box = document.getElementById('ov-toggle');
                var drawn = false;
                var key = 'sygep-overview-charts';
                function draw() {
                    if (drawn || !window.SygepCharts) return;
                    drawn = true;
                    window.SygepCharts.render(@json($overview['charts']), 'ov-');
                }
                try { if (localStorage.getItem(key) === 'open') box.open = true; } catch (e) {}
                if (box.open) draw();
                box.addEventListener('toggle', function () {
                    try { localStorage.setItem(key, box.open ? 'open' : 'closed'); } catch (e) {}
                    if (box.open) draw();
                });
            });
        </script>
    @endif
@endif
