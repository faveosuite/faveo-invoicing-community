<template>
    <div class="mb-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-2">
            <label class="form-label fw-bold d-inline-flex align-items-center gap-1 mb-0" :for="fieldId">
                {{ label }}
                <ToolTip v-if="tooltip" :message="tooltip" size="small" />
            </label>

            <div v-if="shortcodes.length" class="d-inline-flex align-items-center gap-1">
                <button
                    v-for="sc in shortcodes"
                    :key="sc.code"
                    type="button"
                    class="btn btn-light btn-sm shortcode-btn"
                    v-tooltip="sc.description"
                    :disabled="disabled"
                    @click="insertShortcode(sc.code)"
                ><i class="fas fa-plus me-1"></i>{{ sc.label }}</button>
            </div>
        </div>

        <textarea
            v-if="type === 'textarea'"
            ref="fieldEl"
            :id="fieldId"
            class="form-control"
            :class="{ 'is-invalid': error }"
            rows="3"
            :placeholder="placeholder"
            :value="value"
            :disabled="disabled"
            @input="handleInput"
        ></textarea>
        <input
            v-else
            ref="fieldEl"
            :id="fieldId"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': error }"
            :placeholder="placeholder"
            :value="value"
            :disabled="disabled"
            @input="handleInput"
        />

        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">{{ __('message.seo_max_chars_recommended', { n: maxLength }) }}</small>
            <small :class="isOverLimit ? 'text-danger' : 'text-muted'">{{ currentLength }} / {{ maxLength }}</small>
        </div>

        <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
    </div>
</template>

<style scoped>
.shortcode-btn {
    line-height: 1;
}
</style>

<script setup>
import { computed, ref, nextTick } from 'vue'
import ToolTip from '../Tooltip.vue'

const props = defineProps({
    name:        { type: String, required: true },
    label:       { type: String, required: true },
    value:       { type: String, default: '' },
    onChange:    { type: Function, required: true },
    error:       { type: String, default: undefined },
    maxLength:   { type: Number, required: true },
    type:        { type: String, default: 'text' },
    tooltip:     { type: String, default: '' },
    placeholder: { type: String, default: '' },
    disabled:    { type: Boolean, default: false },
    // Optional list of { code, label, description } shortcodes rendered as
    // "+ label" click-to-insert buttons next to the label.
    shortcodes:  { type: Array, default: () => [] },
})

const fieldId = computed(() => `seo-field-${props.name}`)
const currentLength = computed(() => (props.value || '').length)
const isOverLimit = computed(() => currentLength.value > props.maxLength)

const fieldEl = ref(null)

function handleInput(e) {
    props.onChange(e.target.value, props.name)
}

// Inserts at the current cursor position rather than always appending, so
// shortcodes can be dropped into the middle of existing text.
function insertShortcode(code) {
    const el = fieldEl.value
    const current = props.value || ''

    if (!el || typeof el.selectionStart !== 'number') {
        props.onChange(current + code, props.name)
        return
    }

    const start = el.selectionStart
    const end = el.selectionEnd
    props.onChange(current.slice(0, start) + code + current.slice(end), props.name)

    nextTick(() => {
        el.focus()
        const pos = start + code.length
        el.setSelectionRange(pos, pos)
    })
}
</script>
