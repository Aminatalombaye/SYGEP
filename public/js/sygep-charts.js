(function () {
    var ink = {
        series: '#1a3a5c',
        hover: '#2a78d6',
        text: '#52514e',
        muted: '#8a8f98',
        grid: 'rgba(15, 23, 42, 0.06)'
    };

    function configure() {
        if (!window.Chart || configure.done) return;
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size = 13;
        Chart.defaults.color = ink.text;
        configure.done = true;
    }

    function empty(canvas) {
        canvas.parentNode.innerHTML = '<p class="chart-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i> Pas encore de données</p>';
    }

    function bar(canvas, data, horizontal) {
        var values = data.values || [];
        var total = values.reduce(function (a, b) { return a + b; }, 0);
        if (!values.length || total === 0) { empty(canvas); return; }

        var valueAxis = {
            beginAtZero: true,
            ticks: { precision: 0, color: ink.muted },
            grid: { color: ink.grid, drawTicks: false },
            border: { display: false }
        };
        var categoryAxis = {
            ticks: {
                color: ink.text,
                autoSkip: !horizontal,
                maxRotation: 0,
                callback: function (value) {
                    var label = this.getLabelForValue(value);
                    return label && label.length > 28 ? label.slice(0, 27) + '…' : label;
                }
            },
            grid: { display: false },
            border: { color: 'rgba(15, 23, 42, 0.15)' }
        };

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    data: values,
                    backgroundColor: ink.series,
                    hoverBackgroundColor: ink.hover,
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
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f1f33',
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function (ctx) {
                                var v = horizontal ? ctx.parsed.x : ctx.parsed.y;
                                return v + (v > 1 ? ' éléments' : ' élément');
                            }
                        }
                    }
                },
                scales: horizontal ? { x: valueAxis, y: categoryAxis } : { x: categoryAxis, y: valueAxis }
            }
        });
    }

    window.SygepCharts = {
        render: function (charts, prefix) {
            configure();
            if (!window.Chart) return;
            (charts || []).forEach(function (chart) {
                var canvas = document.getElementById((prefix || '') + chart.id);
                if (canvas) bar(canvas, chart, chart.type === 'hbar');
            });
        },
        bar: function (canvas, data, horizontal) {
            configure();
            if (window.Chart && canvas) bar(canvas, data, horizontal);
        }
    };
})();
