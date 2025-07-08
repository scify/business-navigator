<script setup lang="ts">
import { LucideUsers } from 'lucide-vue-next';
defineProps<{
    employees?: string;
}>();
</script>

<template>
    <div
        v-if="employees"
        class="col-sm-6 col-lg-4 col-xl-3"
    >
        <div class="card border border-1 bg-white bg-gradient bg-opacity-100 rounded-3 mb-4 organisation-details-card">
            <div class="card-body">
                <div class="card-heading d-flex flex-row">
                    <div class="">
                        <h3 class="h6 text-ilt-blue fw-medium">Employees</h3>
                    </div>
                    <div class="card-icon lucide-icon ms-auto opacity-75 text-ilt-blue text-size-24">
                        <LucideUsers />
                    </div>
                </div>
                <div class="card-content d-flex flex-column">
                    <span class="card-detail text-ilt-blue fw-medium"> {{ employees }} </span>
                    <span class="card-help small text-ilt-blue-sec"> Team size </span>
                </div>
            </div>
        </div>
    </div>
</template>
