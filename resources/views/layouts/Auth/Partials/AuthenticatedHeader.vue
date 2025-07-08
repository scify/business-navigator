<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { Offcanvas as OffcanvasType } from 'bootstrap';

// SSR-compatible Bootstrap import
let Offcanvas: typeof OffcanvasType | undefined;
if (typeof window !== 'undefined') {
    import('bootstrap').then(module => {
        Offcanvas = module.Offcanvas;
    });
}
import { LucideLayoutGrid, LucideSearch, LucideUser } from 'lucide-vue-next';
import DeployAILogo from '@/views/components/Logos/DeployAILogo.vue';

// Shared data:
// @link https://inertiajs.com/shared-data
const page = usePage();

// Track the status of the off-canvas menu:
const offcanvasRef = ref<HTMLElement | null>(null);

// Function to close the off-canvas menu:
function closeOffcanvas() {
    if (offcanvasRef.value && Offcanvas) {
        const offcanvasInstance = Offcanvas.getInstance(offcanvasRef.value) || new Offcanvas(offcanvasRef.value);
        offcanvasInstance.hide();
    }
}

// Router triggers the closing of the off-canvas menu:
router.on('start', () => {
    if (!offcanvasRef.value) return;
    closeOffcanvas();
});
</script>

<template>
    <div class="navbar navbar-expand-md ilt-navbar">
        <nav
            class="container-xxl"
            aria-label="Main Navigation"
            data-bs-theme="light"
        >
            <span class="navbar-brand d-flex align-items-center">
                <a
                    class="navbar-brand-parent"
                    target="_blank"
                    href="https://www.aiodp.ai"
                >
                    <deploy-a-i-logo />
                </a>
                <Link
                    class="navbar-brand"
                    :href="route('index')"
                >
                    {{ page.props.app.name }}
                </Link>
            </span>
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasNavbar"
                aria-controls="offcanvasNavbar"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div
                id="offcanvasNavbar"
                ref="offcanvasRef"
                class="offcanvas offcanvas-end"
                tabindex="-1"
                aria-labelledby="offcanvasNavbarLabel"
            >
                <div class="offcanvas-header">
                    <strong
                        id="offcanvasNavbarLabel"
                        class="h5 offcanvas-title fw-bold"
                    >
                        {{ page.props.app.name }}
                    </strong>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="offcanvas"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="offcanvas-body">
                    <ul class="navbar-nav justify-content-end flex-grow-1">
                        <!-- Home -->
                        <li class="nav-item text-uppercase">
                            <Link
                                :class="['nav-link', { active: route().current('index') }]"
                                :href="route('index')"
                                >Home</Link
                            >
                        </li>
                        <!-- Map -->
                        <li class="nav-item text-uppercase">
                            <Link
                                :class="['nav-link', { active: $page.url.startsWith('/map') }]"
                                :href="route('map')"
                                >Map</Link
                            >
                        </li>
                        <!-- Explore -->
                        <li class="nav-item text-uppercase">
                            <Link
                                :class="[
                                    'nav-link',
                                    {
                                        active: $page.url.startsWith('/explore'),
                                    },
                                ]"
                                :href="route('explore')"
                                >Explore</Link
                            >
                        </li>
                        <!-- Search -->
                        <li class="nav-item text-uppercase nav-item-icon">
                            <Link
                                :href="route('login')"
                                class="nav-link d-flex column-gap-2"
                            >
                                <span class="d-md-none"> Search </span>
                                <span class="d-md-inline span-item-icon">
                                    <LucideSearch
                                        :size="18"
                                        :stroke-width="2.25"
                                        aria-label="Search"
                                    />
                                </span>
                            </Link>
                        </li>
                        <!-- DAI Universe -->
                        <li class="nav-item text-uppercase nav-item-icon">
                            <Link
                                :href="route('login')"
                                class="nav-link d-flex column-gap-2"
                            >
                                <span class="d-md-none"> DeployAI Universe </span>
                                <span class="d-md-inline span-item-icon">
                                    <LucideLayoutGrid
                                        :size="18"
                                        :stroke-width="2.25"
                                        aria-label="Deploy AI Universe"
                                    />
                                </span>
                            </Link>
                        </li>
                        <!-- User options -->
                        <li class="nav-item dropdown text-uppercase nav-item-icon nav-item-user-options">
                            <a
                                class="nav-link dropdown-toggle d-flex align-items-center"
                                href="#"
                                role="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <span class="d-md-none span-item-username">
                                    {{ page.props.auth.user.name }}
                                </span>
                                <span class="d-md-inline span-item-icon">
                                    <LucideUser
                                        :size="18"
                                        :stroke-width="2.5"
                                        :aria-label="page.props.auth.user.name"
                                    />
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <Link
                                        class="dropdown-item"
                                        :href="route('profile.edit')"
                                    >
                                        Dashboard
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        class="dropdown-item"
                                        :href="route('profile.edit')"
                                    >
                                        Your Profile
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        class="dropdown-item text-uppercase"
                                        :href="route('logout')"
                                        method="post"
                                    >
                                        Log Out
                                    </Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="offcanvas-header p-3">
                    <div class="w-100 small text-end">Version {{ page.props.app.version }}</div>
                </div>
            </div>
        </nav>
    </div>
</template>
