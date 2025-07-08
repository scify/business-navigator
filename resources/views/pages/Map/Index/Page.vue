<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import HeroSection from '@/views/pages/Map/Index/Partials/HeroSection.vue';
import ClusterMap from '@/views/components/Maps/ClusterMapV2.vue';
// import StoreLocatorMap from '@/views/components/Maps/StoreLocatorMap.vue';
import TableLiteV1 from '@/views/pages/Map/Index/Partials/TableLiteV1.vue';
import type { OrganisationGeoProperties, Organisation } from '@/scripts/types/ModelTypes';
import type { FeatureCollection, Point } from 'geojson';

const props = defineProps<{
    organisations: Organisation[];
    organisationsGeoJson: FeatureCollection<Point, OrganisationGeoProperties>;
}>();

const showFilters = false;

// Extract unique countries from GeoJSON data:
// @todo Get these values from the back-end, limit to only the ones with data with their geo center.
const uniqueCountries = computed(() => {
    const countries = new Set<string>();
    props.organisationsGeoJson.features.forEach((feature) => {
        const { country } = feature.properties;
        if (country) {
            countries.add(country);
        }
    });
    return Array.from(countries);
});

const filteredGeoJson = computed<FeatureCollection<Point, OrganisationGeoProperties>>(() => {
    if (!selectedCountry.value) {
        return props.organisationsGeoJson;
    }

    const features = props.organisationsGeoJson.features.filter((feature) => {
        const { country } = feature.properties;
        return country === selectedCountry.value;
    });

    return {
        ...props.organisationsGeoJson,
        features,
    };
});

// Filters
const selectedCountry = ref<string>('');
</script>

<template>
    <GuestLayout>
        <Head>
            <title>Map</title>
            <meta
                name="description"
                content="A comprehensive index of AI-Powered organisations in Europe "
            />
        </Head>

        <HeroSection />

        <section v-if="showFilters">
            <select v-model="selectedCountry">
                <option value="">All Countries</option>
                <option
                    v-for="country in uniqueCountries"
                    :key="country"
                    :value="country"
                >
                    {{ country }}
                </option>
            </select>
        </section>

        <section
            v-if="organisationsGeoJson"
            id="section-map"
            class="section-map"
        >
            <div class="map-container">
                <!--
                <StoreLocatorMap :organisations="props.organisations" />
                -->
                <ClusterMap :organisations-geo-json="filteredGeoJson" />
            </div>
        </section>

        <TableLiteV1 :organisations="organisations" />
    </GuestLayout>
</template>

<style scoped>
.section-map {
    .map-container {
        border-top: 0.275rem solid rgba(0 0 0 / 5%);
        border-bottom: 0.275rem solid rgba(0 0 0 / 5%);
    }
}
</style>
