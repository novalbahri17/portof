<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import {
    ClassicEditor,
    Essentials,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Link,
    List,
    Heading,
    Paragraph,
    BlockQuote,
    Table,
    TableToolbar,
    CodeBlock,
    Code,
    Image,
    Indent,
    Undo,
    Autoformat,
    AutoLink,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const props = withDefaults(defineProps<{
    modelValue: string;
    height?: number;
    minimal?: boolean;
}>(), {
    height: 400,
    minimal: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const isDark = ref(document.documentElement.classList.contains('dark'));

function syncDarkClass() {
    isDark.value = document.documentElement.classList.contains('dark');
    // Toggle class on body so floating panels (rendered at body level) get styled
    document.body.classList.toggle('ck-dark-mode', isDark.value);
}

let observer: MutationObserver | null = null;
onMounted(() => {
    syncDarkClass();
    observer = new MutationObserver(syncDarkClass);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
onUnmounted(() => {
    observer?.disconnect();
});

const editorConfig = computed(() => ({
    plugins: props.minimal
        ? [Essentials, Bold, Italic, Underline, Link, List, Paragraph, Autoformat, AutoLink]
        : [Essentials, Bold, Italic, Underline, Strikethrough, Link, List, Heading, Paragraph, BlockQuote, Table, TableToolbar, CodeBlock, Code, Image, Indent, Undo, Autoformat, AutoLink],
    toolbar: props.minimal
        ? ['bold', 'italic', 'underline', '|', 'bulletedList', 'numberedList', '|', 'link']
        : ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', 'code', '|', 'bulletedList', 'numberedList', '|', 'link', 'blockQuote', 'insertTable', 'codeBlock', '|', 'indent', 'outdent'],
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
    },
    codeBlock: {
        languages: [
            { language: 'php', label: 'PHP' },
            { language: 'javascript', label: 'JavaScript' },
            { language: 'typescript', label: 'TypeScript' },
            { language: 'css', label: 'CSS' },
            { language: 'html', label: 'HTML' },
            { language: 'bash', label: 'Bash' },
            { language: 'sql', label: 'SQL' },
            { language: 'json', label: 'JSON' },
        ],
    },
    licenseKey: 'GPL',
}));
</script>

<template>
    <div class="ck-wrapper" :style="{ '--ck-height': height + 'px' }">
        <Ckeditor
            :editor="ClassicEditor"
            :model-value="modelValue"
            :config="editorConfig"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </div>
</template>

<style>
.ck-wrapper .ck-editor__editable {
    min-height: var(--ck-height, 400px);
}

/*
 * Dark mode — uses .ck-dark-mode on <body> so it catches
 * both inline editor elements AND floating panels/dropdowns
 * that CKEditor appends to <body>.
 */

/* Toolbar */
.ck-dark-mode .ck.ck-toolbar {
    background: #1a1a19 !important;
    border-color: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-toolbar .ck-button,
.ck-dark-mode .ck.ck-toolbar .ck-dropdown__button {
    color: #d4d4d4 !important;
}
.ck-dark-mode .ck.ck-toolbar .ck-button:hover,
.ck-dark-mode .ck.ck-toolbar .ck-dropdown__button:hover {
    background: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-toolbar .ck-button.ck-on,
.ck-dark-mode .ck.ck-toolbar .ck-dropdown__button.ck-on {
    background: #333 !important;
    color: #fff !important;
}
.ck-dark-mode .ck.ck-toolbar .ck-toolbar__separator {
    background: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-toolbar .ck-button .ck-icon {
    color: inherit !important;
}

/* Editor area */
.ck-dark-mode .ck.ck-editor__main > .ck-editor__editable {
    background: #0a0a0a !important;
    color: #e4e4e7 !important;
    border-color: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-editor__editable.ck-focused {
    border-color: #555 !important;
    box-shadow: none !important;
}
.ck-dark-mode .ck.ck-editor__editable .ck-placeholder::before {
    color: #666 !important;
}

/* Dropdown panels (floating, appended to body) */
.ck-dark-mode .ck.ck-dropdown__panel,
.ck-dark-mode .ck.ck-balloon-panel {
    background: #1a1a19 !important;
    border-color: #2a2a28 !important;
}

/* List items inside dropdowns */
.ck-dark-mode .ck.ck-list {
    background: #1a1a19 !important;
}
.ck-dark-mode .ck.ck-list__item .ck-button {
    color: #d4d4d4 !important;
}
.ck-dark-mode .ck.ck-list__item .ck-button:hover,
.ck-dark-mode .ck.ck-list__item .ck-button.ck-on {
    background: #2a2a28 !important;
    color: #fff !important;
}
.ck-dark-mode .ck.ck-list__item .ck-button .ck-button__label {
    color: inherit !important;
}
.ck-dark-mode .ck.ck-list__separator {
    border-color: #2a2a28 !important;
}

/* Heading dropdown specific */
.ck-dark-mode .ck-heading_heading1,
.ck-dark-mode .ck-heading_heading2,
.ck-dark-mode .ck-heading_heading3,
.ck-dark-mode .ck-heading_paragraph {
    color: #d4d4d4 !important;
}

/* Input fields in dialogs (link, table, etc.) */
.ck-dark-mode .ck.ck-input {
    background: #0a0a0a !important;
    color: #e4e4e7 !important;
    border-color: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-input:focus {
    border-color: #555 !important;
}
.ck-dark-mode .ck.ck-labeled-field-view .ck-label {
    color: #999 !important;
}

/* Link form / balloon */
.ck-dark-mode .ck.ck-link-form,
.ck-dark-mode .ck.ck-link-actions {
    background: #1a1a19 !important;
}
.ck-dark-mode .ck.ck-link-actions .ck-button {
    color: #d4d4d4 !important;
}
.ck-dark-mode .ck.ck-link-actions .ck-button:hover {
    background: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-link-actions a {
    color: #60a5fa !important;
}

/* Table widget */
.ck-dark-mode .ck.ck-insert-table-dropdown__grid .ck-insert-table-dropdown__grid-box {
    border-color: #2a2a28 !important;
}
.ck-dark-mode .ck.ck-insert-table-dropdown__grid .ck-insert-table-dropdown__grid-box.ck-on {
    background: #333 !important;
    border-color: #555 !important;
}
.ck-dark-mode .ck.ck-insert-table-dropdown__label {
    color: #999 !important;
}

/* Balloon panel arrow */
.ck-dark-mode .ck.ck-balloon-panel::before {
    border-color: #2a2a28 transparent transparent transparent !important;
}
.ck-dark-mode .ck.ck-balloon-panel::after {
    border-color: #1a1a19 transparent transparent transparent !important;
}
.ck-dark-mode .ck.ck-balloon-panel[class*="arrow_s"]::before {
    border-color: transparent transparent #2a2a28 transparent !important;
}
.ck-dark-mode .ck.ck-balloon-panel[class*="arrow_s"]::after {
    border-color: transparent transparent #1a1a19 transparent !important;
}

/* Powered by badge */
.ck-dark-mode .ck.ck-powered-by {
    opacity: 0.4;
}
</style>
