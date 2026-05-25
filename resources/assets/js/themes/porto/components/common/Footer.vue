<template>
    <footer id="footer" class="position-relative bg-color-light-scale-1 border-top-0">
        <div class="footer-copyright bg-transparent">
            <div class="container">
                <hr class="bg-color-dark opacity-1">
                <div class="row">
                    <div class="col mt-4 mb-4 pb-3">
                        <p class="text-center text-3 mb-0 text-color-grey">
                            {{ __('message.copyright') }} © {{ currentYear }}
                            <a :href="website" class="text-color-grey text-color-hover-primary font-weight-bold" target="_blank">
                                {{ company }}
                            </a>
                            {{ __('message.all_rights') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue'

const el          = document.getElementById('app-client')
const company     = computed(() => el?.dataset?.company ?? '')
const website     = computed(() => el?.dataset?.website ?? '#')
const currentYear = new Date().getFullYear()
</script>
