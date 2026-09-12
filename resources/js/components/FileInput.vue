<script setup lang="ts">
import { Upload } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
};

const props = withDefaults(defineProps<Props>(), {
    label: 'Pilih berkas',
    accept: undefined,
    multiple: false,
    hint: undefined,
    error: undefined,
    showSelection: true,
    emptyText: 'Belum ada berkas dipilih',
});

const emit = defineEmits<{ change: [event: Event] }>();

const input = ref<HTMLInputElement | null>(null);
const names = ref<string[]>([]);

const selectionText = computed(() => {
    if (names.value.length === 0) return props.emptyText;
    if (names.value.length === 1) return names.value[0];
    return `${names.value.length} berkas dipilih`;
});

function pick() {
    // Kosongkan nilai input agar berkas yang sama bisa dipilih ulang.
    if (input.value) input.value.value = '';
    input.value?.click();
}

function onChange(event: Event) {
    const files = Array.from((event.target as HTMLInputElement).files ?? []);

    names.value = files.map((file) => file.name);
    emit('change', event);
}

/** Kosongkan pilihan berkas (dipakai induk saat form dibuka atau setelah disimpan). */
function reset() {
    names.value = [];
    if (input.value) input.value.value = '';
}

defineExpose({ reset });
</script>

<template>
    <div class="space-y-1">
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
