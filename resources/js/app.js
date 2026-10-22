import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Chart from 'chart.js/auto';

// Tombol tampilkan/sembunyikan kata sandi: <button data-toggle-password="id-input">
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-toggle-password]');
    if (!button) return;

    const input = document.getElementById(button.dataset.togglePassword);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
});

// Warna grafik diambil dari token CSS (--color-*) supaya tetap satu sumber dengan tokens.md.
const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(`--color-${name}`).trim();

Chart.defaults.font.family = "'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.color = token('muted-foreground');
Chart.defaults.plugins.legend.labels.boxWidth = 10;
Chart.defaults.plugins.legend.labels.boxHeight = 10;

/**
 * <canvas x-data="chart(@js($config))"> — config: { type, labels, datasets: [{ label, data, color }], stacked }
 * `color` adalah nama token (mis. 'chart-1') atau array nama token per batang/potongan.
 */
Alpine.data('chart', (config) => ({
    instance: null,

    init() {
        const color = (c) => (Array.isArray(c) ? c.map(token) : token(c));
        const isDoughnut = config.type === 'doughnut';

        this.instance = new Chart(this.$el, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: config.datasets.map((d) => ({
                    label: d.label,
                    data: d.data,
                    backgroundColor: color(d.color),
                    borderWidth: isDoughnut ? 2 : 0,
                    borderColor: token('card'),
                    borderRadius: isDoughnut ? 0 : 4,
                    maxBarThickness: 40,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: isDoughnut ? '70%' : undefined,
                plugins: {
                    legend: { display: config.datasets.length > 1 || isDoughnut, position: 'bottom' },
                },
                scales: isDoughnut ? {} : {
                    x: { stacked: config.stacked, grid: { display: false } },
                    y: { stacked: config.stacked, beginAtZero: true, ticks: { precision: 0 }, grid: { color: token('border') } },
                },
            },
        });
    },

    destroy() {
        this.instance?.destroy();
    },
}));

Livewire.start();
