<template>
    <div>
        <div v-for="section in sections" :key="section.title" class="card card-light mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ section.title }}</h5>
            </div>
            <div class="card-body">
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-6 g-3">
                    <div class="col" v-for="item in section.items" :key="item.to">
                        <RouterLink :to="item.to"
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
    </div>
</template>

<script setup>
const sections = [
    {
        title: __('message.settings'),
        items: [
            { to: '/settings/system',              icon: 'fas fa-display',           label: __('message.system-settings') },
            { to: '/settings/cron',                icon: 'fas fa-gauge',             label: __('message.cron-setting') },
            { to: '/settings/license-type',        icon: 'fas fa-file-lines',        label: __('message.lic_type') },
            { to: '/settings/license-permissions', icon: 'fas fa-diagram-project',   label: __('message.license_permission') },
            { to: '/settings/file-storage',        icon: 'fas fa-hard-drive',        label: __('message.file_storage') },
            { to: '/settings/payment-gateway',     icon: 'fas fa-credit-card',       label: __('message.payment_gateway_integrations') },
            { to: '/settings/system-managers',     icon: 'fas fa-people-group',      label: __('message.system_manager_settings') },
            { to: '/settings/third-party-apps',    icon: 'fas fa-puzzle-piece',      label: __('message.third_party_apps') },
            { to: '/settings/cloud-details',       icon: 'fas fa-cloud',             label: __('message.cloud_hub') },
            { to: '/settings/localized-license',   icon: 'fas fa-globe',             label: __('message.localized_license') },
            { to: '/settings/debugging',           icon: 'fas fa-bug',               label: __('message.debugging') },
            { to: '/settings/social-logins',       icon: 'fas fa-id-badge',          label: __('message.social-logins') },
            { to: '/settings/language',            icon: 'fas fa-language',          label: __('message.language') },
            { to: '/settings/whatsapp-users',      icon: 'fab fa-whatsapp',          label: __('message.whatsapp_users') },
            { to: '/settings/contact-options',     icon: 'fas fa-phone',             label: __('message.contact_options') },
        ],
    },
    {
        title: __('message.logs'),
        items: [
            { to: '/settings/logs/system',   icon: 'fas fa-list-ul',          label: __('message.log_setting') },
            { to: '/settings/logs/activity', icon: 'fas fa-wave-square',      label: __('message.activity_logs') },
            { to: '/settings/logs/payment',  icon: 'fas fa-money-bill-wave',  label: __('message.payment_logs') },
            { to: '/settings/logs/msg91',    icon: 'fas fa-message',          label: __('message.msg91_reports') },
        ],
    },
    {
        title: __('message.email'),
        items: [
            { to: '/settings/email/settings',          icon: 'fas fa-envelope',     label: __('message.email_settings') },
            { to: '/settings/email/template-settings', icon: 'fas fa-table-list',   label: __('message.template_settings') },
            { to: '/settings/email/templates',         icon: 'fas fa-file-lines',   label: __('message.email_templates') },
        ],
    },
    {
        title: __('message.common'),
        items: [
            { to: '/settings/common/currency',  icon: 'fas fa-money-bill-transfer', label: __('message.currency') },
            { to: '/settings/common/countries', icon: 'fas fa-globe',               label: __('message.countries') },
            { to: '/settings/common/queues',    icon: 'fas fa-layer-group',         label: __('message.queues') },
            { to: '/settings/common/tax',       icon: 'fas fa-calculator',          label: __('message.tax') },
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
    {
        title: __('message.api_integrations'),
        items: [
            { to: '/settings/api/pipedrive',   icon: 'fas fa-diagram-project', label: __('message.pipedrive') },
            { to: '/settings/api/recaptcha',   icon: 'fas fa-shield-halved',   label: __('message.recaptcha') },
            { to: '/settings/api/third-party', icon: 'fas fa-link',            label: __('message.third_party_integrations') },
        ],
    },
]
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
