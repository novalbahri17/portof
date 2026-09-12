<script setup lang="ts">
import { Upload, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';

type Props = {
    label?: string;
    accept?: string;
    multiple?: boolean;
    hint?: string;
    error?: string;
    /** Tampilkan nama berkas yang dipilih di samping tombol. */
    showSelection?: boolean;
    /** Teks saat belum ada berkas yang dipilih. */
    emptyText?: string;
    /**
     * Tampilkan pratinjau berkas yang baru dipilih.
     * Matikan kalau induk sudah punya pratinjau sendiri.
     */
    showPreview?: boolean;
    /** Kelas kotak tiap pratinjau. */
    previewClass?: string;
    /** Kelas gambar pratinjau (kalau berbeda dari kotak). */
    previewImageClass?: string;
};

const props = withDefaults(defineProps<Props>(), {
    label: 'Pilih berkas',
    accept: undefined,
    multiple: false,
    hint: undefined,
    error: undefined,
    showSelection: true,
    emptyText: 'Belum ada berkas dipilih',
    showPreview: true,
    previewClass: 'h-16 w-24',
    previewImageClass: undefined,
});

const emit = defineEmits<{
    change: [event: Event];
    remove: [index: number];
}>();

const input = ref<HTMLInputElement | null>(null);
const names = ref<string[]>([]);

type Item = {
    name: string;
    url: string | null;
    isImage: boolean;
    size: string;
    file: File;
};

const items = ref<Item[]>([]);

const selectionText = computed(() => {
    if (names.value.length === 0) return props.emptyText;
    if (names.value.length === 1) return names.value[0];

    return `${names.value.length} berkas dipilih`;
});

function isImageFile(file: File): boolean {
    if (file.type) return file.type.startsWith('image/');

    return /\.(jpe?g|png|gif|webp|avif|bmp|svg)$/i.test(file.name);
}

function formatSize(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/** Lepaskan object URL supaya memori browser tidak menumpuk. */
function releaseItems() {
    items.value.forEach((item) => {
        if (item.url) URL.revokeObjectURL(item.url);
    });
}

function pick() {
    // Kosongkan nilai input agar berkas yang sama bisa dipilih ulang.
    if (input.value) input.value.value = '';
    input.value?.click();
}

function buildItems(files: File[]): Item[] {
    return files.map((file) => {
        const isImage = isImageFile(file);

        return {
            name: file.name,
            url: isImage ? URL.createObjectURL(file) : null,
            isImage,
            size: formatSize(file.size),
            file,
        };
    });
}

function onChange(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);

    releaseItems();

    names.value = files.map((file) => file.name);
    items.value = buildItems(files);

    emit('change', event);
}

/**
 * Buang satu berkas dari pilihan. Karena input asli tidak bisa diedit,
 * kita bangun ulang daftar berkasnya lalu kirim event sintetis ke induk.
 */
function removeAt(index: number) {
    const remaining = items.value.filter((_, i) => i !== index);

    releaseItems();

    items.value = remaining;
    names.value = remaining.map((item) => item.name);

    emit('remove', index);

    if (!input.value) return;

    const transfer = new DataTransfer();

    remaining.forEach((item) => {
        if (item.file) transfer.items.add(item.file);
    });

    input.value.files = transfer.files;

    emit('change', {
        target: input.value,
    } as unknown as Event);
}

/** Kosongkan pilihan berkas (dipakai induk saat form dibuka atau setelah disimpan). */
function reset() {
    releaseItems();

    names.value = [];
    items.value = [];

    if (input.value) input.value.value = '';
}

onBeforeUnmount(releaseItems);

defineExpose({ reset });
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-input bg-background px-3 py-2 text-sm text-muted-foreground transition-colors hover:border-primary/50 hover:text-foreground"
                @click="pick"
            >
                <Upload class="h-4 w-4" :stroke-width="1.5" />
                {{ label }}
            </button>
            <span
                v-if="showSelection"
                class="max-w-full min-w-0 truncate text-sm"
                :class="
                    names.length
                        ? 'font-medium text-foreground'
                        : 'text-muted-foreground'
                "
                :title="names.join(', ')"
            >
                {{ selectionText }}
            </span>
        </div>

        <!-- Pratinjau berkas yang baru dipilih, supaya jelas berkas mana
             yang akan diunggah -- bukan cuma nama filenya. -->
        <div v-if="showPreview && items.length" class="flex flex-wrap gap-2">
            <div
                v-for="(item, index) in items"
                :key="`${item.name}-${index}`"
                class="group relative"
            >
                <img
                    v-if="item.isImage && item.url"
                    :src="item.url"
                    :alt="item.name"
                    :class="[
                        previewImageClass ?? previewClass,
                        'rounded-lg border border-primary/40 object-cover',
                    ]"
                />
                <div
                    v-else
                    :class="[
                        previewClass,
                        'flex flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-primary/40 bg-muted/30 px-1 text-center',
                    ]"
                >
                    <span class="text-[11px] font-medium text-foreground">
                        PDF
                    </span>
                    <span class="text-[10px] text-muted-foreground">
                        {{ item.size }}
                    </span>
                </div>

                <button
                    v-if="multiple"
                    type="button"
                    title="Batalkan berkas ini"
                    class="absolute -top-1.5 -right-1.5 rounded-full bg-destructive p-0.5 text-white opacity-0 transition-opacity group-hover:opacity-100 focus:opacity-100"
                    @click="removeAt(index)"
                >
                    <X class="h-3 w-3" />
                </button>

                <p
                    class="mt-1 max-w-24 truncate text-[10px] text-muted-foreground"
                    :title="item.name"
                >
                    {{ item.name }}
                </p>
            </div>
        </div>

        <p v-if="hint" class="text-xs text-muted-foreground">{{ hint }}</p>
        <p v-if="error" class="text-xs text-destructive">{{ error }}</p>

        <input
            ref="input"
            type="file"
            class="hidden"
            :accept="accept"
            :multiple="multiple"
            @change="onChange"
        />
    </div>
</template>
