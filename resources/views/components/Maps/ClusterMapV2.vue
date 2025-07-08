<script setup lang="ts">
// Based on Vue3 Mapbox GL
// @link https://vue-mapbox-gl.studiometa.dev/components/MapboxCluster/
import { nextTick, ref } from 'vue';
import OrganisationPopup from './OrganisationPopup.vue';
import { MapboxCluster, MapboxMap, MapboxNavigationControl, MapboxPopup } from '@studiometa/vue-mapbox-gl';
import type { Feature, FeatureCollection, Point, Position } from 'geojson';
import type { OrganisationGeoProperties } from '@/scripts/types/ModelTypes';
import 'mapbox-gl/dist/mapbox-gl.css';

const props = defineProps<{
    organisationsGeoJson: FeatureCollection<Point, OrganisationGeoProperties>;
}>();

const mapBoxToken = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN;
const mapStyle = 'mapbox://styles/infoscify/cm42whc8c00wx01sd9kes1oir';

// Map References:
// const mapBoxMap = ref();
// const map = computed(() => mapBoxMap.value.map);
const mapCenter = ref<Position>([10.8869, 51.99229]);

// Map Configuration:
const mapConfig = ref({
    clustersPaint: {
        'circle-color': [
            'step',
            ['get', 'point_count'],
            '#51bbd6',
            20, // count
            '#f1f075',
            50, // count
            '#f28cb1',
        ],
        'circle-radius': [
            'step',
            ['get', 'point_count'],
            24,
            20, // count
            42,
            50, // count
            72,
        ],
    },
    clusterCountPaint: {
        'text-color': '#000',
    },
    unclusteredPointPaint: {
        'circle-color': 'rgba(0, 0, 0, 0.7)', // rgba(236,198,37,.25)
        'circle-radius': 6,
        'circle-stroke-width': 2,
        'circle-stroke-color': 'rgba(0, 0, 0, 0.9)',
    },
    clusterCountLayout: {
        'text-field': ['get', 'point_count_abbreviated'],
        'text-size': 16,
        'text-font': ['Montserrat Medium', 'DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
    },
});

// Map Popup Properties:
const popupContent = ref();
const popupIsOpen = ref(false);
const mapPointPosition = ref<Position>([0, 0]);

async function openPopup({ geometry, properties }: Feature<Point, OrganisationGeoProperties>) {
    await nextTick();
    mapPointPosition.value = [...geometry.coordinates];
    popupIsOpen.value = true;

    // Mapbox GL converts properties' values to JSON, so we need to parse them
    // to retrieve any complex data structure such as arrays and objects.
    popupContent.value = Object.fromEntries(
        Object.entries(properties).map(([key, value]) => {
            try {
                return [key, JSON.parse(String(value))];
            } catch {
                // Silence is golden.
            }

            return [key, value];
        }),
    );
}
</script>

<template>
    <MapboxMap
        ref="mapBoxMap"
        :access-token="mapBoxToken"
        :map-style="mapStyle"
        :center="mapCenter"
        :zoom="3"
        :min-zoom="3"
        :max-zoom="16"
        :performance-metrics-collection="false"
        :scroll-zoom="false"
    >
        <MapboxNavigationControl position="bottom-right" />
        <MapboxPopup
            v-if="popupIsOpen"
            :key="mapPointPosition.join('-')"
            :lng-lat="mapPointPosition"
            anchor="bottom"
            @mb-close="() => (popupIsOpen = false)"
        >
            <OrganisationPopup :organisation="popupContent" />
        </MapboxPopup>
        <MapboxCluster
            :data="props.organisationsGeoJson"
            :cluster-max-zoom="14"
            :cluster-radius="50"
            :clusters-paint="mapConfig.clustersPaint"
            :cluster-count-layout="mapConfig.clusterCountLayout"
            :cluster-count-paint="mapConfig.clusterCountPaint"
            :unclustered-point-paint="mapConfig.unclusteredPointPaint"
            @mb-feature-click="openPopup"
        />
    </MapboxMap>
</template>

<!--suppress CssUnusedSymbol -->
<style>
.mapboxgl-map {
    width: 100%;
    margin: 0 auto;
    min-height: 640px;
}
.mapboxgl-popup-content {
    background: rgba(var(--bs-ilt-yellow-rgb), 0.98);
    border-radius: 1rem;
    font-size: 1rem;
    font-family: var(--bs-body-font-family), sans-serif;
    width: 320px;
}
.mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip {
    border-top-color: rgba(var(--bs-ilt-yellow-rgb), 0.94);
}
.mapboxgl-popup-close-button {
    width: 2rem;
    height: 2rem;
    aspect-ratio: 1;
}
</style>
