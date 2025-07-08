<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import FiltersComponent from '@/views/components/FiltersComponent.vue';
import OrganisationItem from '@/views/components/OrganisationCard.vue';
import Pagination from '@/views/components/Pagination.vue';
import type { Filters, SelectedFilters } from '@/scripts/types/FilterTypes';
import type { Organisation } from '@/scripts/types/ModelTypes';
import type { PaginatedResults } from '@/scripts/types/PaginationTypes';
import HeroSection from '@/views/pages/Explore/Index/Partials/HeroSection.vue';

// router.reload({ only: ['results'] })

const props = defineProps<{
    filters: Filters | null;
    results: PaginatedResults<Organisation>;
    selectedFilters: SelectedFilters;
}>();

// Create a ref for the results section
const resultsList = ref<HTMLElement | null>(null);

// Selected filters bound to the select inputs:
const selectedFilters = ref({
    organisation_type: props.selectedFilters.organisation_type || '',
    industry_sector: props.selectedFilters.industry_sector || '',
    enterprise_function: props.selectedFilters.enterprise_function || '',
    solution_type: props.selectedFilters.solution_type || '',
    technology_type: props.selectedFilters.technology_type || '',
    offer_type: props.selectedFilters.offer_type || '',
    country: props.selectedFilters.country || '',
});

// Watch for changes in props.selectedFilters and update the local state
watch(
    () => props.selectedFilters,
    (newVal) => {
        selectedFilters.value = {
            organisation_type: newVal.organisation_type || '',
            industry_sector: newVal.industry_sector || '',
            enterprise_function: newVal.enterprise_function || '',
            solution_type: newVal.solution_type || '',
            technology_type: newVal.technology_type || '',
            offer_type: newVal.offer_type || '',
            country: newVal.country || '',
        };
    },
);

// Watch for changes in the results (which happens after pagination):
watch(
    () => props.results,
    () => {
        // Use nextTick to ensure DOM has updated
        nextTick(() => {
            // Scroll to the results section using the ref
            if (resultsList.value) {
                resultsList.value.scrollIntoView({ behavior: 'smooth' });
                resultsList.value.setAttribute('tabindex', '-1');
                resultsList.value.focus();
            }
        });
    },
    { deep: true }, // Deep watch to detect changes in the results object
);

const applyFilters = (filters: Record<string, string | null>) => {
    // Remove null or empty values
    const sanitizedFilters = Object.fromEntries(
        Object.entries(filters).filter(([, value]) => value !== null && value !== ''),
    );

    console.log(sanitizedFilters); // Optional: for debugging to see what is sent
    router.get(route('explore'), sanitizedFilters, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <GuestLayout>
        <Head>
            <title>Explore</title>
            <meta
                name="description"
                content=" Explore the European AI Landscape"
            />
        </Head>

        <HeroSection />

        <!-- Filter Form -->
        <section
            v-if="props.filters"
            id="section-filters"
            class="section-filters bg-ilt-blue-gray-300 bg-opacity-50"
        >
            <div class="py-1">
                <FiltersComponent
                    :filters="props.filters"
                    :selected-filters="selectedFilters"
                    @apply-filters="applyFilters"
                />
            </div>
        </section>

        <!-- Display Company Results -->
        <section
            v-if="props.results.data.length"
            id="section-results"
            ref="resultsList"
            class="section-results"
        >
            <div class="container-xxl px-4 mb-n5">
                <h2 class="visually-hidden">Results</h2>
                <ul class="list-unstyled row">
                    <OrganisationItem
                        v-for="result in props.results.data"
                        :key="result.id"
                        :organisation="result"
                    />
                </ul>

                <Pagination :links="props.results.links" />
            </div>
        </section>
    </GuestLayout>
</template>
