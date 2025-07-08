<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div
        v-show="message"
        role="alert"
    >
        <p>
            {{ message }}
        </p>
    </div>
</template>
