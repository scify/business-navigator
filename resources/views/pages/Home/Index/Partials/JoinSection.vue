<script setup lang="ts">
import { LucideSparkle } from 'lucide-vue-next';
</script>

<template>
    <section
        id="section-join"
        class="section-join bg-light bg-gradient"
    >
        <div class="container-xxl px-4 px-xxl-0">
            <div class="row">
                <div class="col-12">
                    <h2>Drive AI Transformation</h2>
                    <p class="lead">Empower Progress, Define Tomorrow</p>
                </div>
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row gap-4 pt-4">
                        <div class="col">
                            <figure class="generative-ai-warning">
                                <img
                                    class="img-fluid w-100 lower-saturation"
                                    loading="lazy"
                                    src="@/images/main-business-navigator.webp"
                                    srcset="
                                        @/images/main-business-navigator.webp    1x,
                                        @/images/main-business-navigator@2x.webp 2x
                                    "
                                    alt="A compass made of crystal clear, transparent, glass representing the idea of Business Navigator."
                                    width="648"
                                    height="384"
                                />
                                <figcaption
                                    class="d-flex z-1 align-items-center justify-content-start gap-1 text-white text-uppercase"
                                >
                                    <LucideSparkle /> <span>Created with Generative AI</span>
                                </figcaption>
                            </figure>
                        </div>
                        <div class="col d-flex flex-column d-md-block row-gap-4 p-2">
                            <p>
                                The Business Navigator empowers organizations to become leaders in Europe’s AI
                                transformation. With actionable insights, trusted tools, and ethical frameworks,
                                businesses can drive innovation, confidently adapt to change, and lead in shaping the
                                future of AI.
                            </p>
                            <p>
                                Europe’s AI revolution is redefining industries, and the Business Navigator equips you
                                to play a central role. From tailored strategies to cutting-edge resources, it ensures
                                your business thrives while leading responsibly in a rapidly advancing ecosystem.
                            </p>
                            <a
                                target="_blank"
                                href="https://www.aiodp.ai/partners-page/#onboarding_section"
                                class="btn btn-primary btn-xl mt-0 mt-md-4"
                                >Lead the Way</a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
section {
    --bs-bg-opacity: 0.5;
}
.lower-saturation {
    filter: grayscale(60%) saturate(110%);
}

figure.generative-ai-warning {
    display: grid;
    grid-template-columns: 1fr;
    img,
    figcaption {
        grid-row-start: 1;
        grid-column-start: 1;
    }
    figcaption {
        mix-blend-mode: hard-light;
        font-size: 0.875rem;
        align-self: end;
        line-height: 1;
        opacity: 0.9;
        svg {
            line-height: 1;
            width: 0.875rem;
            margin-inline-start: 0.5rem;
        }
    }
}
</style>
