<template>
    <!-- Bootstrap popover styling, shown by Vue (no Popper): floats over the form under the password field while it has focus. -->
    <div v-if="show" class="popover bs-popover-bottom position-absolute top-100 start-0 w-100 mw-100 mt-2 shadow" role="tooltip">
        <div class="popover-arrow" style="inset-inline-start: 1.5rem"></div>
        <div class="popover-body">
            <!-- Strength: label + 4-segment meter -->
            <div class="d-flex justify-content-between align-items-center gap-2 small mb-2">
                <span class="fw-medium text-body-emphasis">{{ __('message.pwd_strength') }}</span>
                <strong :class="level.text">{{ level.label }}</strong>
            </div>
            <div class="d-flex gap-1" aria-hidden="true">
                <span v-for="i in SEGMENTS" :key="i" class="flex-fill rounded" style="height: 4px"
                      :class="i <= level.filled ? level.bg : 'bg-body-secondary'"></span>
            </div>

            <!-- Rules -->
            <ul class="list-unstyled mb-0 mt-3 pt-3 border-top small" aria-live="polite">
                <li v-for="c in checklist" :key="c.key" class="d-flex align-items-center gap-2 mb-1"
                    :class="c.ok ? 'text-success' : (strict ? 'text-danger' : 'text-body-secondary')">
                    <i class="fa-fw" :class="c.ok ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle-xmark'"
                       role="img" :aria-label="c.ok ? __('message.pwd_rule_met') : __('message.pwd_rule_not_met')"></i>
                    <span>{{ c.label }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { __ } from '@/plugins/i18n'
import { passwordChecks } from '@/validations/client/authSchemas.js'

const props = defineProps({
    password: { type: String, default: '' },
    show:     { type: Boolean, default: false },
    // After a failed submit: unmet rules turn red instead of grey.
    strict:   { type: Boolean, default: false },
})

const SEGMENTS = 4

const checklist = computed(() => {
    const c = passwordChecks(props.password)
    return [
        { key: 'length',  ok: c.length,  label: __('message.pwd_req_length') },
        { key: 'upper',   ok: c.upper,   label: __('message.pwd_req_upper') },
        { key: 'lower',   ok: c.lower,   label: __('message.pwd_req_lower') },
        { key: 'number',  ok: c.number,  label: __('message.pwd_req_number') },
        { key: 'special', ok: c.special, label: __('message.pwd_req_special') },
    ]
})

const score = computed(() => checklist.value.filter(c => c.ok).length)

// 0–2 rules = weak, 3 = fair, 4 = good, all 5 = strong (the only level the server accepts).
// "Good" uses the Porto primary so the meter matches the app's blue.
const level = computed(() => {
    switch (score.value) {
        case 5:  return { filled: 4, bg: 'bg-success',       text: 'text-success',       label: __('message.pwd_strength_strong') }
        case 4:  return { filled: 3, bg: 'bg-color-primary', text: 'text-color-primary', label: __('message.pwd_strength_good') }
        case 3:  return { filled: 2, bg: 'bg-warning',       text: 'text-warning',       label: __('message.pwd_strength_fair') }
        default: return { filled: 1, bg: 'bg-danger',        text: 'text-danger',        label: __('message.pwd_strength_weak') }
    }
})
</script>
