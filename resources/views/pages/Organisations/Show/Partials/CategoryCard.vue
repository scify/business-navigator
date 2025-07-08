<script setup lang="ts">
import type { BaseCategoryType } from '@/scripts/types/ModelTypes';

defineProps<{
    title?: string;
    items?: BaseCategoryType[];
}>();

// Map of names to their replacements (well...)
const nameMapping: Record<string, string> = {
    'Not Provided': 'Not IS WRONG', // this could be deleted.
    // Add other mappings as needed
};

// Helper function to Return the mapped name or the original if no match:
const mapName = (name: string): string => {
    return nameMapping[name] || name;
};
</script>

<template>
    <div
        v-if="items"
        class="col-sm-6 col-lg-4 col-xl-3"
    >
        <div class="card border border-1 bg-white bg-gradient bg-opacity-75 rounded-3 mb-4 organisation-category-card">
            <div class="card-body">
                <h3 class="h5 text-ilt-blue fw-medium">{{ title }}</h3>
                <ul class="list-unstyled">
                    <li
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
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
