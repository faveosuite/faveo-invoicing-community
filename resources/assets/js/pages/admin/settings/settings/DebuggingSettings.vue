<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.debugging_settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row g-4">

                        <!-- Application Debugging -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 shadow-sm h-100">
                                <div class="card-header bg-light d-flex align-items-center gap-3 p-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 p-1">
                                        <i class="fas fa-bug fs-1 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fs-6 fw-bold">{{ __('message.application_debugging') }}</h5>
                                        <small class="text-muted">{{ __('message.application_debugging_desc') }}</small>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <p class="text-uppercase text-muted small fw-bold mb-3">
                                        {{ __('message.debugging_options') }}
                                    </p>

                                    <div class="border rounded-2 p-3 mb-2 d-flex align-items-center justify-content-between">
                                        <div class="pe-3">
                                            <strong>{{ __('message.debug_mode') }}</strong>
                                            <p class="text-muted small mb-0 mt-1">{{ __('message.debug_mode_description') }}</p>
                                        </div>
                                        <Switch name="debug" :value="form.debug" :onChange="(val) => form.debug = val" />
                                    </div>

                                    <div class="border rounded-2 p-3 mb-2 d-flex align-items-center justify-content-between">
                                        <div class="pe-3">
                                            <strong>{{ __('message.pulse_monitoring') }}</strong>
                                            <p class="text-muted small mb-0 mt-1">{{ __('message.pulse_monitoring_desc') }}</p>
                                        </div>
                                        <Switch name="pulse_enabled" :value="form.pulse_enabled" :onChange="(val) => form.pulse_enabled = val" />
                                    </div>

                                    <div class="border rounded-2 p-3 d-flex align-items-center justify-content-between">
                                        <div class="pe-3">
                                            <strong>{{ __('message.clockwork_debugging') }}</strong>
                                            <p class="text-muted small mb-0 mt-1">{{ __('message.clockwork_debugging_desc') }}</p>
                                        </div>
                                        <Switch name="clockwork_enable" :value="form.clockwork_enable" :onChange="(val) => form.clockwork_enable = val" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Application Monitoring -->
                        <div class="col-md-6">
                            <div class="card border rounded-3 shadow-sm h-100">
                                <div class="card-header bg-light d-flex align-items-center gap-3 p-3">
                                    <div class="d-flex align-items-center justify-content-center rounded-3 p-1">
                                        <i class="fas fa-satellite-dish fs-1 text-primary"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fs-6 fw-bold">{{ __('message.application_monitoring') }}</h5>
                                        <small class="text-muted">{{ __('message.application_monitoring_desc') }}</small>
                                    </div>
                                </div>

                                <div class="card-body p-3">
                                    <p class="text-uppercase text-muted small fw-bold mb-3">
                                        {{ __('message.monitoring_options') }}
                                    </p>

                                    <div class="border rounded-2 p-3 mb-2 d-flex align-items-center justify-content-between">
                                        <div class="pe-3">
                                            <strong>{{ __('message.sentry_crash_reporting') }}</strong>
                                            <Tooltip :message="__('message.sentry_crash_reporting_tooltip')" />
                                            <p class="text-muted small mb-0 mt-1">{{ __('message.sentry_crash_reporting_desc') }}</p>
                                        </div>
                                        <Switch name="sentry_reporting" :value="form.sentry_reporting" :onChange="(val) => form.sentry_reporting = val" />
                                    </div>

                                    <div class="border rounded-2 p-3 d-flex align-items-center justify-content-between">
                                        <div class="pe-3">
                                            <strong>{{ __('message.sentry_performance') }}</strong>
                                            <Tooltip :message="__('message.sentry_performance_tooltip')" />
                                            <p class="text-muted small mb-0 mt-1">{{ __('message.sentry_performance_desc') }}</p>
                                        </div>
                                        <Switch name="sentry_performance" :value="form.sentry_performance" :onChange="(val) => form.sentry_performance = val" />
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'debugging-settings'

const loading = ref(true)
const saving  = ref(false)

const form = ref({
    debug:              false,
    pulse_enabled:      false,
    clockwork_enable:   false,
    sentry_reporting:   false,
    sentry_performance: false,
})

onMounted(async () => {
    try {
        const res = await http.get(`/debugg`)
        Object.assign(form.value, res.data?.data ?? {})
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`/save/debugg`, form.value)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
