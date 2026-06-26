<template>
    <div class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="error-page">
            <h2 class="headline" :class="`text-${color}`" style="margin-top: -25px;">{{ code }}</h2>
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
