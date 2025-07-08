<script lang="ts" setup>
import type { Organisation } from '@/scripts/types/ModelTypes';

defineProps<{
    organisation: Organisation;
}>();
</script>

<template>
    <section
        id="section-hero"
        class="section-hero bg-white bg-opacity-20"
    >
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
                <span v-if="organisation.country">
                    based in
                    {{ organisation.country.name === 'Netherlands' ? 'the ' : '' }}
                    {{ organisation.country.name }}
                </span>
            </div>
            <div>
                <h1>{{ organisation.name }}</h1>
                <p class="lead">{{ organisation.short_description }}</p>
            </div>
        </div>
    </section>
</template>
