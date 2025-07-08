<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/views/layouts/Main/GuestLayout.vue';
import InputError from '@/views/components/UI/InputError.vue';
import InputLabel from '@/views/components/UI/InputLabel.vue';
import PrimaryButton from '@/views/components/UI/PrimaryButton.vue';
import TextInput from '@/views/components/UI/TextInput.vue';

interface ValidationRule {
    required?: boolean;
    type?: string;
    max?: number;
    min?: number;
    regex?: string;
    unique?: string;
}

const props = defineProps<{
    validationRules?: Record<string, ValidationRule>;
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

const clearError = (field: 'name' | 'email' | 'password' | 'password_confirmation') => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

const passwordRules = computed(() => {
    const min = getRule('password', 'min', 8);
    const max = getRule('password', 'max', 255);

    // Customize required rules based on your validation logic
    const required = [
        'lower', // Lowercase letters
        'upper', // Uppercase letters
        'digit', // At least one number
        '[-]', // At least one special character
    ].join('; required: ');

    return `minlength: ${min}; maxlength: ${max}; required: ${required};`;
});

function getRule(field: string, rule: keyof ValidationRule, defaultValue: string | number | null) {
    return props.validationRules?.[field]?.[rule] ?? defaultValue;
}
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <section
            id="section-dashboard-register"
            class="section-dashboard section-dashboard-register"
        >
            <div class="container-xxl">
                <div class="row m-auto mb-4">
                    <div class="col">
                        <h1 class="fw-normal lead">Register with the ILT</h1>
                    </div>
                </div>
                <div class="row m-auto">
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel
                                for="name"
                                value="Name"
                            />

                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 d-block w-100"
                                :class="{ 'is-invalid': form.errors.name }"
                                required
                                autofocus
                                maxlength="255"
                                placeholder="Enter your first & last name"
                                autocomplete="name"
                                @input="clearError('name')"
                            />

                            <InputError
                                class="invalid-feedback mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <div class="mt-4">
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
                                required
                                placeholder="Enter a valid email address"
                                autocomplete="username"
                                @input="clearError('email')"
                            />

                            <InputError
                                class="invalid-feedback mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <div class="mt-4">
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
                                name="new-password"
                                required
                                autocomplete="new-password"
                                :minlength="getRule('password', 'min', 8)"
                                maxlength="255"
                                :passwordrules="passwordRules"
                                @input="clearError('password')"
                            />

                            <InputError
                                class="invalid-feedback mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <div class="mt-4">
                            <InputLabel
                                for="password_confirmation"
                                value="Confirm Password"
                            />

                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-1 d-block w-100"
                                :class="{ 'is-invalid': form.errors.password_confirmation }"
                                name="confirm-password"
                                required
                                autocomplete="new-password"
                                :minlength="getRule('password', 'min', 8)"
                                maxlength="255"
                                :passwordrules="passwordRules"
                                @input="clearError('password_confirmation')"
                            />

                            <InputError
                                class="invalid-feedback mt-2"
                                :message="form.errors.password_confirmation"
                            />
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <PrimaryButton
                                class="some-class focus-ring"
                                type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Register
                            </PrimaryButton>
                        </div>
                        <div class="mt-2">
                            Already registered?
                            <Link
                                :href="route('login')"
                                class="focus-ring rounded-1 p-1 link-opacity-75 link-opacity-100-hover"
                            >
                                Log in
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
.row {
    max-width: 70ch;
    margin: 0 auto;
}
</style>
