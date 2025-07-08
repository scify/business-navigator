<!--suppress JSUnusedGlobalSymbols -->
<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useOrganisationDescription } from '@/scripts/composables/useOrganisationDescription';
import MapV2 from '@/views/components/Maps/MapV2.vue';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import HeroSection from '@/views/pages/Organisations/Show/Partials/HeroSection.vue';
import BlueSkyIcon from '@/views/components/Icons/BlueSkyIcon.vue';
import LinkedInIcon from '@/views/components/Icons/LinkedInIcon.vue';
import TwitterIcon from '@/views/components/Icons/TwitterIcon.vue';
import CategoryCard from '@/views/pages/Organisations/Show/Partials/CategoryCard.vue';
import FoundedCard from '@/views/pages/Organisations/Show/Partials/FoundedCard.vue';
// import TurnOverCard from '@/views/pages/Organisations/Show/Partials/TurnOverCard.vue';
// import EmployeesCard from '@/views/pages/Organisations/Show/Partials/EmployeesCard.vue';
import type { Organisation } from '@/scripts/types/ModelTypes';

const props = defineProps<{
    organisation: Organisation;
}>();

const { organisationType, metaDescription } = useOrganisationDescription(props.organisation);

// Convert newlines to commas for display
const formattedAddressDisplay = computed(() => {
    return props.organisation.formatted_address?.replace(/\n/g, ', ') || '';
});
</script>

<template>
    <GuestLayout>
        <Head>
            <title>{{ props.organisation.name }} - AI {{ organisationType }} Profile</title>
            <meta
                name="description"
                :content="metaDescription"
            />
        </Head>

        <HeroSection :organisation="organisation" />

        <section
            id="section-organisation-metrics"
            class="section-organisation-overview"
        >
            <h2 class="visually-hidden">Overview</h2>
            <div class="container-xxl px-4">
                <div class="row">
                    <FoundedCard :year="organisation.founding_year" />

                    <CategoryCard
                        v-if="organisation.industry_sectors?.length"
                        :items="organisation.industry_sectors"
                        title="Industry Sectors"
                    />
                    <CategoryCard
                        v-if="organisation.enterprise_functions?.length"
                        :items="organisation.enterprise_functions"
                        title="Enterprise Functions"
                    />
                    <CategoryCard
                        v-if="organisation.solution_types?.length"
                        :items="organisation.solution_types"
                        title="AI Solutions"
                    />
                    <CategoryCard
                        v-if="organisation.technology_types?.length"
                        :items="organisation.technology_types"
                        title="Technology"
                    />
                    <CategoryCard
                        v-if="organisation.offer_types?.length"
                        :items="organisation.offer_types"
                        title="Offers"
                    />
                </div>
            </div>
        </section>

        <section
            id="section-organisation-description"
            class="section-organisation-description mt-n5"
        >
            <h2 class="visually-hidden">Description</h2>
            <div class="container-xxl px-4">
                <div class="row">
                    <div class="col-12 col-md-5 col-xl-4">
                        <div
                            v-if="organisation.logo"
                            class="organisation-logo bg-white p-3 p-sm-2 p-md-3 p-lg-4 col-12"
                        >
                            <img
                                class="img-fluid"
                                loading="lazy"
                                :src="'/media/logos/' + organisation.logo.filename"
                                :alt="organisation.logo.alt"
                                :width="organisation.logo.width"
                                :height="organisation.logo.height"
                            />
                        </div>
                        <div v-if="organisation.marketplace_slug">
                            <a
                                :href="'https://marketplace.aiodp.ai/search?q=' + organisation.marketplace_slug"
                                class="btn btn-ilt-blue-sec mt-3 col-12 text-wrap-balance"
                            >
                                Find AI Tools & Services by {{ organisation.name }} on the
                                <strong class="fw-bold">AIoDP Marketplace</strong>
                            </a>
                        </div>
                        <div>
                            <a
                                v-if="organisation.website_url"
                                :href="organisation.website_url"
                                class="btn btn-primary mt-3 col-12 text-wrap-balance"
                            >
                                {{ organisation.name }} website
                            </a>
                        </div>
                        <div v-if="organisation.social_linkedin">
                            <a
                                :href="'https://www.linkedin.com/' + organisation.social_linkedin"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-ilt-blue-sec d-flex my-3 align-items-center"
                            >
                                <span class="pe-2 text-wrap-balance">{{ organisation.name }} on LinkedIn</span>
                                <LinkedInIcon class="ms-auto" />
                            </a>
                        </div>
                        <div v-if="organisation.social_bluesky">
                            <a
                                :href="'https://bsky.app/profile/' + organisation.social_bluesky"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-ilt-blue-sec d-flex my-3 align-items-center"
                            >
                                <span class="pe-2 text-wrap-balance">{{ organisation.name }} on BlueSky</span>
                                <BlueSkyIcon class="ms-auto" />
                            </a>
                        </div>
                        <!--
                        <div v-if="organisation.social_facebook">
                            <a
                                :href="'https://www.facebook.com/' + organisation.social_facebook"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-ilt-blue-sec d-flex my-3 align-items-center"
                            >
                                <span class="pe-2 text-wrap-balance">{{ organisation.name }} on Facebook</span>
                                <FacebookIcon class="ms-auto" />
                            </a>
                        </div>
                        <div v-if="organisation.social_instagram">
                            <a
                                :href="'https://www.instagram.com/' + organisation.social_instagram"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-ilt-blue-sec d-flex my-3 align-items-center"
                            >
                                <span class="pe-2 text-wrap-balance">{{ organisation.name }} on Instagram</span>
                                <InstagramIcon class="ms-auto" />
                            </a>
                        </div>
                        -->
                        <div v-if="organisation.social_x">
                            <a
                                :href="'https://x.com/' + organisation.social_x"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-outline-ilt-blue-sec d-flex my-3 align-items-center"
                            >
                                <span class="pe-2 text-wrap-balance">{{ organisation.name }} on X</span>
                                <TwitterIcon class="ms-auto" />
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-xl-8 mt-5 mt-md-0">
                        <!-- eslint-disable vue/no-v-html -->
                        <div
                            v-if="organisation.description"
                            class="col-12 px-xl-5 markdown"
                            v-html="organisation.description"
                        />
                        <!-- eslint-enable vue/no-v-html -->
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="organisation.lat && organisation.lng && organisation.country"
            id="section-map"
            class="section-map mt-n4"
        >
            <div class="container-xxl px-4 pb-3">
                <h2 class="text-ilt-blue d-block">Location</h2>
                <p class="lead">
                    {{ formattedAddressDisplay }}
                </p>
            </div>
            <MapV2
                :organisation="organisation"
                :worldview="organisation.country.alpha2"
                :hide-improve-map-link="
                    organisation.location_confidence !== undefined &&
                    organisation.location_confidence < 10 &&
                    organisation.location_source !== 'manual'
                "
            />
            <div
                v-if="
                    organisation.location_confidence !== undefined &&
                    organisation.location_confidence < 10 &&
                    organisation.location_source !== 'manual'
                "
                class="container-xxl px-4 pt-2"
            >
                <p class="small text-muted">
                    The indicated location might not be precise.
                    <a
                        :href="`https://www.openstreetmap.org/edit?lat=${organisation.lat}&lon=${organisation.lng}&zoom=18`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-decoration-none"
                    >
                        Edit on OpenStreetMap
                    </a>
                </p>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
.organisation-logo {
    border: 1px solid var(--bs-border-color);
    background: var(--bs-white);
    height: 320px; /* Set height to match your intended size */
    display: flex;
    align-items: center; /* Center vertically */
    justify-content: center; /* Center horizontally if needed */
    overflow: hidden; /* Clip any excess part of the image */

    img {
        max-height: 100%; /* Ensures image fits within the height */
        max-width: 100%; /* Ensures the width is contained within the available space */
        object-fit: contain; /* Prevents distortion, keeping aspect ratio intact */
        height: auto; /* Maintain aspect ratio */
        width: auto; /* Maintain aspect ratio */
    }
}
</style>
