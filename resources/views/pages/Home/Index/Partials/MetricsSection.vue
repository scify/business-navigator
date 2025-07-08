<script setup lang="ts">
import NumberIconWidget from '@/views/components/Widgets/NumberIconWidget.vue';
defineProps<{
    organisationCount: number;
    countryCount: number;
    industryCount: number;
}>();
</script>

<template>
    <section
        id="section-metrics"
        class="section-metrics"
    >
        <div class="container-xxl">
            <div class="pb-5 text-center text-ilt-blue">
                <h2>The European AI Landscape in Numbers</h2>
            </div>
            <div class="widget-group row row-gap-4 px-1 px-md-0">
                <NumberIconWidget
                    title="AI-Powered Organisations"
                    icon="organisation"
                    :count="organisationCount"
                />
                <NumberIconWidget
                    title="AI-Driven Industries"
                    icon="industry"
                    :count="industryCount"
                />
                <NumberIconWidget
                    title="AI-Enhanced Countries"
                    icon="country"
                    :count="countryCount"
                />
            </div>
        </div>
    </section>
</template>
