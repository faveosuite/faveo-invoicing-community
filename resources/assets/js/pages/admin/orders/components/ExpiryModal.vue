<template>
    <div class="modal fade" :id="id" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="responseBox" v-if="message" :class="message.type === 'success' ? 'alert alert-success' : 'alert alert-danger'">
                        {{ message.text }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ __('message.new_date') }}</label>
                        <VueDatePicker
                            v-model:value="selectedDate"
                            type="date"
                            format="MM/DD/YYYY"
                            value-type="MM/DD/YYYY"
                            placeholder="MM/DD/YYYY"
                            :clearable="true"
                            :editable="true"
                            :append-to-body="false"
                            input-class="form-control"
                            @change="selectedDate = $event"
                        />
                    </div>
                </div>
                <div class="modal-footer">
                    <action-button action="close" type="button" data-bs-dismiss="modal" />
                    <action-button action="save" type="button" :loading="saving" :disabled="!selectedDate" @click="save" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import VueDatePicker from 'vue-datepicker-next'
import 'vue-datepicker-next/index.css'
import http from '@/plugins/axios'

const props = defineProps({
    id:          { type: String, required: true },
    title:       { type: String, required: true },
    orderId:     { type: [String, Number], required: true },
    initialDate: { type: String, default: null },
    endpoint:    { type: String, required: true },
    baseUrl:     { type: String, default: '' },
})

const emit = defineEmits(['saved'])

const saving       = ref(false)
const message      = ref(null)
const selectedDate = ref(props.initialDate)

watch(() => props.initialDate, v => { selectedDate.value = v })

async function save() {
    if (!selectedDate.value) return
    saving.value  = true
    message.value = null
    try {
        const res = await http.post(`${props.baseUrl}/${props.endpoint}`, {
            orderid: props.orderId,
            date:    selectedDate.value,
        })
        if (res.data?.message === 'success') {
            message.value = { type: 'success', text: res.data.update }
            setTimeout(() => {
                message.value = null
                const el = document.getElementById(props.id)
                if (el) {
                    const modal = window.bootstrap?.Modal?.getInstance(el)
                    modal?.hide()
                }
                emit('saved')
            }, 1500)
        }
    } catch (e) {
        message.value = { type: 'error', text: e?.response?.data?.message ?? 'An error occurred.' }
    } finally {
        saving.value = false
    }
}
</script>
