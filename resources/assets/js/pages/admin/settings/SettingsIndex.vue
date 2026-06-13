<template>
    <div>
        <div v-for="section in sections" :key="section.title" class="card card-light mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ section.title }}</h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3">
                    <div class="col" v-for="item in section.items" :key="item.to ?? item.href ?? item.monitor">

                        <!-- Monitoring tools: check subdirectory before opening -->
                        <a v-if="item.monitor"
                           href="javascript:;"
                           class="settings-tile d-flex flex-column align-items-center text-center text-decoration-none gap-2 py-2"
                           @click="checkMonitoring(item.monitor, item.href)">
                            <span class="settings-icon">
                                <i :class="item.icon"></i>
                            </span>
                            <small class="text-body-secondary lh-sm">{{ item.label }}</small>
                        </a>

                        <!-- External links -->
                        <a v-else-if="item.href"
                           :href="item.href"
                           target="_blank"
                           rel="noopener"
                           class="settings-tile d-flex flex-column align-items-center text-center text-decoration-none gap-2 py-2">
                            <span class="settings-icon">
                                <i :class="item.icon"></i>
                            </span>
                            <small class="text-body-secondary lh-sm">{{ item.label }}</small>
                        </a>

                        <!-- Internal Vue routes -->
                        <RouterLink v-else :to="item.to"
                                    class="settings-tile d-flex flex-column align-items-center text-center text-decoration-none gap-2 py-2">
                            <span class="settings-icon">
                                <i :class="item.icon"></i>
                            </span>
                            <small class="text-body-secondary lh-sm">{{ item.label }}</small>
                        </RouterLink>

                    </div>
                </div>
            </div>
        </div>

        <!-- Monitoring Unavailable Modal -->
        <div v-if="modal.show" class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);" @click.self="modal.show = false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0">
                    <div class="modal-header">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded p-2"
                                  style="background-color: #fff3cd; color: #856d00;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </span>
                            {{ __('message.monitoring_unavailable') }}
                        </h5>
                        <button type="button" class="btn-close" @click="modal.show = false" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h5 class="fw-bold text-dark mb-3">{{ modal.title }}</h5>

                        <p class="fw-semibold text-dark mb-2">
                            {{ __('message.pulse_horizon_invalid_installation_path_detected') }}
                        </p>
                        <p class="text-muted mb-3">
                            {{ __('message.pulse_horizon_folder_based_installations_are_not_supported') }}
                        </p>

                        <div class="mb-3">
                            <div class="small fw-semibold text-muted mb-2">{{ __('message.pulse_horizon_example') }}</div>
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <i class="fas fa-times-circle text-danger fa-xs"></i>
                                <span class="small fw-semibold">
                                    {{ __('message.pulse_horizon_not_supported') }} &middot;
                                    <span class="font-monospace text-muted">{{ __('message.pulse_horizon_not_supported_url') }}</span>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-check-circle text-success fa-xs"></i>
                                <span class="small fw-semibold">
                                    {{ __('message.pulse_horizon_supported') }} &middot;
                                    <span class="font-monospace text-muted">{{ __('message.pulse_horizon_supported_root_url') }}</span>
                                </span>
                            </div>
                        </div>

                        <p class="text-muted mb-2">
                            {{ __('message.pulse_horizon_install_the_application_on_root_domain_or_subdomain') }}
                        </p>
                        <ul class="text-muted small mb-3">
                            <li>{{ __('message.pulse_horizon_next_step_move_application_to_web_root') }}</li>
                            <li>{{ __('message.pulse_horizon_next_step_configure_subdomain') }}</li>
                            <li>{{ __('message.pulse_horizon_next_step_clear_cache_and_try_again') }}</li>
                        </ul>

                        <p v-if="modal.reason" class="text-muted small mb-0">{{ modal.reason }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import http from '@/plugins/axios'

const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const flags = ref({
    is_redis_configured:          false,
    is_debug_mode:                false,
    is_pulse_enabled:             false,
    is_clockwork_enabled:         false,
    is_mail_sending_enabled:      false,
    is_msg91_enabled:             false,
    is_pipedrive_enabled:         false,
    is_recaptcha_enabled:         false,
    is_email_validation_enabled:  false,
})

const modal = ref({ show: false, title: '', reason: '' })

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/index-data`)
        Object.assign(flags.value, res.data?.data ?? {})
    } catch {}
})

async function checkMonitoring(type, url) {
    try {
        const res = await http.get(`${baseUrl}/monitoring/check`, { params: { type } })
        const data = res.data?.data ?? {}
        if (data.allowed) {
            window.open(url, '_blank', 'noopener')
        } else {
            modal.value = { show: true, title: data.message ?? '', reason: data.message ?? '' }
        }
    } catch {
        window.open(url, '_blank', 'noopener')
    }
}

const sections = computed(() => [
    {
        title: __('message.system'),
        items: [
            { to: '/settings/company',   icon: 'fas fa-building',     label: __('message.company-settings') },
            { to: '/settings/system',    icon: 'fas fa-display',      label: __('message.system-settings') },
            { to: '/settings/cron',      icon: 'fas fa-gauge',         label: __('message.cron-setting') },
            { to: '/settings/language',  icon: 'fas fa-language',      label: __('message.language') },
            { to: '/settings/file-storage', icon: 'fas fa-hard-drive', label: __('message.file_system') },
            { to: '/settings/common/queues',   icon: 'fas fa-layer-group',  label: __('message.queues') },
            { to: '/settings/common/cache',    icon: 'fas fa-database',     label: __('message.cache') },
            { to: '/settings/debugging',       icon: 'fas fa-bug',          label: __('message.debugging') },
        ],
    },
    {
        title: __('message.license_and_access'),
        items: [
            { to: '/settings/license-type',        icon: 'fas fa-file-lines',      label: __('message.license-type') },
            { to: '/settings/license-permissions', icon: 'fas fa-diagram-project', label: __('message.license_permission') },
            { to: '/settings/localized-license',   icon: 'fas fa-globe',           label: __('message.localized_license') },
            { to: '/settings/system-managers',     icon: 'fas fa-people-group',    label: __('message.system_manager_settings') },
        ],
    },
    {
        title: __('message.billing'),
        items: [
            { to: '/settings/payment-gateway',   icon: 'fas fa-credit-card',          label: __('message.payment_gateway_integrations') },
            { to: '/settings/common/currency',   icon: 'fas fa-money-bill-transfer',  label: __('message.currency') },
            { to: '/settings/common/countries',  icon: 'fas fa-globe',                label: __('message.countries') },
            { to: '/settings/common/tax',        icon: 'fas fa-calculator',           label: __('message.tax') },
            { to: '/settings/open-payments',     icon: 'fas fa-money-check-dollar',   label: 'Open Payments' },
        ],
    },
    {
        title: __('message.integrations'),
        items: [
            { to: '/settings/cloud-details',    icon: 'fas fa-cloud',        label: __('message.cloud_hub') },
            { to: '/settings/social-logins',    icon: 'fas fa-id-badge',     label: __('message.social_logins') },
            { to: '/settings/whatsapp-users',   icon: 'fab fa-whatsapp',     label: __('message.whatsapp_users') },
            { to: '/settings/third-party-apps', icon: 'fas fa-puzzle-piece', label: __('message.third_party_apps') },
            { to: '/settings/api/third-party',  icon: 'fas fa-link',         label: __('message.third_party_integrations') },
            ...(flags.value.is_pipedrive_enabled ? [{ to: '/settings/api/pipedrive', icon: 'fas fa-diagram-project', label: __('message.pipedrive') }] : []),
            ...(flags.value.is_recaptcha_enabled ? [{ to: '/settings/api/recaptcha', icon: 'fas fa-shield-halved',   label: __('message.recaptcha') }] : []),
        ],
    },
    {
        title: __('message.communication'),
        items: [
            { to: '/settings/email/settings',          icon: 'fas fa-envelope',   label: __('message.email_settings') },
            { to: '/settings/email/template-settings', icon: 'fas fa-table-list', label: __('message.template_settings') },
            { to: '/settings/email/templates',         icon: 'fas fa-file-lines', label: __('message.email_templates') },
            ...(flags.value.is_mail_sending_enabled ? [{ to: '/settings/contact-options', icon: 'fas fa-phone', label: __('message.contact_options') }] : []),
        ],
    },
    {
        title: __('message.logs_and_monitoring'),
        items: [
            { to: '/settings/logs/system',   icon: 'fas fa-list-ul',         label: __('message.log_setting') },
            { to: '/settings/logs/activity', icon: 'fas fa-wave-square',     label: __('message.activity_logs') },
            { to: '/settings/logs/payment',  icon: 'fas fa-money-bill-wave', label: __('message.payment_logs') },
            ...(flags.value.is_msg91_enabled             ? [{ to: '/settings/logs/msg91',                           icon: 'fas fa-message',   label: __('message.msg_reports') }] : []),
            ...(flags.value.is_email_validation_enabled  ? [{ to: '/settings/api/email-validation/logs',            icon: 'fas fa-list-alt',  label: __('message.email_validation_logs') }] : []),
            ...(flags.value.is_redis_configured ? [{ monitor: 'horizon',   href: `${baseUrl}/horizon`,               icon: 'fas fa-gauge-high', label: 'Queue Monitor' }] : []),
            ...(flags.value.is_clockwork_enabled  ? [{ monitor: 'clockwork', href: `${baseUrl}/clockwork/app`, icon: 'fas fa-clock',     label: __('message.clockwork') }] : []),
            ...(flags.value.is_pulse_enabled      ? [{ monitor: 'pulse',     href: `${baseUrl}/pulse`,         icon: 'fas fa-heartbeat', label: __('message.pulse') }]     : []),
        ],
    },
    {
        title: __('message.widgets'),
        items: [
            { to: '/settings/widgets/footer',       icon: 'fas fa-list-ul',     label: __('message.footer_widget') },
            { to: '/settings/widgets/social-media', icon: 'fas fa-share-nodes', label: __('message.social_media') },
            { to: '/settings/widgets/analytics',    icon: 'fas fa-chart-bar',   label: __('message.analytics') },
        ],
    },
])
</script>

<style scoped>
.settings-icon {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    transition: opacity 0.15s;
    flex-shrink: 0;
    border: 5px solid #C4D8E4;
    color: #3c8dbc;
}

.settings-tile:hover .settings-icon {
    opacity: 0.82;
}
</style>
