<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import type { PageProps, FlashMessage } from '@/scripts/types';

const page = usePage<PageProps>();
const show = ref(false);
const message = ref<FlashMessage | null>(null);

watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash?.id) {
            message.value = newFlash;
            show.value = true;
            setTimeout(() => {
                show.value = false;
            }, 3000);
        }
    },
    { deep: true },
);

const toastHeaderClass = computed(() => {
    if (!message.value) return '';
    if (message.value.type === 'success') return 'bg-success text-white';
    if (message.value.type === 'error') return 'bg-danger text-white';
    return 'bg-info text-white';
});

const toastTitle = computed(() => {
    if (!message.value) return '';
    if (message.value.type === 'success') return 'Success';
    if (message.value.type === 'error') return 'Error';
    return 'Info';
});
</script>

<template>
    <div
        v-if="show && message"
        class="position-fixed bottom-0 end-0 p-3"
        style="z-index: 11"
    >
        <div
            id="liveToast"
            class="toast show"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
        >
            <div
                class="toast-header"
                :class="toastHeaderClass"
            >
                <strong class="me-auto">{{ toastTitle }}</strong>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    aria-label="Close"
                    @click="show = false"
                ></button>
            </div>
            <div class="toast-body">
                {{ message.message }}
            </div>
        </div>
    </div>
</template>
