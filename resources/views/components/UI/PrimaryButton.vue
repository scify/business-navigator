<script setup lang="ts">
defineProps<{
    type: 'button' | 'submit' | 'reset' | undefined;
}>();
</script>

<template>
    <button
        class="btn btn-primary align-items-center px-5 py-2"
        :type="type"
    >
        <slot />
    </button>
</template>
