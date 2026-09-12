<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LayoutDashboard,
    FolderKanban,
    FileText,
    Award,
    Milestone,
    Mail,
    Settings,
    LogOut,
    Menu,
    X,
    BarChart3,
    UserCog,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref, computed, watch } from 'vue';
import SiteLogo from '@/components/SiteLogo.vue';

const page = usePage();
const sidebarOpen = ref(false);

const currentUrl = computed(() => page.url);

const navItems = [
    { title: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { title: 'Analitik', href: '/admin/analytics', icon: BarChart3 },
    { title: 'Proyek', href: '/admin/projects', icon: FolderKanban },
    { title: 'Blog', href: '/admin/blogs', icon: FileText },
    { title: 'Sertifikasi', href: '/admin/certifications', icon: Award },
    { title: 'Pengalaman', href: '/admin/experiences', icon: Milestone },
    { title: 'Kontak', href: '/admin/contacts', icon: Mail },
    { title: 'Pengaturan', href: '/admin/settings', icon: Settings },
    { title: 'Akun Saya', href: '/admin/profile', icon: UserCog },
];

function isActive(href: string) {
    if (href === '/admin') return currentUrl.value === '/admin';
    return currentUrl.value.startsWith(href);
}

function showSuccessAlert(message: string) {
    if (typeof window === 'undefined') return;

    const isDarkMode = document.documentElement.classList.contains('dark');

    Swal.fire({
        icon: 'success',
        title: 'Perubahan tersimpan',
        text: message,
        confirmButtonText: 'Tutup',
        confirmButtonColor: isDarkMode ? '#fafafa' : '#171717',
        background: isDarkMode ? '#0a0a0a' : '#ffffff',
        color: isDarkMode ? '#fafafa' : '#0a0a0a',
        customClass: {
            popup: 'rounded-xl border border-white/10',
        },
    });
}

watch(
    () => page.props.flash,
    (flash) => {
        const message = flash?.success;
        if (!message) return;
        showSuccessAlert(message);
    },
    { immediate: true },
);
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <!-- Mobile overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-border bg-card transition-transform lg:static lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center gap-3 border-b border-border px-6">
                <SiteLogo img-class="h-8" />
                <span class="text-lg font-semibold text-foreground">Admin</span>
            </div>

            <nav class="flex-1 space-y-1 p-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                        isActive(item.href)
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    ]"
                >
                    <component :is="item.icon" class="h-4 w-4" />
                    {{ item.title }}
                </Link>
            </nav>

            <div class="border-t border-border p-4">
                <Link
                    href="/"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                >
                    <LogOut class="h-4 w-4" />
                    Kembali ke situs
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col">
            <header class="flex h-16 items-center gap-4 border-b border-border px-6">
                <button
                    class="lg:hidden"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <Menu v-if="!sidebarOpen" class="h-5 w-5" />
                    <X v-else class="h-5 w-5" />
                </button>
                <h1 class="text-lg font-semibold text-foreground">
                    <slot name="title" />
                </h1>
            </header>

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
