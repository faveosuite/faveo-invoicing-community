<template>
    <component
        :is="to ? RouterLink : 'button'"
        v-bind="$attrs"
        class="btn action-btn"
        :class="classes"
        :type="to ? undefined : type"
        :disabled="to ? undefined : isDisabled"
        :to="to || undefined"
        :aria-busy="loading || undefined"
        :aria-label="!showLabel ? resolvedLabel : undefined"
    >
        <span
            v-if="loading"
            class="action-btn-spinner"
            role="status"
            aria-hidden="true"
        ></span>
        <i
            v-else-if="resolvedIcon"
            :class="resolvedIcon"
            class="action-btn-icon"
            aria-hidden="true"
        ></i>
        <span v-if="showLabel" class="action-btn-label">{{ resolvedLabel }}</span>
        <slot />
    </component>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { __ } from '@/plugins/i18n.js'

defineOptions({ inheritAttrs: false })

const props = defineProps({
    action:   { type: String,            default: '' },
    loading:  { type: Boolean,           default: false },
    disabled: { type: Boolean,           default: false },
    size:     { type: String,            default: '' },
    variant:  { type: String,            default: '' },
    icon:     { type: String,            default: '' },
    label:    { type: String,            default: '' },
    iconOnly: { type: Boolean,           default: null },
    type:     { type: String,            default: 'button' },
    to:       { type: [String, Object],  default: null },
    // Porto/client theme's subdued palette: neutral actions render as
    // btn-light instead of btn-secondary.
    light:    { type: Boolean,           default: false },
})

const ACTION_MAP = {
    save:     { variant: 'primary',   icon: 'fas fa-save',        label: 'save' },
    update:   { variant: 'primary',   icon: 'fas fa-sync-alt',    label: 'update' },
    create:   { variant: 'primary',   icon: 'fas fa-plus',        label: 'create' },
    add:      { variant: 'primary',   icon: 'fas fa-plus',        label: 'add' },
    apply:    { variant: 'primary',   icon: 'fas fa-check',       label: 'apply' },
    confirm:  { variant: 'primary',   icon: 'fas fa-check',       label: 'confirm' },
    search:   { variant: 'primary',   icon: 'fas fa-search',      label: 'search' },
    submit:   { variant: 'primary',   icon: 'fas fa-paper-plane', label: 'submit' },
    delete:   { variant: 'danger',    icon: 'fas fa-trash',       label: 'delete' },
    remove:   { variant: 'danger',    icon: 'fas fa-times',       label: 'remove' },
    cancel:   { variant: 'secondary', icon: 'fas fa-times',       label: 'cancel' },
    close:    { variant: 'secondary', icon: 'fas fa-times',       label: 'close' },
    back:     { variant: 'secondary', icon: 'fas fa-arrow-left',  label: 'back' },
    download: { variant: 'secondary', icon: 'fas fa-download',    label: 'download' },
    upload:   { variant: 'secondary', icon: 'fas fa-upload',      label: 'upload' },
    reset:    { variant: 'secondary', icon: 'fas fa-undo',        label: 'reset' },
    export:   { variant: 'secondary', icon: 'fas fa-file-export', label: 'export' },
    import:   { variant: 'secondary', icon: 'fas fa-file-import', label: 'import' },
    refresh:  { variant: 'secondary', icon: 'fas fa-sync-alt',    label: 'refresh' },
    filter:   { variant: 'secondary', icon: 'fas fa-filter',      label: 'filter' },
    sync:     { variant: 'secondary', icon: 'fas fa-sync-alt',    label: 'sync' },
    edit:     { variant: 'light',     icon: 'fas fa-edit',        label: 'edit',    tableBtn: true },
    view:     { variant: 'default',   icon: 'fas fa-eye',         label: 'view',    tableBtn: true },
    restore:  { variant: 'light',     icon: 'fas fa-sync-alt',    label: 'restore', tableBtn: true },
}

const meta           = computed(() => ACTION_MAP[props.action] || {})
const resolvedVariant = computed(() => {
    if (props.variant) return props.variant
    const base = meta.value.variant || 'secondary'
    return (props.light && base === 'secondary') ? 'light' : base
})
const resolvedIcon    = computed(() => props.icon    || meta.value.icon    || '')
const resolvedLabel   = computed(() => {
    if (props.label) return props.label
    const key = meta.value.label
    return key ? __(`message.${key}`) : ''
})

const isTableBtn = computed(() => meta.value.tableBtn && !props.variant)
const showLabel  = computed(() => {
    if (props.iconOnly === true)  return false
    if (props.iconOnly === false) return true
    return !isTableBtn.value && !!resolvedLabel.value
})

const isDisabled  = computed(() => props.disabled || props.loading)
const sizeClass   = computed(() => props.size ? `btn-${props.size}` : '')
const classes     = computed(() => [
    `btn-${resolvedVariant.value}`,
    sizeClass.value,
    { 'table_btn': isTableBtn.value },
])

</script>

<style scoped>
/* Deliberately not display:flex — a flex row with no label child (icon-only
   table buttons) collapses to the icon's tight line-height instead of the
   button's normal line-height-driven height. Plain inline layout always
   reserves that height regardless of content, so icon-only buttons stay the
   same height as labeled ones. Icon/label spacing comes from margin instead
   of gap below. */
.action-btn {
    white-space: nowrap;
    vertical-align: middle;
}

.action-btn-spinner {
    display: inline-block;
    width: 0.9em;
    height: 0.9em;
    border: 0.15em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: action-btn-spin 0.65s linear infinite;
    vertical-align: middle;
}

@keyframes action-btn-spin {
    to { transform: rotate(360deg); }
}

.action-btn-icon {
    line-height: 1;
}

.action-btn-icon:not(:last-child),
.action-btn-spinner:not(:last-child) {
    margin-right: 6px;
}
</style>
