import { ref, onUnmounted } from 'vue'

/**
 * Shared countdown timer for OTP-resend style cooldowns.
 * Usage: const { cooldown, start, stop } = useCooldown(120)
 * start(n) counts from n (e.g. the server's retry_after); start() uses the default.
 */
export function useCooldown(seconds = 120) {
    const cooldown = ref(0)
    let timer = null

    function start(from = seconds) {
        cooldown.value = Math.max(0, Math.round(Number(from) || 0))
        if (!cooldown.value) return
        clearInterval(timer)
        timer = setInterval(() => {
            cooldown.value--
            if (cooldown.value <= 0) clearInterval(timer)
        }, 1000)
    }

    function stop() {
        clearInterval(timer)
        cooldown.value = 0
    }

    onUnmounted(() => clearInterval(timer))

    return { cooldown, start, stop }
}
