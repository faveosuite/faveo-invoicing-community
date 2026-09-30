<template>
    <div class="d-flex align-items-center justify-content-center error-page-wrapper">
        <div class="error-page">
            <h2 class="headline error-headline" :class="`text-${color}`">{{ code }}</h2>
            <div class="error-content">
                <h3>
                    <i class="fas fa-exclamation-triangle" :class="`text-${color}`"></i>
                    {{ title }}
                </h3>
                <p>
                    {{ description }}
                    <router-link to="/dashboard">{{ __('message.return_to_dashboard') }}</router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    code:        { type: String, required: true },
    title:       { type: String, required: true },
    description: { type: String, required: true },
    color:       { type: String, default: 'warning' },
})
</script>

<style scoped>
.error-page-wrapper { min-height: 70vh; }
.error-headline { margin-top: -25px; }
</style>
