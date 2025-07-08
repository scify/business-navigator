<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    status: number;
}>();

const title = computed(() => {
    return {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Page Not Found',
        403: '403: Forbidden',
    }[props.status];
});

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our mighty servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Apologies, you are forbidden from accessing this page.',
    }[props.status];
});
</script>

<template>
    <main>
        <section
            id="section-error"
            class="section-error"
        >
            <div class="container-xxl">
                <div class="row align-items-center min-dvh-100">
                    <div class="col">
                        <div>
                            <h1 class="text-center">{{ title }}</h1>
                            <p class="lead text-center">
                                {{ description }}
                            </p>
                            <p class="text-center">
                                <a
                                    href="/public"
                                    class="link-ilt-blue-sec"
                                    >Navigate back to home safety</a
                                >
                            </p>
                        </div>
                        <div class="pt-5">
                            <p class="small text-center">Please note that our error page is under construction.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>
