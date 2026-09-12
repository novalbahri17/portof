<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SiteLogo from '@/components/SiteLogo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh flex-col items-center justify-center bg-background p-6 md:p-10">

        <div class="relative z-20 w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link href="/" class="flex flex-col items-center gap-2">
                        <SiteLogo img-class="h-8" />
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-semibold tracking-tight text-foreground">{{ title }}</h1>
                        <p class="text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <div class="rounded-xl border border-border/50 bg-white/50 dark:bg-white/5 backdrop-blur-lg p-6">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
