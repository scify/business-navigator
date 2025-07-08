<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import { Organisation } from '@/scripts/types/ModelTypes';

interface MapProps {
    organisation: Organisation;
    worldview?: string;
}

const props = defineProps<MapProps>();
let map: mapboxgl.Map | null = null;

mapboxgl.accessToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN;

// Reference for the map container
const mapContainer = ref<HTMLDivElement | null>(null);

onMounted(() => {
    if (!mapContainer.value) return;
    if (!props.organisation.lng || !props.organisation.lat) return;

    // Initialise the map centered at the provided lat/lon
    map = new mapboxgl.Map({
        container: mapContainer.value,
        style: 'mapbox://styles/mapbox/light-v11',
        center: [props.organisation.lng, props.organisation.lat],
        zoom: 8,
        minZoom: 4,
        maxZoom: 12,
        interactive: false,
        worldview: props.worldview,
    });

    // Add a marker at the given coordinates with the provided name
    new mapboxgl.Marker({
        color: '#000',
    })
        .setLngLat([props.organisation.lng, props.organisation.lat])
        .setPopup(
            new mapboxgl.Popup().setHTML(`
                    <strong>${props.organisation.name}</strong><br>
                    <span>${props.organisation.organisation_types?.[0]?.name || 'Organisation'}</span><br>
                    <span class="small">${props.organisation.address_1 || ''}</span>
                    ${props.organisation.address_2 ? `<span class="small">${props.organisation.address_2}</span>` : ''}
                `),
        )
        .addTo(map);

    /*
    // for mapbox://styles/mapbox/standard
    map.on('style.load', () => {
        map.setConfigProperty('basemap', 'theme', 'monochrome');
        map.setConfigProperty('basemap', 'showRoadLabels', false);
        map.setConfigProperty('basemap', 'showTransitLabels', false);
        map.setConfigProperty('basemap', 'showPointOfInterestLabels', false);
        map.setConfigProperty('basemap', 'lightPreset', 'dawn');

    });
     */
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div
        ref="mapContainer"
        class="map-container"
    ></div>
</template>

<style scoped>
.map-container {
    width: 100%;
    margin: 0 auto;
    min-height: 400px;
}
::v-deep(.mapboxgl-popup-content) {
    font-size: 1rem;
    font-family: var(--bs-body-font-family), sans-serif;
}
</style>
