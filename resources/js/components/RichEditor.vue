<script setup lang="ts">
import { computed } from 'vue';
import { IconInfoCircle } from '@tabler/icons-vue';

/**
 * Textarea biasa — pengganti editor rich-text (CKEditor).
 * Mendukung teks multi-baris. Baris kosong ganda dipakai sebagai pemisah paragraf
 * pada tampilan publik, dan setiap baris baru menjadi item daftar untuk field
 * seperti "Hobbies" (format: "Judul — Keterangan").
 */
const props = withDefaults(defineProps<{
    modelValue: string;
    height?: number;
    placeholder?: string;
    hint?: string;
    rows?: number;
}>(), {
    height: 200,
    placeholder: '',
    hint: '',
    rows: 0,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const textareaRows = computed(() =>
    props.rows > 0 ? props.rows : Math.max(4, Math.round(props.height / 22)),
);

const value = computed({
    get: () => props.modelValue ?? '',
    set: (v: string) => emit('update:modelValue', v),
});
</script>

<template>
    <div>
        <textarea
            v-model="value"
            :rows="textareaRows"
            :placeholder="placeholder"
            :style="{ minHeight: height + 'px' }"
            class="w-full resize-y rounded-lg border border-input bg-background px-3 py-2 text-sm leading-relaxed text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
        />
        <p v-if="hint" class="mt-1.5 flex items-start gap-1.5 text-xs text-muted-foreground">
            <IconInfoCircle class="mt-px h-3.5 w-3.5 shrink-0" :stroke-width="1.5" />
            <span>{{ hint }}</span>
        </p>
    </div>
</template>
