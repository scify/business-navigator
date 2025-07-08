<script setup lang="ts">
import Vue3Autocounter from 'vue3-autocounter';
import CountryIcon from '@/views/components/Icons/CountryIcon.vue';
import OrganisationIcon from '@/views/components/Icons/OrganisationIcon.vue';
import IndustryIcon from '@/views/components/Icons/IndustryIcon.vue';
import GenericIcon from '@/views/components/Icons/GenericIcon.vue';

const props = withDefaults(
    defineProps<{
        icon?: string;
        title?: string;
        count?: number;
        duration?: number;
        theme?: 'black' | 'blue' | 'white' | 'modern';
    }>(),
    {
        icon: 'favorite',
        title: 'Favorites',
        count: 909,
        duration: 3,
        theme: 'blue',
    },
);
</script>

<template>
    <div class="widget widget-number-icon">
        <div :class="['widget-content', props.theme]">
            <span
                v-if="props.icon === 'country'"
                aria-hidden="true"
                class="ilt-icon"
            >
                <CountryIcon />
            </span>
            <span
                v-else-if="props.icon === 'organisation'"
                aria-hidden="true"
                class="ilt-icon"
            >
                <OrganisationIcon />
            </span>
            <span
                v-else-if="props.icon === 'industry'"
                aria-hidden="true"
                class="ilt-icon"
            >
                <IndustryIcon />
            </span>
            <span
                v-else
                aria-hidden="true"
                class="ilt-icon"
            >
                <GenericIcon />
            </span>
            <dl>
                <dt>{{ props.title }}</dt>
                <dd>
                    <vue3-autocounter
                        ref="counter"
                        :start-amount="0"
                        :end-amount="props.count"
                        :duration="props.duration"
                        separator="."
                        :decimals="0"
                        :autoinit="true"
                    />
                </dd>
                <!--
                <dd>{{ props.count.toLocaleString() }}</dd>
                -->
            </dl>
        </div>
    </div>
</template>
