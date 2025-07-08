<script setup lang="ts">
import type { BaseCategoryType } from '@/scripts/types/ModelTypes';

defineProps<{
    title?: string;
    items?: BaseCategoryType[];
}>();

// Map of names to their replacements (well...) @todo make these corrections in database.
const nameMapping: Record<string, string> = {
    'Not Provided': 'Not Specified',
    // Add other mappings as needed
};

// Helper function to Return the mapped name or the original if no match:
const mapName = (name: string): string => {
    return nameMapping[name] || name;
};
</script>

<template>
    <dl v-if="items">
        <dt>{{ title }}</dt>
        <dd
            v-for="item in items"
            :key="item.id"
        >
            <abbr
                v-if="item.label"
                :title="item.label"
            >
                {{ mapName(item.name) }}
            </abbr>
            <span v-else>
                {{ mapName(item.name) }}
            </span>
        </dd>
    </dl>
</template>
