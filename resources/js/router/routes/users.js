export default [
    { path: '/users', component: () => import('@/pages/users/Index.vue'), meta: { title: 'Users' } },
    { path: '/users/create', component: () => import('@/pages/users/Create.vue'), meta: { title: 'New User' } },
    { path: '/users/:id/edit', component: () => import('@/pages/users/Edit.vue'), meta: { title: 'Edit User' } },
    { path: '/users/suspended', component: () => import('@/pages/users/Suspended.vue'), meta: { title: 'Suspended Users' } },
]
