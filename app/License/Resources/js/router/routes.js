import LicenseLayout from '../Layouts/LicenseManagerLayout.vue';

import Dashboard from '../Pages/Dashboard.vue';

import NotFound from '../Pages/NotFound.vue';

//===========================PRODUCTS MENU=========================

//===========================VERSIONS MENU=========================

import VersionCreateEdit from "../Pages/Version/VersionCreateEdit.vue";

import VersionsIndex from "../Pages/Version/VersionsIndex.vue";

import VersionsView from "../Pages/Version/VersionsView.vue";

let versionsMenu = {

    path: '/versions',

    component: LicenseLayout,

    name: 'Versions',

    redirect: '/versions/list',

    children: [

        {

            path: 'list',

            name: 'Versions Index',

            component: VersionsIndex,

            meta: { title : 'versions', crumb : { link: { name : 'dashboard', to : '/' }, active : 'versions' } }
        },

        {

            path: 'create',

            name: 'Version Create',

            component: VersionCreateEdit,

            meta: { title : 'versions', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'versions', to : '/versions' }, active : 'create' } }
        },

        {

            path: ':id/edit',

            name: 'Version Edit',

            component: VersionCreateEdit,

            meta: { title : 'version', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'version', to : '/versions' }, active : 'edit' } }
        },

        {

            path: ':id/view',

            name: 'Versions View',

            component: VersionsView,

            meta: { title : 'versions', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'versions', to : '/versions' }, active : 'view' } }
        },

    ]
}

//=================================================================

//===========================PROFILE MENU=========================

import Profile from "../Pages/UserProfile/Profile.vue";

let profileMenu = {

    path: '/profile',

    component: LicenseLayout,

    name: 'Profile',

    redirect: '/profile/edit',

    children: [

        {

            path: 'edit',

            name: 'Profile Edit',

            component: Profile,

            meta: { title : 'View Profile', crumb : { link: { name : 'dashboard', to : '/' }, active : 'my_profile' } }
        },

    ]
}

//===========================LICENSES MENU=========================

import LicenseCreateEdit from '../Pages/License/LicenseCreateEdit.vue';

import LicensesIndex from '../Pages/License/LicensesIndex.vue';

import LicensesView from "../Pages/License/LicensesView.vue";

let licensesMenu = {

	path: '/licenses',

	component: LicenseLayout,

	name: 'Licenses',

	redirect: '/licenses/list',

	children: [

		{

			path: 'list',

			name: 'Licenses Index',

			component: LicensesIndex,

			meta: { title : 'licenses', crumb : { link: { name : 'dashboard', to : '/' }, active : 'licenses' } }
		},

		{

			path: 'create',

			name: 'License Create',

			component: LicenseCreateEdit,

			meta: { title : 'licenses', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'licenses', to : '/licenses' }, active : 'create' } }
		},

		{

			path: ':id/edit',

			name: 'License Edit',

			component: LicenseCreateEdit,

			meta: { title : 'licenses', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'licenses', to : '/licenses' }, active : 'edit' } }
		},

        {

            path: ':id/view',

            name: 'License View',

            component: LicensesView,

            meta: { title : 'licenses', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'licenses', to : '/licenses' }, active : 'view' } }
        },
	]
}

//=================================================================

//===========================INSTALLATIONS MENU==========================

import InstallationsIndex from '../Pages/Installations/InstallationsIndex.vue';

import InstallationCreateEdit from '../Pages/Installations/InstallationCreateEdit.vue';

import InstallationsView from "../Pages/Installations/InstallationsView.vue";

let installationsMenu = {

	path: '/installations',

	component: LicenseLayout,

	name: 'Installations',

	redirect: '/installations/list',

	children: [

		{

			path: 'list',

			name: 'Installations Index',

			component: InstallationsIndex,

			meta: { title : 'installations', crumb : { link: { name : 'dashboard', to : '/' }, active : 'installations' } }
		},

		{

			path: ':id/edit',

			name: 'Installation Edit',

			component: InstallationCreateEdit,

			meta: { title : 'installations', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'installations', to : '/installations' }, active : 'edit' } }
		},

        {

            path: ':id/view',

            name: 'Installation View',

            component: InstallationsView,

            meta: { title : 'installations', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'installations', to : '/installations' }, active : 'view' } }
        },
	]
}

//=================================================================

//===========================CALLBACKS MENU==========================

import CallbacksIndex from '../Pages/Callbacks/CallbacksIndex.vue';

let callbacksMenu = {

	path: '/callbacks',

	component: LicenseLayout,

	name: 'Callbacks',

	redirect: '/callbacks/list',

	children: [

		{

			path: 'list',

			name: 'Callbacks Index',

			component: CallbacksIndex,

			meta: { title : 'callbacks', crumb : { link: { name : 'dashboard', to : '/' }, active : 'callbacks' } }
		},
	]
}

//=================================================================

//===========================REPORTS MENU==========================

import ViewCrackingReports from '../Pages/Report/ViewCrackingReports.vue';

import ViewLicenseReports from '../Pages/Report/ViewLicenseReports.vue';

import ViewUpdateReports from "../Pages/Report/ViewUpdateReports.vue";

import ViewSystemReports from '../Pages/Report/ViewSystemReports.vue';

let reportsMenu = {

	path: '/reports',

	component: LicenseLayout,

	name: 'Reports',

	redirect: '/reports',

	children: [

		{

			path: 'crack',

			name: 'View Cracking Report',

			component: ViewCrackingReports,

			meta: { title : 'reports', crumb : { link: { name : 'dashboard', to : '/' }, active : 'view_cracking_reports' } }
		},

        {

            path: 'license',

            name: 'View License Report',

            component: ViewLicenseReports,

            meta: { title : 'reports', crumb : { link: { name : 'dashboard', to : '/' }, active : 'view_license_reports' } }
        },

        {

            path: 'update',

            name: 'View Update Report',

            component: ViewUpdateReports,

            meta: { title : 'reports', crumb : { link: { name : 'dashboard', to : '/' }, active : 'view_update_reports' } }
        },

        {

            path: 'system',

            name: 'View System Report',

            component: ViewSystemReports,

            meta: { title : 'reports', crumb : { link: { name : 'dashboard', to : '/' }, active : 'view_system_reports' } }
        }
	]
}

//=================================================================

//===========================BANNED HOSTS MENU==========================

import BannedHostCreateEdit from '../Pages/BannedHost/BannedHostCreateEdit.vue';

import BannedHostsIndex from '../Pages/BannedHost/BannedHostsIndex.vue';

let bannedMenu = {

	path: '/banned-hosts',

	component: LicenseLayout,

	name: 'Banned Hosts',

	redirect: '/banned-hosts/list',

	children: [

		{

			path: 'list',

			name: 'Banned Hosts Index',

			component: BannedHostsIndex,

			meta: { title : 'banned-hosts', crumb : { link: { name : 'dashboard', to : '/' }, active : 'banned-hosts' } }
		},

		{

			path: 'create',

			name: 'Banned Host Create',

			component: BannedHostCreateEdit,

			meta: { title : 'banned-hosts', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'banned-hosts', to : '/banned-hosts' }, active : 'create' } }
		},

		{

			path: ':id/edit',

			name: 'Banned Host Edit',

			component: BannedHostCreateEdit,

			meta: { title : 'banned-hosts', crumb : { link: { name : 'dashboard', to : '/' }, root_link: { name : 'banned-hosts', to : '/banned-hosts' }, active : 'edit' } }
		},
	]
}

//===========================SERVER NOTIFICATIONS MENU=========================

import CustomizeNotifications from "../Pages/ServerNotifications/CustomizeNotifications.vue";

import CustomizeUpdateNotifications from "../Pages/ServerNotifications/CustomizeUpdateNotifications.vue";

let serverMenu = {

    path: '/server',

    component: LicenseLayout,

    name: 'Server Notifications',

    redirect: '/server/notifications',

    children: [

        {

            path: 'notifications',

            name: 'License Custom Notification',

            component: CustomizeNotifications,

            meta: { title : 'server_notifications', crumb : { link: { name : 'dashboard', to : '/' }, active : 'customize_notifications' } }
        },

        {

            path: 'update-notifications',

            name: 'Update Custom Notification',

            component: CustomizeUpdateNotifications,

            meta: { title : 'server_notifications', crumb : { link: { name : 'dashboard', to : '/' }, active : 'customize_update_notifications' } }
        },

    ]
}

//=================================================================

//===========================WHITELIST IP ======================
import WhiteList from "../Pages/WhiteList/WhiteList.vue";
import WhiteListCreate from "../Pages/WhiteList/WhiteListCreate.vue";

let whitelistMenu = {
    path: '/Whitelist',
    component: LicenseLayout,
    name: 'Whitelist',
    redirect: '/Whitelist/list',
    children: [
        {
            path: 'list',
            name: 'Whitelist Index',
            component: WhiteList,
            meta: { title: 'Whitelist', crumb: { link: { name: 'dashboard', to: '/' }, active: 'Whitelist' } }
        },
        {
            path: 'create',
            name: 'Whitelist Create',
            component: WhiteListCreate,
            meta: { title: 'Whitelist', crumb: { link: { name: 'dashboard', to: '/' }, root_link: { name: 'Whitelist', to: '/whitelist' }, active: 'create' } }
        },
        {
            path: ':id/edit',
            name: 'Whitelist Edit',
            component: WhiteListCreate,
            meta: { title: 'Whitelist', crumb: { link: { name: 'dashboard', to: '/' }, root_link: { name: 'Whitelist', to: '/whitelist' }, active: 'edit' } }
        }
    ]

}

//=================================================================

const routes = [

    {

		path: '/',

		component: LicenseLayout,

		redirect: '/dashboard',

		name: 'Dashboard Layout',

		children: [

			{

				path: 'dashboard',

				name: 'Dashboard',

				component: Dashboard,

				meta: { title : 'dashboard', crumb : { active : 'dashboard' } }
			}
		]
	},

    versionsMenu,

    licensesMenu,

    installationsMenu,

    callbacksMenu,

    reportsMenu,

    profileMenu,

    bannedMenu,

    whitelistMenu,

    serverMenu,

    {
        path: '/:pathMatch(.*)*',
        name:"404",
        component: NotFound,
        meta: { title : '', crumb : { link: { name : 'dashboard', to : '/' }, active : 'Not found' } }
    }
];

export default routes;
