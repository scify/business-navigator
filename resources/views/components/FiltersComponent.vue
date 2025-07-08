<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import FilterSelect from './FilterSelect.vue';
import type { Filters, SelectedFilters } from '@/scripts/types/FilterTypes';

// Props definitions.
const props = defineProps<{
    filters: Filters;
    selectedFilters: SelectedFilters;
}>();

// Emit definitions.
const emit = defineEmits<{
    (e: 'apply-filters', filters: Record<string, string | null>): void;
}>();

/**
 * Applies filters by emitting the current selectedFilters.
 * @returns {void}
 */
const applyFilters = (): void => {
    emit('apply-filters', selectedFilters.value);
};

// Creating a ref copy of selectedFilters to allow reactive modification:
const selectedFilters = ref({ ...props.selectedFilters });

/**
 * Re-sync selectedFilters when props.selectedFilters change
 */
watch(
    () => props.selectedFilters,
    (newVal) => {
        selectedFilters.value = { ...newVal };
    },
    { deep: true },
);

// Define a type that represents the keys of the selectedFilters object
type SelectedFiltersKeys = keyof typeof props.selectedFilters;

/**
 * Check if filters have changed vs original props.
 * If not re-synced, this will remain "true" after props update.
 */
const filtersChanged = computed<boolean>(() => {
    return (Object.keys(props.selectedFilters) as SelectedFiltersKeys[]).some((key) => {
        return props.selectedFilters[key] !== selectedFilters.value[key];
    });
});
</script>

<template>
    <form @submit.prevent="applyFilters">
        <div class="container-xxl px-4">
            <div class="row row-cols-1 row-gap-4 row-cols-md-3 row-cols-xl-4">
                <FilterSelect
                    v-if="filters.organisation_types"
                    v-model="selectedFilters.organisation_type"
                    :filter="filters.organisation_types"
                />
                <FilterSelect
                    v-if="filters.industry_sectors"
                    v-model="selectedFilters.industry_sector"
                    :filter="filters.industry_sectors"
                />
                <FilterSelect
                    v-if="filters.enterprise_functions"
                    v-model="selectedFilters.enterprise_function"
                    label="Enterprise Function"
                    :filter="filters.enterprise_functions"
                />
                <FilterSelect
                    v-if="filters.solution_types"
                    v-model="selectedFilters.solution_type"
                    :filter="filters.solution_types"
                />
                <FilterSelect
                    v-if="filters.technology_types"
                    v-model="selectedFilters.technology_type"
                    :filter="filters.technology_types"
                />
                <FilterSelect
                    v-if="filters.offer_types"
                    v-model="selectedFilters.offer_type"
                    :filter="filters.offer_types"
                />
                <FilterSelect
                    v-if="filters.countries"
                    v-model="selectedFilters.country"
                    :filter="filters.countries"
                />
                <div class="col ms-auto">
                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                        :class="{ 'btn-highlight': filtersChanged }"
                    >
                        Apply filters
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
button.btn-highlight {
    position: relative;
    &::after {
        position: absolute;
        content: '';
        inset: 0;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        box-shadow: 0 0 0 0.5rem hsla(var(--ilt-yellow-sec-hsl) / 60%);
        animation: btn-highlight-anim 2s ease-in-out infinite;
        opacity: 1;
    }
}
@keyframes btn-highlight-anim {
    0% {
        box-shadow: 0 0 0 0 color-mix(in lch, var(--bs-btn-border-color), rgba(0 0 0 / 70%) 15%);
    }
    100% {
        box-shadow: 0 0 0 1rem color-mix(in lch, var(--bs-btn-bg), transparent 100%);
    }
}
</style>
