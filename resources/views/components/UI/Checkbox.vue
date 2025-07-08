<script setup lang="ts">
import { computed } from 'vue';

const emit = defineEmits<{
    (e: 'update:checked', value: boolean): void;
}>();

const props = defineProps<{
    id: string;
    checked: boolean;
    value?: string | number | boolean | object | null;
}>();

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        :id="id"
        v-model="proxyChecked"
        type="checkbox"
        :value="value"
        class="form-check-input"
    />
</template>
