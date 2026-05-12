<template>
    <div>
        <modal v-if="showModal" :showModal="showModal" :onClose="onClose" :containerStyle="containerStyle">
            <template #title>
                <h4 class="modal-title">{{ lang(title) }}</h4>
            </template>
            <template #fields>
                <div v-if="!loading" class="mod_width">
                    <p v-html="content" :class="[{ 'trace': title === 'trace' }]"></p>
                </div>
            </template>
        </modal>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import axios from '@/plugins/axios'
import { lang } from '@/helpers/extraLogics'

const props = defineProps({
    showModal:    { type: Boolean, default: false },
    onClose:      { type: Function },
    data:         { type: Object,  default: () => ({}) },
    title:        { type: String,  default: '' },
    hideCheckBox: { type: Boolean },
})

const containerStyle = ref({ width: '950px' })
const loading = ref(true)
const content = ref('')

onBeforeMount(() => {
    checkTitle()
})

function checkTitle() {
    if (props.title !== 'delete_logs') {
        if (props.title === 'logs_content') {
            getLogsContent(props.data.id)
        } else {
            content.value = props.data.trace
            containerStyle.value.width = '1000px'
            loading.value = false
        }
    } else {
        loading.value = false
    }
}

async function getLogsContent(id) {
    const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl || ''
    try {
        const res = await axios.get(`${baseUrl}/api/get-log-mail-body/${id}`)
        content.value = res.data.data.mail_body.replaceAll('<br />\r\n', '')
    } catch {
        // silently fail
    } finally {
        loading.value = false
    }
}
</script>

<style type="text/css">
.mod_width { max-height: 400px; overflow-x: hidden; overflow-y: auto; }
.trace { background: black !important; color: aliceblue; padding: 10px; font-family: monospace; font-size: 13px; line-height: 1.5 !important; }
p { word-break: break-word; }
</style>
