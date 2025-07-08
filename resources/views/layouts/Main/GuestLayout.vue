<script setup lang="ts">
import GuestHeader from './Partials/GuestHeader.vue';
import GuestFooter from './Partials/GuestFooter.vue';
</script>

<template>
    <header>
        <GuestHeader />
        <div v-if="$slots.header">
            <slot name="header" />
        </div>
    </header>
    <main>
        <slot />
    </main>
    <footer>
        <GuestFooter />
    </footer>
</template>
