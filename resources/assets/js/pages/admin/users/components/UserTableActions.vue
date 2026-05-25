<template>
    <div class="user-table-actions">
        <router-link
            :to="`/users/${userId}/edit`"
            class="btn btn-light table_btn"
            :title="__('message.edit')"
            v-tooltip
        >
            <i class="fas fa-edit"></i>
        </router-link>
        <router-link
            :to="`/users/${userId}`"
            class="btn btn-default table_btn"
            :title="__('message.view')"
            v-tooltip
        >
            <i class="fas fa-eye"></i>
        </router-link>
        <button
            class="btn btn-light table_btn"
            :title="__('message.Delete')"
            v-tooltip
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

const props = defineProps({
    userId:        { type: Number, required: true },
    baseUrl:       { type: String, default: '' },
    componentName: { type: String, default: 'users-index' },
})

const emit = defineEmits(['deleted'])

const showModal = ref(false)
</script>
