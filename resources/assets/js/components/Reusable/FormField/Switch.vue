<template>
    <div :class="classname">
        <Toggle v-model="enabled" :disabled="!!disabled" class="toggle-switch" />
    </div>
</template>

<script>
import Toggle from '@vueform/toggle'
import { boolean } from '@/helpers/extraLogics'

export default {
    name: 'status-switch',

    components: { Toggle },

    props: {
        name:      { type: [String, Number], required: true },
        value:     { type: [Boolean, Number], default: false },
        classname: { type: String,  default: '' },
        onChange:  { type: Function, required: true },
        bold:      { type: [Boolean, Number], default: false },
        disabled:  { type: [Boolean, Number], default: false },
    },

    data() {
        return {
            enabled: boolean(this.value),
        }
    },

    watch: {
        enabled(newVal) {
            this.onChange(newVal, this.name)
        },
        value(newVal) {
            this.enabled = boolean(newVal)
        },
    },
}
</script>

<style src="@vueform/toggle/themes/default.css"></style>

<style>
.toggle-switch {
    --toggle-bg-on:     #28a745;
    --toggle-border-on: #28a745;
    --toggle-bg-off:     #d9534f;
    --toggle-border-off: #d9534f;
}

.toggle-off { opacity: 0.5; }

.toggle-switch #toggle { display: none !important; }

.toggle {
    width:  40px !important;
    height: 15px !important;
}

.toggle-handle {
    width:  15px !important;
    height: 15px !important;
}

.toggle-container:focus {
    box-shadow: none !important;
    outline:    none !important;
}
</style>
