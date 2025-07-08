<script setup lang="ts">
import AuthenticatedHeader from './Partials/AuthenticatedHeader.vue';
import AuthenticatedFooter from './Partials/AuthenticatedFooter.vue';
import Toast from '@/views/components/Toast.vue';
</script>

<template>
    <header>
        <AuthenticatedHeader />
        <div v-if="$slots.header">
            <slot name="header" />
        </div>
    </header>
    <main class="auth">
        <slot />
    </main>
    <footer>
        <AuthenticatedFooter />
    </footer>
    <Toast />
</template>
