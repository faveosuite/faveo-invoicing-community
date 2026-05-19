export default [
    { path: '/users',           component: () => import('../../pages/users/Index.vue'),     meta: { title: 'Users',           titleKey: 'message.users' } },
    { path: '/users/create',    component: () => import('../../pages/users/Create.vue'),    meta: { title: 'Create New User', titleKey: 'message.create_new_user' } },
    { path: '/users/:id',       component: () => import('../../pages/users/Show.vue'),     meta: { title: 'User Details',    titleKey: 'message.user_details' } },
    { path: '/users/:id/edit',  component: () => import('../../pages/users/Edit.vue'),      meta: { title: 'Edit User',       titleKey: 'message.edit_user' } },
    { path: '/users/suspended', component: () => import('../../pages/users/Suspended.vue'), meta: { title: 'Suspended Users', titleKey: 'message.suspended_users' } },
]
