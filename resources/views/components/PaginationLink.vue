<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
/* Pagination Link Component */
import { PageLink } from '@/scripts/types/PaginationTypes';

defineProps<{
    link: PageLink;
    isMobile: boolean;
}>();

/**
 * Process links to add appropriate aria-labels for Previous & Next Items.
 * On mobile, simplify the content to just show the label.
 */
const getLinkContent = (label: string, isMobile: boolean) => {
    // For desktop, process special characters
    if (label.includes('&laquo;')) {
        if (isMobile) {
            return { content: 'Previous', ariaLabel: null };
        }
        return { content: '«', ariaLabel: 'Previous' };
    } else if (label.includes('&raquo;')) {
        if (isMobile) {
            return { content: 'Next', ariaLabel: null };
        }
        return { content: '»', ariaLabel: 'Next' };
    }

    return { content: label, ariaLabel: null };
};
</script>

<template>
    <li
        :class="['page-item', { active: link.active, disabled: !link.url }]"
        :aria-current="link.active ? 'page' : undefined"
    >
        <template v-if="link.url">
            <Link
                class="page-link"
                :href="`${link.url}`"
                :aria-label="getLinkContent(link.label, isMobile).ariaLabel || undefined"
                preserve-state
            >
                <template v-if="getLinkContent(link.label, isMobile).ariaLabel">
                    <span aria-hidden="true">{{ getLinkContent(link.label, isMobile).content }}</span>
                </template>
                <template v-else>
                    {{ getLinkContent(link.label, isMobile).content }}
                </template>
            </Link>
        </template>
        <template v-else>
            <span
                class="page-link"
                :aria-label="getLinkContent(link.label, isMobile).ariaLabel || undefined"
            >
                <template v-if="getLinkContent(link.label, isMobile).ariaLabel">
                    <span aria-hidden="true">{{ getLinkContent(link.label, isMobile).content }}</span>
                </template>
                <template v-else>
                    {{ getLinkContent(link.label, isMobile).content }}
                </template>
            </span>
        </template>
    </li>
</template>
