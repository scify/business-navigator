<script setup lang="ts">
// Based on Vue3 Mapbox GL
// @link https://vue-mapbox-gl.studiometa.dev/components/MapboxMap/
import { onMounted } from 'vue';
import type { Organisation } from '@/scripts/types/ModelTypes';
import { MapboxMap, MapboxMarker } from '@studiometa/vue-mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

interface MapProps {
    organisation: Organisation;
    worldview?: string;
    hideImproveMapLink?: boolean;
}

const props = defineProps<MapProps>();
const mapBoxToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN;
const mapStyle = 'mapbox://styles/infoscify/cm42whc8c00wx01sd9kes1oir';

onMounted(() => {
    if (!props.organisation.lng || !props.organisation.lat) return;
});
</script>

<template>
    <MapboxMap
        :access-token="mapBoxToken"
        :map-style="mapStyle"
        :center="[props.organisation.lng, props.organisation.lat]"
        :zoom="12"
        :interactive="false"
        :performance-metrics-collection="false"
        :scroll-zoom="false"
        :worldview="worldview"
        :class="{ 'hide-improve-map': hideImproveMapLink }"
    >
        <MapboxMarker
            :lng-lat="[props.organisation.lng, props.organisation.lat]"
            color="#000"
        />
    </MapboxMap>
</template>

<!--suppress CssUnusedSymbol -->
<style>
.mapboxgl-map {
    width: 100%;
    margin: 0 auto;
    min-height: 400px;
}

.hide-improve-map .mapbox-improve-map {
    display: none;
}
</style>
