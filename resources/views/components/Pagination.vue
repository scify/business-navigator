<script setup lang="ts">
/* Pagination Component */
import { PageLink } from '@/scripts/types/PaginationTypes';
import PaginationLink from './PaginationLink.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface Props {
    // Note that '?' equals undefined, therefore, optional.
    links?: PageLink[];
    ariaLabel?: string;
}

// Props Default Values for type-based declaration:
// @link https://vuejs.org/guide/typescript/composition-api.html#props-default-values
const props = withDefaults(defineProps<Props>(), {
    links: undefined,
    ariaLabel: 'Pages Navigation',
});

// Track if we're on mobile view
const isMobile = ref(false);

// Function to check if we're on mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768; // Adjust breakpoint as needed
};

// Set up responsive behaviour
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

// Process links for display
const displayLinks = computed(() => {
    if (!props.links?.length) return [];

    // For desktop or if we have few links, show all
    if (!isMobile.value || props.links.length <= 5) {
        return props.links;
    }

    // For mobile, show: previous, first page, active page, last page, next
    const processed = [];

    // Find the active page index
    const activeIndex = props.links.findIndex((link) => link.active);

    // The first link is always "Previous"
    if (props.links[0]) {
        processed.push(props.links[0]);
    }

    // First page (the one after "Previous")
    // Adds it only if it's not the active page
    if (props.links[1]) {
        processed.push(props.links[1]);
    }

    // Add the active page (if it's not already included as the first or last page)
    if (activeIndex !== 1 && activeIndex !== props.links.length - 2 && activeIndex !== -1) {
        processed.push(props.links[activeIndex]);
    }

    // Last page (the one before "Next")
    // Adds it only if it's not the active page
    const lastPageIndex = props.links.length - 2;
    if (props.links[lastPageIndex]) {
        processed.push(props.links[lastPageIndex]);
    }

    // The last link is always "Next"
    if (props.links[props.links.length - 1]) {
        processed.push(props.links[props.links.length - 1]);
    }

    return processed;
});
</script>

<template>
    <nav
        v-if="links?.length"
        :aria-label="props.ariaLabel"
    >
        <ul class="pagination">
            <template
                v-for="link in displayLinks"
                :key="link.label"
            >
                <PaginationLink
                    :link="link"
                    :is-mobile="isMobile"
                />
            </template>
        </ul>
    </nav>
</template>
