<template>
    <div class="mt-5">
        <div class="site-content">
            <article class="hentry text-center">

                <h1 class="error-title">
                    <slot name="code" />
                </h1>

                <h2 class="entry-title text-muted mt-4">{{ title }}</h2>

                <div class="entry-content clearfix mt-5">
                    <p>
                        <router-link :to="backTo" class="btn btn-primary px-4">
                            <i class="fas fa-home me-2"></i>{{ __('message.go_to_home') }}
                        </router-link>
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
})
</script>

<style scoped>
.error-title {
    font-size: 10em;
    font-weight: 700;
    margin-bottom: 0.1em;
    line-height: 1;
}
</style>
