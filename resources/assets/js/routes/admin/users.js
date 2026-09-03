export default [
    { path: '/users',           component: () => import('../../pages/admin/users/UserIndex.vue'),     meta: { title: 'All Contacts',     titleKey: 'message.all-contacts' } },
    { path: '/users/create',   component: () => import('../../pages/admin/users/UserCreateEdit.vue'), meta: { title: 'Create Contact', titleKey: 'message.create_new_user' } },
    { path: '/users/:id',      component: () => import('../../pages/admin/users/UserShow.vue'),      meta: { title: 'User Details',    titleKey: 'message.user_details' } },
    { path: '/users/:id/edit', component: () => import('../../pages/admin/users/UserCreateEdit.vue'), meta: { title: 'Edit User',       titleKey: 'message.edit_user',
        breadcrumb: (route) => [
            { title: 'All Contacts', titleKey: 'message.all-contacts', to: '/users' },
            { title: 'User Details', titleKey: 'message.user_details', to: `/users/${route.params.id}` },
            { title: 'Edit Contact', titleKey: 'message.edit_user' },
        ],
    } },
    { path: '/users/:id/payments/create', component: () => import('../../pages/admin/users/UserPaymentCreate.vue'), meta: { title: 'Create Payment',  titleKey: 'message.create-payment',
        breadcrumb: (route) => [
            { title: 'All Contacts', titleKey: 'message.all-contacts', to: '/users' },
            { title: 'User Details', titleKey: 'message.user_details', to: `/users/${route.params.id}` },
            { title: 'Create Payment', titleKey: 'message.create-payment' },
        ],
    } },
    { path: '/users/:id/payments/:paymentId/edit', component: () => import('../../pages/admin/users/UserPaymentEdit.vue'), meta: { title: 'Apply Payment', titleKey: 'message.apply_payment_to_invoices',
        breadcrumb: (route) => [
            { title: 'All Contacts', titleKey: 'message.all-contacts', to: '/users' },
            { title: 'User Details', titleKey: 'message.user_details', to: `/users/${route.params.id}` },
            { title: 'Apply Payment', titleKey: 'message.apply_payment_to_invoices' },
        ],
    } },
    { path: '/users/suspended', component: () => import('../../pages/admin/users/Suspended.vue'), meta: { title: 'Suspended Users', titleKey: 'message.suspended_users' } },
]
