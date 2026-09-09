<template>
    <div>
        <div v-if="uploading" class="progress mt-2" style="height: 6px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                :style="{ width: progress + '%' }" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small v-if="uploading" class="text-muted d-block mt-1">{{ __('message.uploading') || 'Uploading' }}… {{ progress }}%</small>
        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
        <small v-else-if="uploadedName" class="text-success d-block mt-1">{{ uploadedName }}</small>
    </div>
</template>

<script setup>
defineProps({
    uploading: { type: Boolean, default: false },
    progress: { type: Number, default: 0 },
    error: { type: String, default: '' },
    uploadedName: { type: String, default: '' },
})
</script>
