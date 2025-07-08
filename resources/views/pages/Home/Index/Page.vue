<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import HeroSection from '@/views/pages/Home/Index/Partials/HeroSection.vue';
import MetricsSection from '@/views/pages/Home/Index/Partials/MetricsSection.vue';
import InsightsSection from '@/views/pages/Home/Index/Partials/InsightsSection.vue';
import JoinSection from '@/views/pages/Home/Index/Partials/JoinSection.vue';
import LinksSection from '@/views/pages/Home/Index/Partials/LinksSection.vue';

defineProps<{
    organisationCount: number;
    countryCount: number;
    industryCount: number;
}>();
</script>

<template>
    <Head>
        <meta
            name="description"
            content="Explore the European AI Landscape"
        />
    </Head>
    <GuestLayout>
        <HeroSection />
        <MetricsSection
            :organisation-count="organisationCount"
            :country-count="countryCount"
            :industry-count="industryCount"
        />
        <InsightsSection />
        <JoinSection />
        <LinksSection />
    </GuestLayout>
</template>
