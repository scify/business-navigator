<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        href: string;
        active?: boolean;
        responsive?: boolean;
        class?: string;
    }>(),
    {
        class: 'btn-primary', // Default class
        responsive: false, // Responsive by default
    },
);

const classes = computed(() => {
    const responsiveClasses = props.responsive ? 'd-block d-sm-inline-flex align-items-center' : '';

    const activeClasses = props.active ? 'text-ilt-blue fw-semibold disabled' : 'text-ilt-blue fw-semibold';

    return `btn btn-xl ${responsiveClasses} ${props.class} ${activeClasses}`.trim();
});
</script>

<template>
    <Link
        :href="href"
        :class="classes"
    >
        <slot />
    </Link>
</template>
