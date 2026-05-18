<template>
    <div class="d-flex gap-1">
        <button
            class="btn btn-light table_btn"
            :title="isDefault ? 'Cannot disable the default language' : (status ? 'Disable' : 'Enable')"
            :disabled="toggling || isDefault"
            v-tooltip
            @click="emit('toggle')"
        >
            <i :class="status ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger'"></i>
        </button>
        <button
            v-if="!isDefault && status"
            class="btn btn-light table_btn"
            title="Make Default"
            :disabled="settingDefault"
            v-tooltip
            @click="emit('set-default')"
        >
            <i class="fas fa-star"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({
    status:        { type: Number,  required: true },
    isDefault:     { type: Boolean, required: true },
    toggling:      { type: Boolean, default: false },
    settingDefault:{ type: Boolean, default: false },
})

const emit = defineEmits(['toggle', 'set-default'])
</script>
