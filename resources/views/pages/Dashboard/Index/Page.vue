<script setup lang="ts">
import AuthenticatedLayout from '@/views/layouts/Auth/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import DashboardMenu from '../Partials/DashboardMenu.vue';

const {
    auth: { user },
} = usePage().props;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="border-3 border-bottom bg-white">
                <div class="container-xxl py-5">
                    <h1 class="text-ilt-blue">Dashboard</h1>
                </div>
            </div>
        </template>

        <section class="section-dashboard-index">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-md-3 mb-4 mb-md-0">
                        <DashboardMenu />
                    </div>
                    <div class="col-md-9">
                        <h2 class="text-ilt-blue">Welcome {{ user.name }}!</h2>
                        <p class="lead">You're logged in!</p>
                        <p
                            v-if="user.id === 1"
                            class="max-ch-70"
                        >
                            You are the super user with the glorious {{ user.email }} email! Now, this is unsafe, but
                            you are here because you have to. Please do import any files needed. So, check if uploading
                            is enabled.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
