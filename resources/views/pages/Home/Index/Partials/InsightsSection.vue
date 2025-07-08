<script lang="ts" setup>
import { LucideChartNoAxesCombined, LucideSearchCode } from 'lucide-vue-next';
</script>
<template>
    <section
        id="section-text"
        class="section-explore"
    >
        <div class="container-xxl px-4 px-xxl-0">
            <h2>Unlock AI Insights for Smarter Strategies</h2>
            <p class="lead">Explore Key Trends Shaping Europe's AI Ecosystem</p>
            <div class="d-flex pt-4">
                <div class="col-0"></div>
                <div class="col-12">
                    <p class="d-flex flex-row align-items-center gap-5 pt-4">
                        <span
                            class="d-none d-sm-block text-secondary has-icon"
                            aria-hidden="true"
                        >
                            <LucideChartNoAxesCombined :stroke-width="2" />
                        </span>
                        <span>
                            Understanding Europe’s AI landscape is essential to unlocking its potential. The Business
                            Navigator connects trends, policies, and emerging opportunities to your business goals,
                            transforming insights into actionable strategies that drive real-world results.
                        </span>
                    </p>
                    <p class="d-flex flex-row align-items-center gap-5 pt-4">
                        <span
                            class="d-none d-sm-block text-secondary has-icon"
                            aria-hidden="true"
                        >
                            <LucideSearchCode
                                :size="36"
                                :stroke-width="2"
                            />
                        </span>
                        <span>
                            Designed for businesses of all sizes and public institutions, the Business Navigator
                            simplifies Europe’s AI transformation. Stay ahead with data-driven guidance that helps you
                            explore trends, adapt to changes, and seize new opportunities.
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
<style scoped>
.has-icon {
    font-size: 3rem;
    min-width: 1.25lh;
    svg {
        height: 1.25lh;
        width: 1.25lh;
    }
}
</style>
