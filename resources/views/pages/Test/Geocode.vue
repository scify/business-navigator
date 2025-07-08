<script lang="ts" setup>
import { Head, useForm } from '@inertiajs/vue3';
import type { GeocodeResponse } from '@/scripts/types/GeocodeTypes';
import { computed } from 'vue';
import { LucideExternalLink, LucideMapPin, LucideAlertTriangle, LucideXCircle, LucideSearch } from 'lucide-vue-next';

const props = defineProps<GeocodeResponse>();

const form = useForm({
    address: props?.address,
    country: props?.country,
    useCache: props.useCache,
});

// Map for confidence levels (0-10 scale from OpenCage)
const getConfidenceDetails = (confidence: number) => {
    if (confidence === 0)
        return {
            icon: LucideXCircle,
            text: 'No Results Found',
            precision: 'Unable to determine',
            class: 'text-danger',
        };
    if (confidence === 10)
        return {
            icon: LucideMapPin,
            text: `Highest Confidence (${confidence}/10)`,
            precision: '< 0.25km',
            class: 'text-success',
        };
    if (confidence === 9)
        return {
            icon: LucideMapPin,
            text: `Very High Confidence (${confidence}/10)`,
            precision: '< 0.50km',
            class: 'text-success',
        };
    if (confidence === 8)
        return {
            icon: LucideMapPin,
            text: `High Confidence (${confidence}/10)`,
            precision: '< 1.00km',
            class: 'text-success',
        };
    if (confidence === 7)
        return {
            icon: LucideMapPin,
            text: `High Confidence (${confidence}/10)`,
            precision: '< 5.00km',
            class: 'text-success',
        };
    if (confidence === 6)
        return {
            icon: LucideSearch,
            text: `Medium Confidence (${confidence}/10)`,
            precision: '< 7.50km',
            class: 'text-warning',
        };
    if (confidence === 5)
        return {
            icon: LucideSearch,
            text: `Medium Confidence (${confidence}/10)`,
            precision: '< 10.00km',
            class: 'text-warning',
        };
    if (confidence === 4)
        return {
            icon: LucideAlertTriangle,
            text: `Low Confidence (${confidence}/10)`,
            precision: '< 15.00km',
            class: 'text-danger',
        };
    if (confidence === 3)
        return {
            icon: LucideAlertTriangle,
            text: `Low Confidence (${confidence}/10)`,
            precision: '< 20.00km',
            class: 'text-danger',
        };
    if (confidence === 2)
        return {
            icon: LucideAlertTriangle,
            text: `Very Low Confidence (${confidence}/10)`,
            precision: '< 25.00km',
            class: 'text-danger',
        };
    if (confidence === 1)
        return {
            icon: LucideAlertTriangle,
            text: `Very Low Confidence (${confidence}/10)`,
            precision: '> 25.00km',
            class: 'text-danger',
        };
    return { icon: LucideXCircle, text: 'Unknown', precision: 'Unknown', class: 'text-muted' };
};

const isResultNotFound = props.result?.confidence === 0;

const currentConfidence = computed(() => {
    return props.result?.confidence !== undefined ? getConfidenceDetails(props.result.confidence) : null;
});

const googleMapsLink = computed(() => {
    if (props.result) {
        const { lat, lng } = props.result;
        return `https://www.google.com/maps?q=${lat},${lng}`;
    }
    return undefined;
});

const openStreetMapLink = computed(() => {
    if (props.result) {
        const { lat, lng } = props.result;
        return `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=17/${lat}/${lng}`;
    }
    return undefined;
});
</script>

<template>
    <Head title="Geocoder Test" />
    <main>
        <section
            id="section-test"
            class="section-test"
        >
            <div class="container-xxl">
                <div class="row">
                    <div class="col">
                        <h1>Geocode</h1>

                        <form
                            method="get"
                            action="/test/geocode"
                            @submit.prevent="form.get('/test/geocode', { preserveScroll: true })"
                        >
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label
                                        class="form-label"
                                        for="address"
                                        >Address:</label
                                    >
                                    <input
                                        id="address"
                                        v-model="form.address"
                                        class="form-control"
                                        type="text"
                                        name="address"
                                        data-1p-ignore
                                        placeholder="Enter an arbitrary address"
                                    />
                                </div>
                                <div class="col-md-4">
                                    <label
                                        class="form-label"
                                        for="country"
                                        >Country:</label
                                    >
                                    <select
                                        id="country"
                                        v-model="form.country"
                                        class="form-select"
                                        name="country"
                                    >
                                        <option
                                            v-for="country in props.countries"
                                            :key="country.alpha2"
                                            :value="country.alpha2"
                                        >
                                            {{ country.name }} ({{ country.alpha2 }})
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input
                                    id="useCache"
                                    v-model="form.useCache"
                                    class="form-check-input"
                                    type="checkbox"
                                    name="useCache"
                                />
                                <label
                                    class="form-check-label"
                                    for="useCache"
                                    >Use Cache</label
                                >
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="btn btn-primary"
                            >
                                Geocode
                            </button>
                        </form>
                    </div>
                </div>

                <div
                    v-if="props.result"
                    class="row mt-4"
                >
                    <div
                        v-if="!form.processing"
                        class="col"
                    >
                        <h2>Result</h2>

                        <div class="border p-3 mb-3">
                            <div
                                v-if="isResultNotFound"
                                class="alert alert-danger"
                                role="alert"
                            >
                                <strong>No results found for the given address.</strong>
                            </div>
                            <div v-else>
                                <div class="d-flex gap-2 mb-2">
                                    <div
                                        v-if="props.result.type"
                                        class="text-muted text-capitalize"
                                    >
                                        <strong>Type:</strong> {{ props.result.type }}
                                    </div>
                                    <div>
                                        <strong>Coordinates:</strong> {{ props.result.lat }}, {{ props.result.lng }}
                                    </div>
                                    <div
                                        v-if="props.result.country"
                                        class="text-muted"
                                    >
                                        <strong>Country:</strong> {{ props.result.country }} ({{ props.result.alpha2 }})
                                    </div>
                                    <div
                                        v-if="props.result.region"
                                        class="text-muted"
                                    >
                                        <strong>Region:</strong> {{ props.result.region }}
                                    </div>
                                    <div
                                        v-if="props.result.city"
                                        class="text-muted"
                                    >
                                        <strong>City:</strong> {{ props.result.city }}
                                    </div>
                                    <div
                                        v-if="props.result.postal_code"
                                        class="text-muted"
                                    >
                                        <strong>Postal Code:</strong> {{ props.result.postal_code }}
                                    </div>
                                    <div><strong>Address:</strong> {{ props.result.formatted_address }}</div>
                                </div>

                                <div class="bg-dark-subtle card border-0 p-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <component
                                            :is="currentConfidence?.icon"
                                            :size="64"
                                            :class="currentConfidence?.class"
                                        />
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span
                                                    :class="currentConfidence?.class"
                                                    class="fw-bold h5"
                                                    >{{ currentConfidence?.text }}</span
                                                >
                                                <span class="badge bg-secondary">{{ props.result.confidence }}/10</span>
                                            </div>
                                            <div class="progress mb-2 bg-dark-subtle">
                                                <div
                                                    class="progress-bar"
                                                    role="progressbar"
                                                    aria-valuemin="0"
                                                    :aria-valuenow="props.result.confidence"
                                                    aria-valuemax="10"
                                                    :class="
                                                        props.result.confidence >= 7
                                                            ? 'bg-success'
                                                            : props.result.confidence >= 5
                                                              ? 'bg-warning'
                                                              : 'bg-danger'
                                                    "
                                                    :style="{ width: props.result.confidence * 10 + '%' }"
                                                ></div>
                                            </div>
                                            <div
                                                :class="currentConfidence?.class"
                                                class="fw-bold h5"
                                            >
                                                Precision: {{ currentConfidence?.precision }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="googleMapsLink"
                                    class="d-flex gap-2 my-3 ms-auto"
                                >
                                    <a
                                        :href="googleMapsLink"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-secondary"
                                    >
                                        <span class="d-flex align-items-center column-gap-2">
                                            <span>Open in Google Maps</span>
                                            <LucideExternalLink :size="16" />
                                        </span>
                                    </a>
                                    <a
                                        :href="openStreetMapLink"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-outline-secondary"
                                    >
                                        <span class="d-flex align-items-center column-gap-2">
                                            <span>Open in OpenStreetMap</span>
                                            <LucideExternalLink :size="16" />
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h3>Debug Data</h3>
                            <pre class="bg-light small p-3 border-dark-subtle border">{{
                                JSON.stringify(props.result, null, 2)
                            }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
