<script setup lang="ts">
import AuthenticatedLayout from '@/views/layouts/Auth/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Organisation } from '@/scripts/types/ModelTypes';
import DashboardMenu from '../../Partials/DashboardMenu.vue';
import DashboardOrganisationsTable from '../Partials/DashboardOrganisationsTable.vue';
import Breadcrumbs from '../../Partials/Breadcrumbs.vue';

const { organisations } = defineProps<{ organisations: Organisation[] }>();

const breadcrumbs = [{ name: 'Dashboard', path: route('dashboard') }, { name: 'Manage Organisations' }];
</script>

<template>
    <Head title="Manage Organisations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="border-3 border-bottom bg-white">
                <div class="container-xxl py-5">
                    <h1 class="text-ilt-blue">Dashboard</h1>
                </div>
            </div>
        </template>

        <section class="section-dashboard-organisations-index">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-md-3 mb-4 mb-md-0">
                        <DashboardMenu />
                    </div>
                    <div class="col-md-9">
                        <Breadcrumbs :crumbs="breadcrumbs" />
                        <h2 class="text-ilt-blue mt-4">Manage Organisations</h2>
                        <DashboardOrganisationsTable :organisations="organisations" />
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
