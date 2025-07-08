<script setup lang="ts">
import { OrganisationGeoProperties } from '@/scripts/types/ModelTypes';
import { Link } from '@inertiajs/vue3';
import { LucideArrowRight, LucideSignpost } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    organisation: OrganisationGeoProperties;
}

const props = defineProps<Props>();

/**
 * Clean address by removing the country name if it's already included
 * and removing any trailing commas or whitespace
 */
const cleanAddress = computed(() => {
    if (!props.organisation.address || !props.organisation.country) {
        return props.organisation.address;
    }

    const address = props.organisation.address;
    const country = props.organisation.country;

    // Remove country from the end of the address (case-insensitive)
    const addressWithoutCountry = address.replace(
        new RegExp(`\\n\\s*${country.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*$`, 'i'),
        '',
    );

    // Clean up any trailing newlines and whitespace
    return addressWithoutCountry.replace(/[\n\s]+$/, '');
});
</script>

<template>
    <div class="organisation-popup">
        <h3 class="organisation-name">{{ organisation.name }}</h3>
        <div class="organisation-details">
            <dl class="list-unstyled">
                <dt
                    v-if="organisation.organisation_types"
                    class="visually-hidden"
                >
                    Type
                </dt>
                <dd
                    v-if="organisation.organisation_types"
                    class="types"
                >
                    {{ organisation.organisation_types.join(', ') }}
                </dd>
                <dt
                    v-if="organisation.address"
                    class="visually-hidden"
                >
                    Address
                </dt>
                <dd
                    v-if="organisation.address"
                    class="address"
                >
                    <p class="d-flex">
                        <LucideSignpost :size="64" />
                        <span class="ms-3">
                            {{ cleanAddress }} <br />
                            {{ organisation.country }}
                        </span>
                    </p>
                </dd>
            </dl>
        </div>
        <div class="organisation-cta mt-4">
            <Link
                :href="route('organisations.show', organisation.slug)"
                class="btn btn-outline-dark w-100 d-flex text-center flex-grow-1"
            >
                <span class="m-auto"> Learn more </span>
                <LucideArrowRight />
            </Link>
        </div>
    </div>
</template>

<style scoped>
.organisation-name {
    font-weight: 700;
}
.organisation-details {
    margin-top: -0.5em;
    font-size: 0.925rem;
    dd.types {
        font-weight: 500;
        font-size: 1rem;
    }
    dd.address {
        font-weight: 400;
        font-size: 1rem;
        max-width: 33ch;
        white-space: pre-line; /* Preserve line breaks from \n characters */
    }
}

.organisation-popup {
    padding: 0.25rem;
}

.organisation-details p {
    margin: 0.25rem 0;
}
</style>
