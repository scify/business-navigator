<script setup lang="ts">
import AuthenticatedLayout from '@/views/layouts/Auth/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import type { Organisation } from '@/scripts/types/ModelTypes';
import DashboardMenu from '../../Partials/DashboardMenu.vue';
import Breadcrumbs from '../../Partials/Breadcrumbs.vue';
import { computed, defineAsyncComponent } from 'vue';
// SSR-compatible MarkdownEditor import
const MarkdownEditor = defineAsyncComponent(() => import('@/views/components/MarkdownEditor.vue'));

import type { SolutionType, TechnologyType } from '@/scripts/types/ModelTypes';

const { organisation, solutionTypes, technologyTypes, shortDescriptionLimit } = defineProps<{
    organisation: Organisation;
    solutionTypes: SolutionType[];
    technologyTypes: TechnologyType[];
    shortDescriptionLimit: number;
}>();

const shortDescriptionRemaining = computed(() => shortDescriptionLimit - form.short_description.length);

const form = useForm({
    short_description: organisation.short_description || '',
    description: organisation.description || '',
    solution_types: organisation.solution_types?.map((st) => st.id) || [],
    technology_types: organisation.technology_types?.map((tt) => tt.id) || [],
});

const submit = () => {
    form.patch(route('dashboard.organisations.update', { organisation: organisation.id }));
};

const breadcrumbs = [
    { name: 'Dashboard', path: route('dashboard') },
    { name: 'Manage Organisations', path: route('dashboard.organisations.index') },
    { name: `Edit ${organisation.name}` },
];
</script>

<template>
    <Head :title="`Edit ${organisation.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="border-3 border-bottom bg-white">
                <div class="container-xxl py-5">
                    <h1 class="text-ilt-blue">Dashboard</h1>
                </div>
            </div>
        </template>

        <section class="section-dashboard-organisations-edit">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-md-3 mb-4 mb-md-0">
                        <DashboardMenu />
                    </div>
                    <div class="col-md-9">
                        <Breadcrumbs :crumbs="breadcrumbs" />
                        <h2 class="text-ilt-blue mt-4">Edit {{ organisation.name }}</h2>
                        <form @submit.prevent="submit">
                            <!-- General Information -->
                            <div class="mb-2">
                                <h3 class="text-ilt-blue mt-4">General Information</h3>
                                <p class="text-muted mb-4">Provide general information about the organisation.</p>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <label
                                            for="short_description"
                                            class="form-label"
                                            >Short Description <span class="text-danger">*</span></label
                                        >
                                        <small
                                            id="shortDescriptionCharCount"
                                            class="text-end"
                                            >{{ shortDescriptionRemaining }} characters remaining</small
                                        >
                                    </div>
                                    <input
                                        id="short_description"
                                        v-model="form.short_description"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': form.errors.short_description }"
                                        required
                                        :maxlength="shortDescriptionLimit"
                                        aria-describedby="shortDescriptionHelp shortDescriptionCharCount"
                                    />
                                    <div
                                        v-if="form.errors.short_description"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.short_description }}
                                    </div>
                                    <small
                                        id="shortDescriptionHelp"
                                        class="form-text text-muted"
                                        >A concise, one-sentence description of the organisation.</small
                                    >
                                </div>
                                <div class="mb-3">
                                    <label
                                        for="description"
                                        class="form-label"
                                        >Description <span class="text-danger">*</span></label
                                    >
                                    <MarkdownEditor
                                        v-model="form.description"
                                        required
                                        aria-describedby="descriptionHelp"
                                    />
                                    <div
                                        v-if="form.errors.description"
                                        class="invalid-feedback d-block"
                                    >
                                        {{ form.errors.description }}
                                    </div>
                                    <small
                                        id="descriptionHelp"
                                        class="form-text text-muted"
                                        >A detailed description of the organisation, its mission, and activities.</small
                                    >
                                </div>
                            </div>

                            <!-- Categorisation Attributes -->
                            <div class="mb-2">
                                <h3 class="text-ilt-blue mt-4">Categorisation Attributes</h3>
                                <p class="text-muted mb-4">
                                    These attributes help categorize the organisation and improve its discoverability
                                    within the application.
                                </p>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <label
                                            for="solution_types"
                                            class="form-label"
                                            >Solution Types <span class="text-danger">*</span></label
                                        >
                                        <small
                                            id="solutionTypesCount"
                                            class="text-end"
                                            >{{ form.solution_types.length }} selected</small
                                        >
                                    </div>
                                    <select
                                        id="solution_types"
                                        v-model="form.solution_types"
                                        class="form-select"
                                        multiple
                                        :class="{ 'is-invalid': form.errors.solution_types }"
                                        required
                                        aria-describedby="solutionTypesHelp solutionTypesCount"
                                    >
                                        <option
                                            v-for="solutionType in solutionTypes"
                                            :key="solutionType.id"
                                            :value="solutionType.id"
                                        >
                                            {{ solutionType.name }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.solution_types"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.solution_types }}
                                    </div>
                                    <small
                                        id="solutionTypesHelp"
                                        class="form-text text-muted"
                                        >Select the AI solution types that best describe the organisation's
                                        offerings.</small
                                    >
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <label
                                            for="technology_types"
                                            class="form-label"
                                            >Technology Types <span class="text-danger">*</span></label
                                        >
                                        <small
                                            id="technologyTypesCount"
                                            class="text-end"
                                            >{{ form.technology_types.length }} selected</small
                                        >
                                    </div>
                                    <select
                                        id="technology_types"
                                        v-model="form.technology_types"
                                        class="form-select"
                                        multiple
                                        :class="{ 'is-invalid': form.errors.technology_types }"
                                        required
                                        aria-describedby="technologyTypesHelp technologyTypesCount"
                                    >
                                        <option
                                            v-for="technologyType in technologyTypes"
                                            :key="technologyType.id"
                                            :value="technologyType.id"
                                        >
                                            {{ technologyType.name }}
                                        </option>
                                    </select>
                                    <div
                                        v-if="form.errors.technology_types"
                                        class="invalid-feedback"
                                    >
                                        {{ form.errors.technology_types }}
                                    </div>
                                    <small
                                        id="technologyTypesHelp"
                                        class="form-text text-muted"
                                        >Select the technologies that best describe the organisation's offerings.</small
                                    >
                                </div>
                            </div>

                            <!-- Save -->
                            <div class="d-flex justify-content-end mt-4">
                                <a
                                    :href="route('organisations.show', organisation.slug)"
                                    class="btn btn-outline-secondary me-3"
                                    target="_blank"
                                    >View</a
                                >
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    :disabled="!form.isDirty || form.processing"
                                >
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
