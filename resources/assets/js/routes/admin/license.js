const VersionsIndex = () => import('../../../../../app/License/Resources/js/Pages/Version/VersionsIndex.vue');
const VersionsView = () => import('../../../../../app/License/Resources/js/Pages/Version/VersionsView.vue');

const LicenseCreateEdit = () => import('../../../../../app/License/Resources/js/Pages/License/LicenseCreateEdit.vue');
const LicensesIndex = () => import('../../../../../app/License/Resources/js/Pages/License/LicensesIndex.vue');
const LicensesView = () => import('../../../../../app/License/Resources/js/Pages/License/LicensesView.vue');

const InstallationsIndex = () => import('../../../../../app/License/Resources/js/Pages/Installations/InstallationsIndex.vue');
const InstallationCreateEdit = () => import('../../../../../app/License/Resources/js/Pages/Installations/InstallationCreateEdit.vue');
const InstallationsView = () => import('../../../../../app/License/Resources/js/Pages/Installations/InstallationsView.vue');

const CallbacksIndex = () => import('../../../../../app/License/Resources/js/Pages/Callbacks/CallbacksIndex.vue');

const BannedHostCreateEdit = () => import('../../../../../app/License/Resources/js/Pages/BannedHost/BannedHostCreateEdit.vue');
const BannedHostsIndex = () => import('../../../../../app/License/Resources/js/Pages/BannedHost/BannedHostsIndex.vue');
const BannedHostSecuritySettings = () => import('../../../../../app/License/Resources/js/Pages/BannedHost/BannedHostSecuritySettings.vue');

const CustomizeNotifications = () => import('../../../../../app/License/Resources/js/Pages/ServerNotifications/CustomizeNotifications.vue');
const CustomizeUpdateNotifications = () => import('../../../../../app/License/Resources/js/Pages/ServerNotifications/CustomizeUpdateNotifications.vue');

const ViewCrackingReports = () => import('../../../../../app/License/Resources/js/Pages/Report/ViewCrackingReports.vue');
const ViewLicenseReports = () => import('../../../../../app/License/Resources/js/Pages/Report/ViewLicenseReports.vue');
const ViewUpdateReports = () => import('../../../../../app/License/Resources/js/Pages/Report/ViewUpdateReports.vue');
const ViewSystemReports = () => import('../../../../../app/License/Resources/js/Pages/Report/ViewSystemReports.vue');

export default [
    { path: '/versions', redirect: '/versions/list' },
    { path: '/versions/list', component: VersionsIndex, meta: { title: 'All Versions', titleKey: 'message.all_versions' } },
    { path: '/versions/:id/view', component: VersionsView, meta: { title: 'Version View', titleKey: 'message.version_view' } },

    { path: '/licenses', redirect: '/licenses/list' },
    { path: '/licenses/list', component: LicensesIndex, meta: { title: 'All Licenses', titleKey: 'message.all_licenses' } },
    { path: '/licenses/create', component: LicenseCreateEdit, meta: { title: 'New License', titleKey: 'message.new_license' } },
    { path: '/licenses/:id/edit', component: LicenseCreateEdit, meta: { title: 'Edit License', titleKey: 'message.edit_license' } },
    { path: '/licenses/:id/view', component: LicensesView, meta: { title: 'License View', titleKey: 'message.license_view' } },

    { path: '/installations', redirect: '/installations/list' },
    { path: '/installations/list', component: InstallationsIndex, meta: { title: 'All Installations', titleKey: 'message.all_installations' } },
    { path: '/installations/:id/edit', component: InstallationCreateEdit, meta: { title: 'Edit Installation', titleKey: 'message.edit_installation' } },
    { path: '/installations/:id/view', component: InstallationsView, meta: { title: 'Installation View', titleKey: 'message.installation_view' } },

    { path: '/callbacks', redirect: '/callbacks/list' },
    { path: '/callbacks/list', component: CallbacksIndex, meta: { title: 'All Callbacks', titleKey: 'message.callbacks' } },

    { path: '/banned-hosts', redirect: '/banned-hosts/list' },
    { path: '/banned-hosts/list', component: BannedHostsIndex, meta: { title: 'All Banned Hosts', titleKey: 'message.all_banned_hosts' } },
    { path: '/banned-hosts/create', component: BannedHostCreateEdit, meta: { title: 'New Banned Host', titleKey: 'message.new_banned_host' } },
    { path: '/banned-hosts/:id/edit', component: BannedHostCreateEdit, meta: { title: 'Edit Banned Host', titleKey: 'message.edit_banned_host' } },
    { path: '/banned-hosts/settings', component: BannedHostSecuritySettings, meta: { title: 'Banned Host Settings', titleKey: 'message.security_settings' } },

    { path: '/server', redirect: '/server/notifications' },
    { path: '/server/notifications', component: CustomizeNotifications, meta: { title: 'License Custom Notification', titleKey: 'message.license_custom_notification' } },
    { path: '/server/update-notifications', component: CustomizeUpdateNotifications, meta: { title: 'Update Custom Notification', titleKey: 'message.update_custom_notification' } },

    { path: '/log-reports', redirect: '/log-reports/license' },
    { path: '/log-reports/crack', component: ViewCrackingReports, meta: { title: 'Cracking Reports' } },
    { path: '/log-reports/license', component: ViewLicenseReports, meta: { title: 'License Reports' } },
    { path: '/log-reports/update', component: ViewUpdateReports, meta: { title: 'Update Reports' } },
    { path: '/log-reports/system', component: ViewSystemReports, meta: { title: 'System Reports' } },
];
