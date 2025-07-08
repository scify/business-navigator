<!--suppress CssUnusedSymbol -->
<template>
    <div>
        <textarea ref="editor"></textarea>
    </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type EasyMDE from 'easymde';

// SSR-compatible imports
let EasyMDEClass: typeof EasyMDE | undefined;
if (typeof window !== 'undefined') {
    import('easymde').then(module => {
        EasyMDEClass = module.default;
    });
    import('easymde/dist/easymde.min.css');
}

const props = defineProps<{ modelValue: string }>();
const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const editor = ref<HTMLTextAreaElement | null>(null);
let easyMDE: EasyMDE | null = null;

// A flag to prevent the watcher from re-updating the editor
// with the same content that the editor just emitted.
let isUpdatingFromEditor = false;

const customToolbar = [
    'bold',
    'italic',
    'heading',
    '|',
    'quote',
    'unordered-list',
    'ordered-list',
    '|',
    'preview',
    'side-by-side',
    'fullscreen',
    '|',
    'guide',
] as const;

onMounted(async () => {
    if (editor.value && typeof window !== 'undefined') {
        // Wait for EasyMDE to be loaded
        if (!EasyMDEClass) {
            const module = await import('easymde');
            EasyMDEClass = module.default;
        }
        
        easyMDE = new EasyMDEClass({
            element: editor.value,
            initialValue: props.modelValue,
            toolbar: customToolbar,
            status: false, // Disables the status bar
            spellChecker: false, // Disables the spell checker
        });

        // At this point easyMDE is definitely not null
        const instance = easyMDE!;
        instance.codemirror.on('change', () => {
            isUpdatingFromEditor = true;
            emit('update:modelValue', instance.value());
        });
    }
});

watch(
    () => props.modelValue,
    (newValue) => {
        if (easyMDE && !isUpdatingFromEditor) {
            easyMDE.value(newValue);
        }
        // Reset the flag after the watch cycle
        isUpdatingFromEditor = false;
    },
);

onUnmounted(() => {
    if (easyMDE) {
        easyMDE.toTextArea();
        easyMDE = null;
    }
});
</script>

<style scoped>
:deep(.editor-toolbar) {
    border-radius: 0.375rem 0.375rem 0 0;
}
:deep(.CodeMirror) {
    border-radius: 0 0 0.375rem 0.375rem;
    border: 1px solid #ced4da; /* Match Bootstrap's default border */
}
</style>
