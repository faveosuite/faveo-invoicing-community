<template>
    <div class="mt-5">
        <div class="site-content">
            <article class="hentry text-center">

                <h1 class="error-title">
                    <slot name="code" />
                </h1>

                <h2 class="entry-title text-muted mt-4">{{ title }}</h2>

                <div class="entry-content clearfix mt-5">
                    <p class="d-flex flex-wrap justify-content-center gap-2">
                        <!-- Server errors: go back to the page that failed (reloading /500 would just show this again). -->
                        <button v-if="retry" type="button" class="btn btn-primary px-4" @click="goBack">
                            <i class="fas fa-rotate-right me-2" aria-hidden="true"></i>{{ __('message.retry') }}
                        </button>
                        <router-link :to="backTo" class="btn px-4" :class="retry ? 'btn-light border' : 'btn-primary'">
                            <i class="fas fa-home me-2" aria-hidden="true"></i>{{ __('message.go_to_home') }}
                        </router-link>
                    </p>
                    <p v-if="retry" class="text-2">
                        {{ __('message.need_help') }}
                        <router-link to="/contact-us" class="text-color-primary font-weight-semibold text-decoration-none">{{ __('message.contact_support') }}</router-link>
                    </p>
                </div>

            </article>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title:  { type: String, required: true },
    backTo: { type: String, default: '/' },
    // Show "Retry" + a support link (server errors).
    retry:  { type: Boolean, default: false },
})

const goBack = () => globalThis.history.back()
</script>

<style scoped>
.error-title {
    font-size: 10em;
    font-weight: 700;
    margin-bottom: 0.1em;
    line-height: 1;
}
</style>
