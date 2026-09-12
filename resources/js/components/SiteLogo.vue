<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        imgClass?: string;
        alt?: string;
    }>(),
    {
        imgClass: 'h-8',
        alt: 'Logo',
    },
);

const page = usePage();

// Baca logo dari pengaturan (storage). Jika belum diunggah, pakai logo bawaan
// bertema kode di /public (SVG, tajam di semua ukuran & latar gelap).
const logo = computed(() => {
    const path = (page.props as any).logo?.light;
    return path ? `/storage/${path}` : '/logo-code.svg';
});
</script>

<template>
    <img :src="logo" :alt="alt" :class="imgClass" />
</template>
