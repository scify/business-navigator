<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();

// Default classes
// const originalClasses = 'section-hero bg-primary bg-gradient';
const classes = 'bg-white bg-opacity-100 bg-3d-grid-container';
</script>

<template>
    <section
        id="section-hero"
        :class="['section-hero', classes]"
    >
        <div class="bg-3d-grid">
            <div class="container-xxl px-4">
                <div class="d-flex flex-column pt-3 pb-1">
                    <div class="text-center py-4 m-n3">
                        <h1 class="text-ilt-blue pt-0">{{ page.props.app.name }}</h1>
                        <h2 class="lead">Explore the European AI Landscape</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
