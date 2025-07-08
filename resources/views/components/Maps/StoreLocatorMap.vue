<script setup lang="ts">
// Based on Vue3 Mapbox GL
// @link https://vue-mapbox-gl.studiometa.dev/components/StoreLocator/
import { StoreLocator } from '@studiometa/vue-mapbox-gl';
import { Organisation } from '@/scripts/types/ModelTypes';
import 'mapbox-gl/dist/mapbox-gl.css';
import '@mapbox/mapbox-gl-geocoder/lib/mapbox-gl-geocoder.css';

defineProps<{
    organisations: Organisation[];
}>();

const mapBoxToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN;
const mapStyle = 'mapbox://styles/infoscify/cm42whc8c00wx01sd9kes1oir';
</script>

<template>
    <StoreLocator
        :items="organisations"
        :access-token="mapBoxToken"
        :mapbox-map="{ mapStyle: mapStyle }"
    >
        <!-- Before list slot -->
        <template #before-list="{ filteredItems }">
            <p class="m-0">Total: {{ filteredItems.length }}</p>
        </template>

        <!-- After list slot -->
        <template #after-list="{ filteredItems }">
            <p v-if="filteredItems.length <= 0">No result.</p>
        </template>

        <!-- Panel slot -->
        <template #panel="{ close, item }">
            <button @click="close">Close</button>
            <p>{{ item }}</p>
        </template>
    </StoreLocator>
</template>
