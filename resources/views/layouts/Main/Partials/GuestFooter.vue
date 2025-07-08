<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import FlagIcon from '@/views/components/Icons/FlagIcon.vue';
import TwitterIcon from '@/views/components/Icons/TwitterIcon.vue';
import LinkedInIcon from '@/views/components/Icons/LinkedInIcon.vue';
import DeployAILogo from '@/views/components/Logos/DeployAILogo.vue';

// Shared data:
// @link https://inertiajs.com/shared-data
const page = usePage();
</script>

<template>
    <div class="text-ilt-blue-sec text-center">
        <div class="container-xxl mt-5 pb-5 px-4 px-xxl-0">
            <div class="col-12 mx-auto">
                <div
                    class="d-block border-3 border-ilt-yellow border-bottom mb-5"
                    aria-hidden="true"
                ></div>

                <div class="d-flex flex-column flex-md-row align-items-center row-gap-4 mb-5 mb-sm-4">
                    <div class="col-12 col-md-2">
                        <a
                            href="https://www.aiodp.ai/"
                            class="d-inline-block d-sm-inline deploy-ai"
                        >
                            <deploy-a-i-logo
                                class="mx-auto mx-md-0"
                                :scale="1.75"
                            />
                        </a>
                    </div>
                    <div
                        id="footer-links"
                        class="col-12 col-md-10 d-flex flex-column flex-md-row gap-4 justify-content-center lh-1 footer-links"
                    >
                        <a
                            href="mailto:contact@deployaiproject.eu"
                            class="text-ilt-blue"
                            >Contact Us</a
                        >
                        <a
                            href="https://www.aiodp.ai/"
                            class="text-ilt-blue"
                            target="_blank"
                            >Explore the AIoD Platform</a
                        >
                        <a
                            href="https://www.linkedin.com/company/deployaieu"
                            class="d-flex justify-content-center text-ilt-blue fs-5 footer-link-icon"
                            rel="nofollow noopener noreferrer"
                            target="_blank"
                        >
                            <linked-in-icon />
                        </a>
                        <a
                            href="https://x.com/DeployAIeu"
                            class="d-flex justify-content-center text-ilt-blue fs-5 footer-link-icon"
                            rel="nofollow noopener noreferrer"
                            target="_blank"
                        >
                            <twitter-icon />
                        </a>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row align-items-center row-gap-4">
                    <div class="col-2">
                        <FlagIcon />
                    </div>
                    <div class="col-12 col-md-10">
                        <div class="small text-wrap-pretty">
                            The {{ page.props.app.name }} has received funding from the Digital Europe Programme
                            (DIGITAL) under grant agreement <span class="text-nowrap">№. 101146490</span> -
                            <span class="text-nowrap">DIGITAL-2022-CLOUD-AI-B-03</span>
                        </div>
                        <div class="small mt-3 footer-links-minor">
                            {{ page.props.app.name }} <span>v{{ page.props.app.version }}</span>
                            <br />
                            &copy;2025 &dash;
                            <a
                                href="https://www.deployaiproject.eu/imprint/"
                                class="text-ilt-blue"
                                target="_blank"
                                >Imprint</a
                            >
                            &middot;
                            <a
                                href="https://www.deployaiproject.eu/terms-and-conditions/"
                                class="text-ilt-blue"
                                target="_blank"
                                >Terms & Conditions</a
                            >
                            &middot;
                            <a
                                href="https://www.deployaiproject.eu/privacy-policy/"
                                class="text-ilt-blue"
                                target="_blank"
                                >Privacy Policy</a
                            >
                            &middot;
                            <button
                                class="text-ilt-blue"
                                href="javascript:void(0);"
                                role="button"
                                onclick="// noinspection JSUnresolvedReference
                                    toggleCookieBanner()"
                            >
                                Cookie Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
