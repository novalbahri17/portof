<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next';
import StorageImage from '@/components/StorageImage.vue';

/**
 * Daftar gambar yang SUDAH tersimpan di server, lengkap dengan tombol hapus.
 *
 * Dibuat terpisah dari `FileInput` supaya jelas bedanya:
 * - `FileInput` = berkas baru yang belum diunggah (tombol X = batalkan pilihan)
 * - `SavedImages` = berkas yang sudah ada di server (tombol hapus = buang sekarang)
 */
type Props = {
    /** Path berkas yang sudah tersimpan. */
    paths: string[];
    /** Judul kecil di atas daftar, mis. "Gambar tersimpan". */
    label?: string;
    alt?: string;
    /** Kelas ukuran tiap gambar. */
    imageClass?: string;
    /** Sembunyikan tombol hapus (mis. untuk pratinjau saja). */
    readonly?: boolean;
    /** Matikan tombol hapus saat ada proses berjalan. */
    disabled?: boolean;
    /** Teks konfirmasi sebelum menghapus. */
    confirmText?: string;
};

const props = withDefaults(defineProps<Props>(), {
    label: undefined,
    alt: 'Gambar tersimpan',
    imageClass: 'h-20 w-28',
    readonly: false,
    disabled: false,
    confirmText: 'Hapus gambar ini dari server?',
});

const emit = defineEmits<{ remove: [path: string] }>();

function onRemove(path: string) {
    if (props.disabled) return;

    if (!window.confirm(props.confirmText)) return;

    emit('remove', path);
}
</script>

<template>
    <div v-if="paths.length" class="space-y-2">
        <p v-if="label" class="text-xs font-medium text-muted-foreground">
            {{ label }} ({{ paths.length }})
        </p>

        <div class="flex flex-wrap gap-3">
            <div v-for="path in paths" :key="path" class="group relative">
                <StorageImage
                    :path="path"
                    :alt="alt"
                    :show-label="false"
                    :image-class="`${imageClass} rounded-lg border border-border object-cover`"
                />

                <button
                    v-if="!readonly"
                    type="button"
                    :title="confirmText"
                    :disabled="disabled"
                    aria-label="Hapus gambar"
                    class="absolute -top-1.5 -right-1.5 cursor-pointer rounded-full bg-destructive p-1 text-white shadow-sm transition-opacity disabled:cursor-not-allowed disabled:opacity-50 sm:opacity-0 sm:group-hover:opacity-100 sm:focus:opacity-100"
                    @click="onRemove(path)"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </button>

                <p
                    class="mt-1 max-w-28 truncate text-[10px] text-muted-foreground"
                    :title="path"
                >
                    {{ path.split('/').pop() }}
                </p>
            </div>
        </div>
    </div>
</template>
