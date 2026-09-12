<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { FolderKanban, FileText, Mail, MailWarning, Eye, TrendingUp } from 'lucide-vue-next';

const props = defineProps<{
    stats: {
        projects: number;
        blogs: number;
        contacts: number;
        unreadContacts: number;
        totalVisits: number;
        todayVisits: number;
    };
}>();

const cards = [
    { label: 'Proyek', value: props.stats.projects, icon: FolderKanban, color: 'text-blue-500' },
    { label: 'Blog', value: props.stats.blogs, icon: FileText, color: 'text-green-500' },
    { label: 'Kontak', value: props.stats.contacts, icon: Mail, color: 'text-purple-500' },
    { label: 'Belum dibaca', value: props.stats.unreadContacts, icon: MailWarning, color: 'text-orange-500' },
    { label: 'Total kunjungan', value: props.stats.totalVisits, icon: Eye, color: 'text-cyan-500' },
    { label: 'Kunjungan hari ini', value: props.stats.todayVisits, icon: TrendingUp, color: 'text-pink-500' },
];
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #title>Dashboard</template>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="card in cards"
                :key="card.label"
                class="rounded-xl border border-border bg-card p-6 shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">{{ card.label }}</span>
                    <component :is="card.icon" :class="['h-5 w-5', card.color]" />
                </div>
                <p class="mt-2 text-3xl font-bold text-foreground">{{ card.value }}</p>
            </div>
        </div>
    </AdminLayout>
</template>
