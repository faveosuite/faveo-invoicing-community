import VersionsIndex from '../../../../../app/License/Resources/js/Pages/Version/VersionsIndex.vue';
import VersionsView from '../../../../../app/License/Resources/js/Pages/Version/VersionsView.vue';

import LicenseCreateEdit from '../../../../../app/License/Resources/js/Pages/License/LicenseCreateEdit.vue';
import LicensesIndex from '../../../../../app/License/Resources/js/Pages/License/LicensesIndex.vue';
import LicensesView from '../../../../../app/License/Resources/js/Pages/License/LicensesView.vue';

import InstallationsIndex from '../../../../../app/License/Resources/js/Pages/Installations/InstallationsIndex.vue';
import InstallationCreateEdit from '../../../../../app/License/Resources/js/Pages/Installations/InstallationCreateEdit.vue';
import InstallationsView from '../../../../../app/License/Resources/js/Pages/Installations/InstallationsView.vue';

import CallbacksIndex from '../../../../../app/License/Resources/js/Pages/Callbacks/CallbacksIndex.vue';

import BannedHostCreateEdit from '../../../../../app/License/Resources/js/Pages/BannedHost/BannedHostCreateEdit.vue';
import BannedHostsIndex from '../../../../../app/License/Resources/js/Pages/BannedHost/BannedHostsIndex.vue';

import CustomizeNotifications from '../../../../../app/License/Resources/js/Pages/ServerNotifications/CustomizeNotifications.vue';
import CustomizeUpdateNotifications from '../../../../../app/License/Resources/js/Pages/ServerNotifications/CustomizeUpdateNotifications.vue';

import WhiteList from '../../../../../app/License/Resources/js/Pages/WhiteList/WhiteList.vue';
import WhiteListCreate from '../../../../../app/License/Resources/js/Pages/WhiteList/WhiteListCreate.vue';

import ViewCrackingReports from '../../../../../app/License/Resources/js/Pages/Report/ViewCrackingReports.vue';
import ViewLicenseReports from '../../../../../app/License/Resources/js/Pages/Report/ViewLicenseReports.vue';
import ViewUpdateReports from '../../../../../app/License/Resources/js/Pages/Report/ViewUpdateReports.vue';
import ViewSystemReports from '../../../../../app/License/Resources/js/Pages/Report/ViewSystemReports.vue';

export default [
    { path: '/versions', redirect: '/versions/list' },
    { path: '/versions/list', component: VersionsIndex, meta: { title: 'Versions', titleKey: 'message.versions' } },
    { path: '/versions/:id/view', component: VersionsView, meta: { title: 'Version View', titleKey: 'message.versions' } },

    { path: '/licenses', redirect: '/licenses/list' },
    { path: '/licenses/list', component: LicensesIndex, meta: { title: 'Licenses', titleKey: 'message.licenses' } },
    { path: '/licenses/create', component: LicenseCreateEdit, meta: { title: 'New License', titleKey: 'message.licenses' } },
    { path: '/licenses/:id/edit', component: LicenseCreateEdit, meta: { title: 'Edit License', titleKey: 'message.licenses' } },
    { path: '/licenses/:id/view', component: LicensesView, meta: { title: 'License View', titleKey: 'message.licenses' } },

    { path: '/installations', redirect: '/installations/list' },
    { path: '/installations/list', component: InstallationsIndex, meta: { title: 'Installations', titleKey: 'message.installations' } },
    { path: '/installations/:id/edit', component: InstallationCreateEdit, meta: { title: 'Edit Installation', titleKey: 'message.installations' } },
    { path: '/installations/:id/view', component: InstallationsView, meta: { title: 'Installation View', titleKey: 'message.installations' } },

    { path: '/callbacks', redirect: '/callbacks/list' },
    { path: '/callbacks/list', component: CallbacksIndex, meta: { title: 'Callbacks', titleKey: 'message.callbacks' } },

    { path: '/banned-hosts', redirect: '/banned-hosts/list' },
    { path: '/banned-hosts/list', component: BannedHostsIndex, meta: { title: 'Banned Hosts', titleKey: 'message.banned_hosts' } },
    { path: '/banned-hosts/create', component: BannedHostCreateEdit, meta: { title: 'New Banned Host', titleKey: 'message.banned_hosts' } },
    { path: '/banned-hosts/:id/edit', component: BannedHostCreateEdit, meta: { title: 'Edit Banned Host', titleKey: 'message.banned_hosts' } },

    { path: '/whitelist', redirect: '/whitelist/list' },
    { path: '/whitelist/list', component: WhiteList, meta: { title: 'Whitelist IP', titleKey: 'message.whitelist_ip' } },
    { path: '/whitelist/create', component: WhiteListCreate, meta: { title: 'New Whitelist', titleKey: 'message.whitelist_ip' } },
    { path: '/whitelist/:id/edit', component: WhiteListCreate, meta: { title: 'Edit Whitelist', titleKey: 'message.whitelist_ip' } },

    { path: '/server', redirect: '/server/notifications' },
    { path: '/server/notifications', component: CustomizeNotifications, meta: { title: 'License Custom Notification', titleKey: 'message.server_notifications' } },
    { path: '/server/update-notifications', component: CustomizeUpdateNotifications, meta: { title: 'Update Custom Notification', titleKey: 'message.server_notifications' } },
    
    { path: '/log-reports', redirect: '/log-reports/license' },
    { path: '/log-reports/crack', component: ViewCrackingReports, meta: { title: 'Cracking Reports' } },
    { path: '/log-reports/license', component: ViewLicenseReports, meta: { title: 'License Reports' } },
    { path: '/log-reports/update', component: ViewUpdateReports, meta: { title: 'Update Reports' } },
    { path: '/log-reports/system', component: ViewSystemReports, meta: { title: 'System Reports' } },
];
