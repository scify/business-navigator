<script lang="ts" setup>
import { Head } from '@inertiajs/vue3';
import NumberIconWidget from '@/views/components/Widgets/NumberIconWidget.vue';
</script>

<template>
    <Head title="Widgets & Fonts Test" />
    <main>
        <section
            id="section-test"
            class="section-test"
        >
            <div class="container-xxl">
                <div class="row">
                    <div class="col">
                        <h1>Tests</h1>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h2>Widgets & Fonts</h2>
                        <small
                            >A common issue with widgets is that at least 5 characters should always be visible in all
                            screen sizes. (e.g. 9.100 or 99.1k). Please slowly adjust the window of your browser. Make
                            it smaller and smaller to test these widgets in all screen sizes with the 3 proposed
                            fonts.</small
                        >

                        <hr />
                        <h3 class="py-3">QuickSand (Default Display Font)</h3>
                        <div class="widget-group row row-gap-4 py-3">
                            <NumberIconWidget
                                title="AI-Powered Organisations"
                                icon="corporate_fare"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="AI-Driven Industries"
                                icon="precision_manufacturing"
                                :duration="10"
                            />
                            <NumberIconWidget
                                title="AI-Enhanced Countries"
                                icon="globe"
                                :duration="30"
                            />
                        </div>
                        <h3 class="py-3">Source Sans (Default Base Font)</h3>
                        <div class="widget-group row row-gap-4 font-source py-3">
                            <NumberIconWidget
                                title="AI-Powered Organisations"
                                icon="corporate_fare"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="AI-Driven Industries"
                                icon="precision_manufacturing"
                                :duration="10"
                            />
                            <NumberIconWidget
                                title="AI-Enhanced Countries"
                                icon="globe"
                                :duration="30"
                            />
                        </div>
                        <h3 class="py-3">Montserrat (Banned)</h3>
                        <div class="widget-group row row-gap-4 font-montserrat py-3">
                            <NumberIconWidget
                                title="AI-Powered Organisations"
                                icon="corporate_fare"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="AI-Driven Industries"
                                icon="precision_manufacturing"
                                :duration="10"
                            />
                            <NumberIconWidget
                                title="AI-Enhanced Countries"
                                icon="globe"
                                :duration="30"
                            />
                        </div>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h2>Widget Variations</h2>
                        <div class="widget-group row row-gap-4 py-3">
                            <NumberIconWidget
                                title="Black Theme"
                                icon="precision_manufacturing"
                                theme="black"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="White Theme"
                                icon="precision_manufacturing"
                                theme="white"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="Blue Theme"
                                icon="precision_manufacturing"
                                theme="blue"
                                :count="9999"
                            />
                        </div>
                        <div class="widget-group row row-gap-4 py-3">
                            <NumberIconWidget
                                title="Modern Theme"
                                icon="precision_manufacturing"
                                theme="modern"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="Modern Theme"
                                icon="precision_manufacturing"
                                theme="modern"
                                :count="9999"
                            />
                            <NumberIconWidget
                                title="Modern Theme"
                                icon="precision_manufacturing"
                                theme="modern"
                                :count="9999"
                            />
                        </div>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h2>Unicode support</h2>
                        <small>In all cases the default font-size for h3 is used.</small>
                        <div class="py-5">
                            <h3>QuickSand</h3>
                            <p>
                                Supports for latin and vietnamese. Cyrillic and Greek fail back to the default system-ui
                                font (OS dependent).
                            </p>
                            <h3>Latin: The quick brown fox jumps over the lazy dog.</h3>
                            <h3>Cyrillic: Быстрая коричневая лиса прыгает через ленивую собаку.</h3>
                            <h3>Greek: Η γρήγορη καφέ αλεπού πηδάει πάνω από το τεμπέλικο σκυλί.</h3>
                            <h3>Mixed: Η Быстрая brown αλεπού jumps over από το ленивую σκυλί.</h3>
                            <p>Note the difference in weights on mixed:</p>
                            <h2>Πaπaδόπыoυλяος €</h2>
                            <hr />
                        </div>
                        <!-- Source Sans Font -->
                        <div class="py-4 font-source">
                            <h3>Source Sans</h3>
                            <p>Supports for latin, cyrillic, greek. No fallback needed.</p>
                            <h3>Latin: The quick brown fox jumps over the lazy dog.</h3>
                            <h3>Cyrillic: Быстрая коричневая лиса прыгает через ленивую собаку.</h3>
                            <h3>Greek: Η γρήγορη καφέ αλεπού πηδάει πάνω από το τεμπέλικο σκυλί.</h3>
                            <h3>Mixed: Η Быстрая brown αλεπού jumps over από το ленивую σκυλί.</h3>
                            <p>No issues.</p>
                            <h2>Πaπaδόπыoυλяος €</h2>
                            <hr />
                        </div>
                        <!-- Montserrat Font -->
                        <div class="py-4 font-montserrat">
                            <h3>Montserrat</h3>
                            <p>Supports for latin, cyrillic. Fallbacks to system-ui for greek.</p>
                            <h3>Latin: The quick brown fox jumps over the lazy dog.</h3>
                            <h3>Cyrillic: Быстрая коричневая лиса прыгает через ленивую собаку.</h3>
                            <h3>Greek: Η γρήγορη καφέ αλεπού πηδάει πάνω από το τεμπέλικο σκυλί.</h3>
                            <h3>Mixed: Η Быстрая brown αλεπού jumps over από το ленивую σκυλί.</h3>
                            <p>Note the difference in widths/weights on mixed.</p>
                            <h2>Πaπaδόπыoυλяος €</h2>
                            <hr />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</template>

<style>
.font-source dl dd,
.font-source h3,
.font-source h2 {
    font-family: 'Source Sans 3 Variable', sans-serif !important;
}
.font-montserrat dl dd,
.font-montserrat h3,
.font-montserrat h2 {
    font-family: 'Montserrat Variable', sans-serif !important;
}
</style>
