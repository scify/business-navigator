<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PrimaryLinkButtonXL from '@/views/components/UI/LinkButtonXL.vue';
const page = usePage();
const app_name = computed(() => page.props.app.name);

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
                <div class="d-flex flex-column">
                    <div class="align-self-center">
                        <img
                            class="img-fluid"
                            srcset="
                                @/images/hero-map.webp    1x,
                                @/images/hero-map@2x.webp 2x,
                                @/images/hero-map@3x.webp 3x
                            "
                            src="@/images/hero-map.webp"
                            width="400"
                            height="313"
                            alt=""
                        />
                    </div>
                    <div class="text-center pt-0 pb-3 m-n3">
                        <h1 class="text-ilt-blue pt-0">{{ app_name }}</h1>
                        <p class="lead">Explore the European AI Landscape</p>
                    </div>
                    <div class="text-center py-2">
                        <PrimaryLinkButtonXL
                            :href="route('explore')"
                            :responsive="true"
                        >
                            Explore
                        </PrimaryLinkButtonXL>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
