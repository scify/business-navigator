<script setup lang="ts"></script>
<template>
    <Teleport to="body">
        <div class="alert-container position-fixed w-100 w-sm-50 py-1 px-3 py-sm-3 px-sm-4">
            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >
                <strong>Interactive Landscape Tool is under construction!</strong>
                This site is scheduled to officially launch in late Q4. This is an early demo version featuring actual,
                limited data from organizations based in France. New features and updates are introduced on a regular
                basis.
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.alert-container {
    position: fixed;
    bottom: 0;
    right: 0;
    max-width: 480px;
    z-index: 1050;
    > .alert {
        opacity: 0.95;
        background-blend-mode: overlay;
        box-shadow: 0.25rem 0.5rem 0.75rem rgba(0, 0, 0, 0.1);
    }
}
</style>
