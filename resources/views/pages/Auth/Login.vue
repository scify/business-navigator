<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Checkbox from '@/views/components/UI/Checkbox.vue';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import InputError from '@/views/components/UI/InputError.vue';
import InputLabel from '@/views/components/UI/InputLabel.vue';
import PrimaryButton from '@/views/components/UI/PrimaryButton.vue';
import TextInput from '@/views/components/UI/TextInput.vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    console.log('LOGGING IN');
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};

const clearError = (field: 'email' | 'password') => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <section
            id="section-dashboard-login"
            class="section-dashboard section-dashboard-login"
        >
            <div class="container-xxl">
                <div
                    v-if="status"
                    class="row"
                >
                    <div class="col mb-4 text-sm font-medium text-green-600">
                        <!-- @todo fix status -->
                        {{ status }}
                    </div>
                </div>
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <!-- Decoration swirl -->
                    <div class="col-12 col-sm-4 h-100">
                        <!-- @todo add decoration -->
                    </div>
                    <!-- / Decoration swirl -->
                    <!-- Login form -->
                    <div class="col-12 col-sm-7 col-md-6 h-100">
                        <form
                            class="needs-validation"
                            @submit.prevent="submit"
                        >
                            <div class="d-flex flex-column justify-content-center">
                                <div class="mb-4">
                                    <h1 class="fw-normal lead">Log in to the ILT</h1>
                                </div>
                                <div>
                                    <!-- Email -->
                                    <InputLabel
                                        for="email"
                                        value="Email"
                                    />

                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-1 d-block w-100"
                                        :class="{ 'is-invalid': form.errors.email }"
                                        placeholder="Enter a valid email address"
                                        required
                                        autofocus
                                        autocomplete="username"
                                        @input="clearError('email')"
                                    />

                                    <InputError
                                        class="invalid-feedback mt-2"
                                        :message="form.errors.email"
                                    />
                                </div>

                                <div class="mt-4">
                                    <!-- Password -->
                                    <InputLabel
                                        for="password"
                                        value="Password"
                                    />

                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 d-block w-100"
                                        :class="{ 'is-invalid': form.errors.password }"
                                        required
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                    />

                                    <InputError
                                        class="invalid-feedback mt-2"
                                        :message="form.errors.password"
                                    />
                                </div>

                                <div class="mt-4 d-flex align-items-center">
                                    <div class="me-auto form-check">
                                        <!-- Remember me -->
                                        <Checkbox
                                            id="rememberMe"
                                            v-model:checked="form.remember"
                                            name="remember"
                                        />
                                        <label
                                            for="rememberMe"
                                            class="form-check-label"
                                            >Remember me</label
                                        >
                                    </div>
                                    <div>
                                        <!-- Forgot password -->
                                        <Link
                                            v-if="canResetPassword"
                                            :href="route('password.request')"
                                            class="focus-ring rounded-1 p-1 link-opacity-75 link-opacity-100-hover"
                                        >
                                            Forgot password?
                                        </Link>
                                    </div>
                                </div>

                                <div class="mt-4 d-grid d-md-block">
                                    <!-- Login button -->
                                    <PrimaryButton
                                        class="some-class focus-ring"
                                        :type="'submit'"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        Log in
                                    </PrimaryButton>
                                </div>
                                <div class="mt-2 text-center text-md-start">
                                    Don't have an account?
                                    <Link
                                        :href="route('register')"
                                        class="focus-ring rounded-1 py-1 link-opacity-75 link-opacity-100-hover"
                                    >
                                        Register
                                    </Link>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- / Login form -->
                </div>
            </div>
        </section>
    </GuestLayout>
</template>
