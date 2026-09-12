<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Eye, TrendingUp, Globe, Calendar } from 'lucide-vue-next';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import { computed } from 'vue';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend, Filler);

interface DailyVisit { date: string; total: number }
interface CountryData { country: string; country_code: string; total: number }
interface SimpleData { device?: string; browser?: string; os?: string; total: number }
interface PageData { path: string; total: number }
interface RecentVisit { path: string; country: string; country_code: string; city: string; device: string; browser: string; os: string; visited_at: string }

const props = defineProps<{
    dailyVisits: DailyVisit[];
    countries: CountryData[];
    devices: SimpleData[];
    browsers: SimpleData[];
    os: SimpleData[];
    topPages: PageData[];
    recentVisits: RecentVisit[];
    totalVisits: number;
    todayVisits: number;
    weekVisits: number;
    uniqueCountries: number;
}>();

const statCards = [
    { label: 'Total kunjungan', value: props.totalVisits, icon: Eye, color: 'text-cyan-500' },
    { label: 'Hari ini', value: props.todayVisits, icon: TrendingUp, color: 'text-green-500' },
    { label: 'Minggu ini', value: props.weekVisits, icon: Calendar, color: 'text-blue-500' },
    { label: 'Negara', value: props.uniqueCountries, icon: Globe, color: 'text-purple-500' },
];

const chartColors = ['#06b6d4', '#8b5cf6', '#f59e0b', '#ef4444', '#10b981', '#ec4899', '#6366f1', '#14b8a6', '#f97316', '#84cc16'];

const lineChartData = computed(() => ({
    labels: props.dailyVisits.map(v => {
        const d = new Date(v.date);
        return `${d.getDate()}/${d.getMonth() + 1}`;
    }),
    datasets: [{
        label: 'Visitas',
        data: props.dailyVisits.map(v => v.total),
        borderColor: '#06b6d4',
        backgroundColor: 'rgba(6, 182, 212, 0.1)',
        fill: true,
        tension: 0.4,
        pointRadius: 2,
        pointHoverRadius: 5,
    }],
}));

const lineChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0, color: '#9ca3af' }, grid: { color: 'rgba(156,163,175,0.15)' } },
        x: { ticks: { color: '#9ca3af', maxTicksLimit: 15 }, grid: { display: false } },
    },
};

const deviceChartData = computed(() => ({
    labels: props.devices.map(d => d.device === 'desktop' ? 'Desktop' : d.device === 'mobile' ? 'Mobile' : 'Tablet'),
    datasets: [{ data: props.devices.map(d => d.total), backgroundColor: chartColors.slice(0, props.devices.length) }],
}));

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const, labels: { color: '#9ca3af', padding: 16 } } },
};

const countryChartData = computed(() => ({
    labels: props.countries.map(c => c.country),
    datasets: [{ label: 'Visitas', data: props.countries.map(c => c.total), backgroundColor: chartColors.slice(0, props.countries.length) }],
}));

const browserChartData = computed(() => ({
    labels: props.browsers.map(b => b.browser),
    datasets: [{ label: 'Visitas', data: props.browsers.map(b => b.total), backgroundColor: chartColors.slice(0, props.browsers.length) }],
}));

const osChartData = computed(() => ({
    labels: props.os.map(o => o.os),
    datasets: [{ data: props.os.map(o => o.total), backgroundColor: chartColors.slice(0, props.os.length) }],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y' as const,
    plugins: { legend: { display: false } },
    scales: {
        x: { beginAtZero: true, ticks: { precision: 0, color: '#9ca3af' }, grid: { color: 'rgba(156,163,175,0.15)' } },
        y: { ticks: { color: '#9ca3af' }, grid: { display: false } },
    },
};

function formatDate(dateStr: string) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Analitik" />
    <AdminLayout>
        <template #title>Analitik</template>

        <!-- Stat cards -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="card in statCards" :key="card.label" class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">{{ card.label }}</span>
                    <component :is="card.icon" :class="['h-5 w-5', card.color]" />
                </div>
                <p class="mt-2 text-3xl font-bold text-foreground">{{ card.value }}</p>
            </div>
        </div>

        <!-- Kunjungan 30 hari terakhir -->
        <div class="mt-6 rounded-xl border border-border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-medium text-muted-foreground">Kunjungan 30 hari terakhir</h3>
            <div class="h-64">
                <Line :data="lineChartData" :options="lineChartOptions" />
            </div>
        </div>

        <!-- Charts grid -->
        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <!-- Perangkat -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-medium text-muted-foreground">Perangkat</h3>
                <div class="h-56">
                    <Doughnut :data="deviceChartData" :options="doughnutOptions" />
                </div>
            </div>

            <!-- Negara -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-medium text-muted-foreground">Negara terbanyak</h3>
                <div class="h-56">
                    <Bar :data="countryChartData" :options="barOptions" />
                </div>
            </div>

            <!-- Browser -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-medium text-muted-foreground">Browser</h3>
                <div class="h-56">
                    <Bar :data="browserChartData" :options="barOptions" />
                </div>
            </div>

            <!-- Sistem operasi -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-medium text-muted-foreground">Sistem operasi</h3>
                <div class="h-56">
                    <Doughnut :data="osChartData" :options="doughnutOptions" />
                </div>
            </div>
        </div>

        <!-- Halaman paling banyak dikunjungi -->
        <div class="mt-6 rounded-xl border border-border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-medium text-muted-foreground">Halaman paling banyak dikunjungi</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th class="pb-3 font-medium">Halaman</th>
                            <th class="pb-3 text-right font-medium">Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="page in topPages" :key="page.path" class="border-b border-border/50">
                            <td class="py-2.5 text-foreground">{{ page.path }}</td>
                            <td class="py-2.5 text-right font-medium text-foreground">{{ page.total }}</td>
                        </tr>
                        <tr v-if="!topPages.length">
                            <td colspan="2" class="py-4 text-center text-muted-foreground">Belum ada data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kunjungan terbaru -->
        <div class="mt-6 rounded-xl border border-border bg-card p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-medium text-muted-foreground">Kunjungan terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-muted-foreground">
                            <th class="pb-3 font-medium">Halaman</th>
                            <th class="pb-3 font-medium">Negara</th>
                            <th class="pb-3 font-medium">Perangkat</th>
                            <th class="pb-3 font-medium">Browser</th>
                            <th class="pb-3 font-medium">OS</th>
                            <th class="pb-3 text-right font-medium">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(visit, i) in recentVisits" :key="i" class="border-b border-border/50">
                            <td class="py-2.5 text-foreground">{{ visit.path }}</td>
                            <td class="py-2.5 text-foreground">{{ visit.country || '—' }}</td>
                            <td class="py-2.5 capitalize text-foreground">{{ visit.device }}</td>
                            <td class="py-2.5 text-foreground">{{ visit.browser || '—' }}</td>
                            <td class="py-2.5 text-foreground">{{ visit.os || '—' }}</td>
                            <td class="py-2.5 text-right text-muted-foreground">{{ formatDate(visit.visited_at) }}</td>
                        </tr>
                        <tr v-if="!recentVisits.length">
                            <td colspan="6" class="py-4 text-center text-muted-foreground">Belum ada kunjungan tercatat</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
