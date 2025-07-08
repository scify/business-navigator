<script setup lang="ts">
import { ref, watch } from 'vue';
import type { Filter } from '@/scripts/types/FilterTypes';

const props = defineProps<{
    filter: Filter;
    modelValue: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

// Local state to handle v-model changes
const localValue = ref(props.modelValue);

// Default option for all filters.
const defaultOption = `All ${props.filter.label.plural}`;

// Watch for changes to localValue and emit them
watch(localValue, (newValue) => {
    emit('update:modelValue', newValue);
});

// Updates local value if the prop modelValue changes externally:
watch(
    () => props.modelValue,
    (newVal) => {
        localValue.value = newVal;
    },
);
</script>

<template>
    <div class="col">
        <label
            class="visually-hidden"
            :for="filter.slug"
            >{{ filter.label.singular }}</label
        >
        <select
            :id="filter.slug"
            v-model="localValue"
            class="form-select"
        >
            <option value="">{{ defaultOption }}</option>
            <option
                v-for="option in filter.options"
                :key="option.name"
                :value="option.slug"
            >
                {{ option.name }}
            </option>
        </select>
    </div>
</template>
