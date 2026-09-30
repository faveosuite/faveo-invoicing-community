import { ref } from 'vue'

// Module-level so any component can trigger a notification
const message = ref('')
const type    = ref('success')   // 'success' | 'danger' | 'warning' | 'info'
const visible = ref(false)

let timer = null

export function useNotification() {
    function notify(msg, msgType = 'success', duration = 7000) {
        message.value = msg
        type.value    = msgType
        visible.value = true
        clearTimeout(timer)
        timer = setTimeout(() => { visible.value = false }, duration)
    }

    function dismiss() {
        clearTimeout(timer)
        visible.value = false
    }

    return { message, type, visible, notify, dismiss }
}
