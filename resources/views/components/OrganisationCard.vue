<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Organisation } from '@/scripts/types/ModelTypes';
import { LucideArrowRight, LucideImageOff, LucideMapPin } from 'lucide-vue-next';

defineProps<{
    organisation: Organisation;
}>();
</script>
<template>
    <li class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">
        <div class="card organisation-card mb-4 bg-white border-ilt-gray-300 border-1">
            <div class="card-img">
                <div
                    v-if="organisation.logo"
                    class="img-logo"
                >
                    <img
                        :src="'/media/logos/' + organisation.logo.filename"
                        class=""
                        alt=""
                        loading="lazy"
                        height="160"
                    />
                </div>
                <div
                    v-else
                    class="img-placeholder"
                >
                    <LucideImageOff :size="72" />
                </div>
            </div>

            <div class="card-body border-ilt-gray-300 border-top position-relative">
                <a
                    v-if="organisation.country"
                    class="btn border border-ilt-gray-300 border-1 px-1 d-inline-flex align-items-center justify-content-start small card-location"
                >
                    <LucideMapPin
                        absolute-stroke-width
                        :stroke-width="1"
                    />
                    <span class="small ps-1 pe-2">
                        {{ organisation.country.name }}
                    </span>
                </a>
                <div class="p-limit mt-2">
                    <h3 class="card-title h4 mt-3 fw-bold">{{ organisation.name }}</h3>
                    <p class="card-description">{{ organisation.short_description }}</p>
                </div>
                <Link
                    :href="route('organisations.show', organisation.slug)"
                    class="btn btn-ilt-yellow-sec w-100 d-flex text-center flex-grow-1"
                >
                    <span class="m-auto"> Learn more </span>
                    <LucideArrowRight />
                </Link>
            </div>
        </div>
    </li>
</template>
