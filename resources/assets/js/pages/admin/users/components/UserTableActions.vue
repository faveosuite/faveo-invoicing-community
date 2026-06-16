<template>
    <div class="user-table-actions">
        <router-link
            :to="`/users/${userId}/edit`"
            class="btn btn-light table_btn"
            v-tooltip="__('message.edit')"
        >
            <i class="fas fa-edit"></i>
        </router-link>
        <router-link
            :to="`/users/${userId}`"
            class="btn btn-default table_btn"
            v-tooltip="__('message.view')"
        >
            <i class="fas fa-eye"></i>
        </router-link>
        <button
            class="btn btn-light table_btn"
            v-tooltip="__('message.Delete')"
            @click="showModal = true"
        >
            <i class="fas fa-trash"></i>
        </button>

        <DeleteModal
            v-if="showModal"
            :showModal="showModal"
            :onClose="() => showModal = false"
            :deleteUrl="`${baseUrl}/users`"
            :deleteData="{ user_ids: [userId] }"
            :componentName="componentName"
            @deleted="emit('deleted')"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
    userId:        { type: Number, required: true },
    baseUrl:       { type: String, default: '' },
    componentName: { type: String, default: 'users-index' },
})

const emit = defineEmits(['deleted'])

const showModal = ref(false)
</script>
