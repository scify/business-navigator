<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
const page = usePage();
</script>

<template>
    <section
        id="section-hero"
        class="section-hero bg-primary bg-gradient bg-opacity-20"
    >
        <div class="container-xxl px-4">
            <div class="text-center py-4">
                <h1>{{ page.props.app.name }}</h1>
                <p class="lead">Explore the European AI Landscape</p>
            </div>
        </div>
    </section>
</template>
