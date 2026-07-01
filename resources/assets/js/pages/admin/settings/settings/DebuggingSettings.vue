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
                            <div class="border rounded h-100">
                                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                                    <i class="fas fa-bug fa-lg text-primary"></i>
                                    <div>
                                        <div class="fw-semibold">{{ __('message.application_debugging') }}</div>
                                        <small class="text-muted">{{ __('message.application_debugging_desc') }}</small>
                                    </div>
                                </div>

                                <div class="p-3">
                                    <p class="text-uppercase text-muted small fw-semibold mb-3 section-label">
                                        {{ __('message.debugging_options') }}
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ __('message.debug_mode') }}</div>
                                            <small class="text-muted">{{ __('message.debug_mode_description') }}</small>
                                        </div>
                                        <Switch name="debug" :value="form.debug" :onChange="(val) => form.debug = val" />
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                        <div>
                                            <div class="fw-semibold">{{ __('message.pulse_monitoring') }}</div>
                                            <small class="text-muted">{{ __('message.pulse_monitoring_desc') }}</small>
                                        </div>
                                        <Switch name="pulse_enabled" :value="form.pulse_enabled" :onChange="(val) => form.pulse_enabled = val" />
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between py-3">
                                        <div>
                                            <div class="fw-semibold">{{ __('message.clockwork_debugging') }}</div>
                                            <small class="text-muted">{{ __('message.clockwork_debugging_desc') }}</small>
                                        </div>
                                        <Switch name="clockwork_enable" :value="form.clockwork_enable" :onChange="(val) => form.clockwork_enable = val" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Application Monitoring -->
                        <div class="col-md-6">
                            <div class="border rounded h-100">
                                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                                    <i class="fas fa-satellite-dish fa-lg text-primary"></i>
                                    <div>
                                        <div class="fw-semibold">{{ __('message.application_monitoring') }}</div>
                                        <small class="text-muted">{{ __('message.application_monitoring_desc') }}</small>
                                    </div>
                                </div>

                                <div class="p-3">
                                    <p class="text-uppercase text-muted small fw-semibold mb-3 section-label">
                                        {{ __('message.monitoring_options') }}
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between py-3 border-bottom">
                                        <div>
                                            <div class="fw-semibold d-flex align-items-center gap-1">
                                                {{ __('message.sentry_crash_reporting') }}
                                                <Tooltip :message="__('message.sentry_crash_reporting_tooltip')" />
                                            </div>
                                            <small class="text-muted">{{ __('message.sentry_crash_reporting_desc') }}</small>
                                        </div>
                                        <Switch name="sentry_reporting" :value="form.sentry_reporting" :onChange="(val) => form.sentry_reporting = val" />
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between py-3">
                                        <div>
                                            <div class="fw-semibold d-flex align-items-center gap-1">
                                                {{ __('message.sentry_performance') }}
                                                <Tooltip :message="__('message.sentry_performance_tooltip')" />
                                            </div>
                                            <small class="text-muted">{{ __('message.sentry_performance_desc') }}</small>
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

<style scoped>
.section-label { letter-spacing: .06em; }
</style>
