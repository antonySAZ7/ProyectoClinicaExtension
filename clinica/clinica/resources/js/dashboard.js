/**
 * Gráficos del panel analítico (Persona 3).
 *
 * Lee los datos desde un <script type="application/json" id="analitica-data">
 * inyectado por dashboard.blade.php y dibuja cada gráfico solo si su <canvas>
 * existe en la página. Así este bundle solo se carga en /dashboard via @vite.
 */
import {
    Chart,
    LineController,
    BarController,
    DoughnutController,
    PieController,
    LineElement,
    BarElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(
    LineController,
    BarController,
    DoughnutController,
    PieController,
    LineElement,
    BarElement,
    PointElement,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler,
);

// Paleta consistente con el resto del panel (Tailwind: indigo/emerald/amber/rose/sky/gray).
const COLORES = {
    indigo: '#6366f1',
    emerald: '#10b981',
    amber: '#f59e0b',
    rose: '#f43f5e',
    sky: '#0ea5e9',
    gray: '#9ca3af',
    violet: '#8b5cf6',
    teal: '#14b8a6',
};

const PALETA = Object.values(COLORES);

const moneda = new Intl.NumberFormat('es-GT', {
    style: 'currency',
    currency: 'GTQ',
    minimumFractionDigits: 2,
});

Chart.defaults.font.family =
    'Figtree, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, sans-serif';
Chart.defaults.color = '#6b7280';

function leerDatos() {
    const nodo = document.getElementById('analitica-data');
    if (!nodo) {
        return null;
    }
    try {
        return JSON.parse(nodo.textContent);
    } catch (error) {
        console.error('No se pudo parsear la data analítica del panel:', error);
        return null;
    }
}

function ctx(id) {
    const el = document.getElementById(id);
    return el ? el.getContext('2d') : null;
}

function hayDatos(serie) {
    return Array.isArray(serie) && serie.some((v) => Number(v) > 0);
}

/** Marca un gráfico vacío mostrando su mensaje hermano "sin datos". */
function marcarVacio(canvasId) {
    const canvas = document.getElementById(canvasId);
    const vacio = canvas?.parentElement?.querySelector('[data-grafico-vacio]');
    if (canvas) canvas.classList.add('hidden');
    if (vacio) {
        // 'flex' se agrega solo aquí para que nunca compita con 'hidden' en el
        // estado normal (gráfico con datos): el mensaje permanece oculto.
        vacio.classList.remove('hidden');
        vacio.classList.add('flex');
    }
}

const SIN_LEYENDA = { plugins: { legend: { display: false } } };
const BASE = { responsive: true, maintainAspectRatio: false };

function render(datos) {
    // 1. Ingresos por mes (línea con relleno).
    if (ctx('grafico-ingresos') && hayDatos(datos.ingresos_por_mes.data)) {
        new Chart(ctx('grafico-ingresos'), {
            type: 'line',
            data: {
                labels: datos.ingresos_por_mes.labels,
                datasets: [
                    {
                        label: 'Ingresos cobrados',
                        data: datos.ingresos_por_mes.data,
                        borderColor: COLORES.emerald,
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    },
                ],
            },
            options: {
                ...BASE,
                ...SIN_LEYENDA,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => moneda.format(v) },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (c) => moneda.format(c.parsed.y) },
                    },
                },
            },
        });
    } else {
        marcarVacio('grafico-ingresos');
    }

    // 2. Distribución de estados de citas (doughnut).
    if (ctx('grafico-estados') && hayDatos(datos.distribucion_estados.data)) {
        new Chart(ctx('grafico-estados'), {
            type: 'doughnut',
            data: {
                labels: datos.distribucion_estados.labels,
                datasets: [
                    {
                        data: datos.distribucion_estados.data,
                        backgroundColor: [
                            COLORES.amber,
                            COLORES.emerald,
                            COLORES.sky,
                            COLORES.rose,
                            COLORES.gray,
                        ],
                        borderWidth: 0,
                    },
                ],
            },
            options: {
                ...BASE,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } },
            },
        });
    } else {
        marcarVacio('grafico-estados');
    }

    // 3. Tratamientos más frecuentes (barras horizontales).
    if (ctx('grafico-tratamientos') && hayDatos(datos.tratamientos.data)) {
        new Chart(ctx('grafico-tratamientos'), {
            type: 'bar',
            data: {
                labels: datos.tratamientos.labels,
                datasets: [
                    {
                        label: 'Veces indicado',
                        data: datos.tratamientos.data,
                        backgroundColor: COLORES.violet,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                ...BASE,
                ...SIN_LEYENDA,
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    } else {
        marcarVacio('grafico-tratamientos');
    }

    // 4. Ocupación de agenda: citas por día del periodo (barras).
    if (ctx('grafico-ocupacion') && hayDatos(datos.ocupacion.data)) {
        new Chart(ctx('grafico-ocupacion'), {
            type: 'bar',
            data: {
                labels: datos.ocupacion.labels,
                datasets: [
                    {
                        label: 'Citas',
                        data: datos.ocupacion.data,
                        backgroundColor: COLORES.indigo,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                ...BASE,
                ...SIN_LEYENDA,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    } else {
        marcarVacio('grafico-ocupacion');
    }

    // 5. Pacientes nuevos por mes (barras).
    if (ctx('grafico-pacientes') && hayDatos(datos.pacientes_por_mes.data)) {
        new Chart(ctx('grafico-pacientes'), {
            type: 'bar',
            data: {
                labels: datos.pacientes_por_mes.labels,
                datasets: [
                    {
                        label: 'Pacientes nuevos',
                        data: datos.pacientes_por_mes.data,
                        backgroundColor: COLORES.teal,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                ...BASE,
                ...SIN_LEYENDA,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    } else {
        marcarVacio('grafico-pacientes');
    }
}

const datos = leerDatos();
if (datos) {
    render(datos);
}
