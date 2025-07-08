<script lang="ts" setup>
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
                        <h1 class="text-ilt-blue pt-0">European AI Map</h1>
                        <h2 class="lead">A comprehensive index of AI-Powered organisations in Europe</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
