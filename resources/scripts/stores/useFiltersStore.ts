// resources/scripts/stores/useFiltersStore.ts
import type { SelectedFilters } from '@/scripts/types/FilterTypes';
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useFiltersStore = defineStore('filters', () => {
    // State: Using ref to store reactive state
    const currentFilters = ref<SelectedFilters>({
        organisation_type: null,
        industry_sector: null,
        enterprise_function: null,
        solution_type: null,
        technology_type: null,
        offer_type: null,
        country: null,
    });

    const appliedFilters = ref<SelectedFilters>({
        organisation_type: null,
        industry_sector: null,
        enterprise_function: null,
        solution_type: null,
        technology_type: null,
        offer_type: null,
        country: null,
    });

    /**
     * Determine if the current filters differ from the applied filters.
     * @returns {boolean} true if there's any difference, false otherwise.
     */
    const filtersChanged = computed<boolean>(() => {
        return Object.keys(appliedFilters.value).some((key) => {
            const typedKey = key as keyof SelectedFilters;
            return currentFilters.value[typedKey] !== appliedFilters.value[typedKey];
        });
    });

    /**
     * Update the currentFilters with partial values.
     * @param {Partial<SelectedFilters>} filters - Partial set of updated filter values.
     * @returns {void}
     */
    function setCurrentFilters(filters: Partial<SelectedFilters>): void {
        currentFilters.value = { ...currentFilters.value, ...filters };
    }

    /**
     * Set the appliedFilters to a full set of SelectedFilters.
     * Usually called after a successful application or data refresh.
     * @param {SelectedFilters} filters - The new applied filters state.
     * @returns {void}
     */
    function setAppliedFilters(filters: SelectedFilters): void {
        appliedFilters.value = { ...filters };
    }

    return {
        currentFilters,
        appliedFilters,
        filtersChanged,
        setCurrentFilters,
        setAppliedFilters,
    };
});
