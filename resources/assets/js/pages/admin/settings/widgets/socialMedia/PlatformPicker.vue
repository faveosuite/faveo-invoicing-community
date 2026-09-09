<template>
    <FormFieldTemplate :label="label" :name="name" :required="required">
        <div class="dropdown">
            <button
                type="button"
                class="form-select text-start d-flex align-items-center justify-content-between"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <span class="d-flex align-items-center gap-2 text-truncate">
                    <i :class="selected.fa_class" :style="{ color: selected.color, width: '18px' }" class="text-center"></i>
                    <span class="fw-medium">{{ selected.label }}</span>
                </span>
            </button>
            <div class="dropdown-menu w-100 p-2 shadow-sm border" style="max-height: 320px; overflow-y: auto;">
                <!-- Search input -->
                <div class="px-1 pb-2">
                    <input
                        v-model="search"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Search platform (e.g. Twitter, YouTube, Discord)..."
                        @click.stop
                    />
                </div>

                <!-- Custom option pinned at the top for instant visibility -->
                <div v-if="showCustomOption" class="px-1 mb-1">
                    <button
                        type="button"
                        class="dropdown-item platform-item d-flex align-items-center justify-content-between py-1.5 px-2 rounded"
                        :class="{ 'is-selected': value === 'custom' }"
                        @click="selectOption(CUSTOM_PLATFORM)"
                    >
                        <span class="d-flex align-items-center gap-2 text-truncate">
                            <i class="fas fa-sliders text-center" style="width: 18px; color: #6c757d;"></i>
                            <span>{{ __('message.custom_enter_own_icon') }}</span>
                        </span>
                        <i v-if="value === 'custom'" class="fas fa-check small text-primary ms-2"></i>
                    </button>
                </div>

                <div v-if="showCustomOption && filteredPresets.length > 0" class="dropdown-divider my-1"></div>

                <!-- Preset options list -->
                <ul class="list-unstyled mb-0">
                    <li v-for="opt in filteredPresets" :key="opt.id">
                        <button
                            type="button"
                            class="dropdown-item platform-item d-flex align-items-center justify-content-between py-1.5 px-2 rounded"
                            :class="{ 'is-selected': opt.id === value }"
                            @click="selectOption(opt)"
                        >
                            <span class="d-flex align-items-center gap-2 text-truncate">
                                <i
                                    :class="opt.fa_class"
                                    :style="{ color: opt.color, width: '18px' }"
                                    class="text-center"
                                ></i>
                                <span>{{ opt.label }}</span>
                            </span>
                            <i v-if="opt.id === value" class="fas fa-check small text-primary ms-2"></i>
                        </button>
                    </li>
                    <li v-if="filteredPresets.length === 0 && !showCustomOption" class="text-muted small text-center py-2">
                        {{ __('message.no_platform_found') }}
                    </li>
                </ul>
            </div>
        </div>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, computed } from 'vue'
import FormFieldTemplate from '@/components/Reusable/FormField/FormFieldTemplate.vue'
import {
    SOCIAL_PLATFORM_OPTIONS,
    CUSTOM_PLATFORM,
} from './platforms'

const props = defineProps({
    label:    { type: String,   required: true },
    name:     { type: String,   required: true },
    options:  { type: Array,    default: () => SOCIAL_PLATFORM_OPTIONS },
    value:    { type: String,   default: '' },
    onChange: { type: Function, required: true },
    required: { type: Boolean,  default: false },
})

const search = ref('')

const selected = computed(() => {
    if (props.value === 'custom') {
        return {
            id: 'custom',
            label: __('message.custom_enter_own_icon'),
            fa_class: 'fas fa-sliders',
            color: '#6c757d',
        }
    }
    return props.options.find(o => o.id === props.value) ?? {
        label: __('message.select_a_platform'),
        fa_class: 'fas fa-icons',
        color: '#6c757d',
    }
})

const showCustomOption = computed(() => {
    const q = search.value.trim().toLowerCase()
    if (!q) return true
    return 'custom'.includes(q) || 'other'.includes(q) || 'icon'.includes(q) || 'own'.includes(q)
})

const filteredPresets = computed(() => {
    const q = search.value.trim().toLowerCase()
    const presets = props.options.filter(o => o.id !== 'custom')
    if (!q) return presets
    return presets.filter(o =>
        o.label?.toLowerCase().includes(q) ||
        o.id?.toLowerCase().includes(q) ||
        o.fa_class?.toLowerCase().includes(q) ||
        o.keywords?.some(k => k.toLowerCase().includes(q))
    )
})

function selectOption(opt) {
    props.onChange(opt.id, opt)
    search.value = ''
}
</script>

<style scoped>
.platform-item {
    color: #212529;
    background-color: transparent;
}
.platform-item:hover {
    background-color: #f1f3f5;
    color: #212529;
}
.platform-item.is-selected {
    background-color: #e9ecef;
    color: #212529;
    font-weight: 600;
}
</style>
