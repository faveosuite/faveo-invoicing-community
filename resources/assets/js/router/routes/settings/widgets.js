export default [
    { path: '/settings/widgets/footer',              component: () => import('../../../pages/settings/widgets/Footer.vue'),                  meta: { title: 'Footer Widget' } },
    { path: '/settings/widgets/social-media',        component: () => import('../../../pages/settings/widgets/socialMedia/Index.vue'),       meta: { title: 'Social Media' } },
    { path: '/settings/widgets/social-media/create', component: () => import('../../../pages/settings/widgets/socialMedia/Create.vue'),     meta: { title: 'Add Social Media' } },
    { path: '/settings/widgets/social-media/:id/edit', component: () => import('../../../pages/settings/widgets/socialMedia/Edit.vue'),    meta: { title: 'Edit Social Media' } },
    { path: '/settings/widgets/analytics',           component: () => import('../../../pages/settings/widgets/analytics/Index.vue'),        meta: { title: 'Analytics' } },
    { path: '/settings/widgets/analytics/create',    component: () => import('../../../pages/settings/widgets/analytics/Create.vue'),       meta: { title: 'Add Analytics' } },
    { path: '/settings/widgets/analytics/:id/edit',  component: () => import('../../../pages/settings/widgets/analytics/Edit.vue'),         meta: { title: 'Edit Analytics' } },
]
