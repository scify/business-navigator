<script lang="ts" setup>
import type { Organisation } from '@/scripts/types/ModelTypes';
import { useOrganisationDescription } from '@/scripts/composables/useOrganisationDescription';

const props = defineProps<{
    organisation: Organisation;
}>();

const { countryWithArticle } = useOrganisationDescription(props.organisation);

// Default classes
// const originalClasses = 'section-hero bg-primary bg-gradient';
const classes = 'bg-white bg-opacity-100 bg-3d-grid-container';
</script>

<template>
    <section
        id="section-hero"
        :class="['section-hero', classes]"
    >
        <div class="bg-3d-grid bg-opacity-50">
            <div class="container-xxl px-4">
                <div class="small">
                    <span v-if="organisation.organisation_types">
                        <span
                            v-for="(type, index) in organisation.organisation_types"
                            :key="type.id"
                        >
                            {{ index > 0 ? ', ' : '' }}
                            <abbr
                                v-if="type.label"
                                :title="type.label"
                                >{{ type.name }}</abbr
                            >
                            <span v-else>{{ type.name }}</span>
                        </span>
                    </span>
                    <span v-else> AI-Powered Organisation </span>

                    <span v-if="organisation.country"> based in {{ countryWithArticle }} </span>
                </div>
                <div>
                    <h1 class="text-ilt-blue">{{ organisation.name }}</h1>
                    <p class="lead">{{ organisation.short_description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
