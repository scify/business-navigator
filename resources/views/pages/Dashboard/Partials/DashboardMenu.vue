<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2 as LucideBuilding2 } from 'lucide-vue-next';
</script>

<template>
    <div class="list-group">
        <Link
            :href="route('dashboard')"
            class="list-group-item list-group-item-action"
            :class="{ active: route().current('dashboard') }"
        >
            Dashboard
        </Link>
        <Link
            :href="route('dashboard.organisations.index')"
            class="list-group-item list-group-item-action d-flex align-items-center"
            :class="{ active: route().current('dashboard.organisations.*') }"
        >
            <LucideBuilding2
                class="me-2"
                :size="20"
            />
            Manage Organisations
        </Link>
    </div>
</template>

<style scoped>
.list-group-item.active {
    /* Override default active text color to ensure contrast against the yellow background */
    color: var(--bs-dark);
}
</style>
