<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    crumbs: { name: string; path?: string }[];
}>();

const breadcrumbs = computed(() => {
    return props.crumbs.map((crumb, index) => ({
        ...crumb,
        isLast: index === props.crumbs.length - 1,
    }));
});
</script>

<template>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li
                v-for="(crumb, index) in breadcrumbs"
                :key="index"
                class="breadcrumb-item"
                :class="{ active: crumb.isLast }"
                :aria-current="crumb.isLast ? 'page' : undefined"
            >
                <Link
                    v-if="!crumb.isLast"
                    :href="crumb.path!"
                    >{{ crumb.name }}</Link
                >
                <span v-else>{{ crumb.name }}</span>
            </li>
        </ol>
    </nav>
</template>
