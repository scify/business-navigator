<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/views/layouts/Auth/AuthenticatedLayout.vue';

defineProps<{
    fileExists: boolean;
    fileUrl: string | null;
}>();

const form = useForm({
    csvFile: null as File | null,
});

function submit() {
    form.post(route('csv.upload'));
}

function handleFileInput(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target && target.files) {
        form.csvFile = target.files[0];
    }
}
</script>

<template>
    <Head title="CSV Uploader" />

    <AuthenticatedLayout>
        <section class="section-dashboard-index">
            <div class="container-xxl">
                <div class="row">
                    <div class="col-12">
                        <h1>CSV Management</h1>

                        <!-- File Upload Section -->
                        <div v-if="!fileExists">
                            <form @submit.prevent="submit">
                                <label for="csv-file">Upload a CSV File:</label>
                                <input
                                    id="csv-file"
                                    class="form-check"
                                    type="file"
                                    accept=".csv"
                                    @input="handleFileInput"
                                />
                                <progress
                                    v-if="form.progress"
                                    :value="form.progress.percentage"
                                    max="100"
                                >
                                    {{ form.progress.percentage }}%
                                </progress>
                                <button
                                    class="btn btn-primary"
                                    type="submit"
                                >
                                    Upload CSV
                                </button>
                            </form>
                        </div>

                        <!-- File Management Section -->
                        <div v-else>
                            <p>File "<strong>seed.csv</strong>" is already uploaded.</p>
                            <a
                                v-if="fileUrl"
                                :href="fileUrl"
                                class="btn btn-success"
                                download
                            >
                                Download CSV
                            </a>
                            <button class="btn btn-danger">Delete CSV</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
