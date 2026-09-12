<script setup lang="ts">
import { IconPhotoOff } from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

type Props = {
    /**
     * Path berkas. Boleh path relatif disk `public` (contoh: `seo/foto.jpg`)
     * atau URL penuh (contoh: blob pratinjau sebelum disimpan).
     */
    path?: string | null;
    alt?: string;
    /** Kelas untuk gambar sekaligus kotak penggantinya. */
    imageClass?: string;
    /** Tampilkan label teks di dalam kotak pengganti. */
    showLabel?: boolean;
    /** Teks saat berkas benar-benar tidak ada di server. */
    missingText?: string;
    /** Teks saat memang belum ada gambar. */
    emptyText?: string;
};

const props = withDefaults(defineProps<Props>(), {
    path: null,
    alt: '',
    imageClass: '',
    showLabel: true,
    missingText: 'Berkas hilang',
    emptyText: 'Belum ada gambar',
});

const failed = ref(false);

/** Hanya path relatif yang perlu ditambah prefix /storage. */
const src = computed(() => {
    const value = props.path || '';

    if (value.startsWith('blob:')) return value;
    if (value.startsWith('data:')) return value;
    if (value.startsWith('http')) return value;

    return `/storage/${value}`;
});

watch(
    () => props.path,
    () => {
        failed.value = false;
    },
);

const isMissing = computed(() => Boolean(props.path) && failed.value);
</script>

<template>
    <img
        v-if="path && !failed"
        :src="src"
        :alt="alt"
        :class="imageClass"
        @error="failed = true"
    />
    <div
        v-else
        :class="[
            imageClass,
            'flex flex-col items-center justify-center gap-1 border border-dashed text-center',
            isMissing
                ? 'border-destructive/50 bg-destructive/5 text-destructive'
                : 'border-border bg-muted/30 text-muted-foreground',
        ]"
        :title="
            isMissing ? `Berkas tidak ditemukan di server: ${path}` : undefined
        "
    >
        <IconPhotoOff class="h-4 w-4 shrink-0" :stroke-width="1.5" />
        <span v-if="showLabel" class="px-1 text-[10px] leading-tight">
            {{ isMissing ? missingText : emptyText }}
        </span>
    </div>
</template>
