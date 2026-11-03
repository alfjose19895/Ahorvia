import './bootstrap';
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
);

window.Chart = Chart;

window.ahorviaTheme = {
    current() {
        return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    },
    toggle() {
        const isDark = document.documentElement.classList.toggle('dark');
        const next = isDark ? 'dark' : 'light';
        localStorage.setItem('ahorvia_theme', next);
        window.dispatchEvent(new CustomEvent('ahorvia-theme-changed', { detail: { theme: next } }));
        return next;
    },
};

window.ahorviaEvolutionChart = function (series = []) {
    return {
        chart: null,
        init() {
            const canvas = this.$refs.canvas;
            if (!canvas || typeof window.Chart === 'undefined') {
                return;
            }

            const renderChart = () => {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.06)';
                const tickColor = isDark ? '#94A3B8' : '#64748B';

                if (this.chart) {
                    this.chart.destroy();
                }

                this.chart = new window.Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: series.map((item) => item.month),
                        datasets: [
                            {
                                label: 'Ingresos',
                                data: series.map((item) => item.income),
                                backgroundColor: 'rgba(16, 185, 129, 0.82)',
                                borderRadius: 6,
                                maxBarThickness: 22,
                            },
                            {
                                label: 'Gastos',
                                data: series.map((item) => item.expense),
                                backgroundColor: 'rgba(239, 68, 68, 0.78)',
                                borderRadius: 6,
                                maxBarThickness: 22,
                            },
                            {
                                type: 'line',
                                label: 'Ahorro',
                                data: series.map((item) => item.savings),
                                borderColor: '#064E3B',
                                backgroundColor: 'rgba(16, 185, 129, 0.15)',
                                borderWidth: 2.5,
                                tension: 0.35,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: {
                                    color: tickColor,
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: { size: 12 },
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: tickColor, font: { size: 11 } },
                            },
                            y: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                grid: { color: gridColor },
                                ticks: { color: tickColor, font: { size: 11 } },
                            },
                        },
                    },
                });
            };

            renderChart();
            window.addEventListener('ahorvia-theme-changed', renderChart);
        },
    };
};

window.ahorviaCashFlowReportChart = function (labels = [], incomes = [], expenses = [], netSavings = []) {
    return {
        chart: null,
        init() {
            const canvas = this.$refs.canvas;
            if (!canvas || typeof window.Chart === 'undefined') return;

            const render = () => {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.06)';
                const tickColor = isDark ? '#94A3B8' : '#64748B';

                if (this.chart) this.chart.destroy();

                this.chart = new window.Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Ingresos',
                                data: incomes,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)',
                                borderRadius: 6,
                                maxBarThickness: 26,
                            },
                            {
                                label: 'Gastos',
                                data: expenses,
                                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                borderRadius: 6,
                                maxBarThickness: 26,
                            },
                            {
                                type: 'line',
                                label: 'Flujo Neto',
                                data: netSavings,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                borderWidth: 2,
                                tension: 0.3,
                                pointRadius: 4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end',
                                labels: { color: tickColor, font: { size: 11 }, usePointStyle: true },
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true },
                        },
                    },
                });
            };

            render();
            window.addEventListener('ahorvia-theme-changed', render);
        },
    };
};

window.ahorviaCategoryDoughnutReportChart = function (labels = [], values = [], colors = []) {
    return {
        chart: null,
        init() {
            const canvas = this.$refs.canvas;
            if (!canvas || typeof window.Chart === 'undefined') return;

            const render = () => {
                const isDark = document.documentElement.classList.contains('dark');
                const tickColor = isDark ? '#94A3B8' : '#64748B';

                if (this.chart) this.chart.destroy();

                this.chart = new window.Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                data: values,
                                backgroundColor: colors.length > 0 ? colors : ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6'],
                                borderWidth: 2,
                                borderColor: isDark ? '#0F172A' : '#FFFFFF',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: tickColor, font: { size: 11 }, usePointStyle: true, boxWidth: 8 },
                            },
                        },
                        cutout: '68%',
                    },
                });
            };

            render();
            window.addEventListener('ahorvia-theme-changed', render);
        },
    };
};

window.ahorviaNetWorthReportChart = function (labels = [], values = []) {
    return {
        chart: null,
        init() {
            const canvas = this.$refs.canvas;
            if (!canvas || typeof window.Chart === 'undefined') return;

            const render = () => {
                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.06)';
                const tickColor = isDark ? '#94A3B8' : '#64748B';

                if (this.chart) this.chart.destroy();

                this.chart = new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Patrimonio Neto',
                                data: values,
                                borderColor: '#064E3B',
                                backgroundColor: isDark ? 'rgba(16, 185, 129, 0.2)' : 'rgba(16, 185, 129, 0.12)',
                                fill: true,
                                borderWidth: 3,
                                tension: 0.35,
                                pointRadius: 4,
                                pointBackgroundColor: '#10B981',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                            y: { grid: { color: gridColor }, ticks: { color: tickColor, font: { size: 11 } } },
                        },
                    },
                });
            };

            render();
            window.addEventListener('ahorvia-theme-changed', render);
        },
    };
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Registro silencioso: en entornos locales HTTP no seguros puede omitirse sin afectar la app.
        });
    });
}
