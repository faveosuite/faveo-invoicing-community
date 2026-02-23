export default [
    { path: '/settings/widgets/footer', component: () => import('../../../pages/settings/widgets/Footer.vue') },
    { path: '/settings/widgets/social-media', component: () => import('../../../pages/settings/widgets/socialMedia/Index.vue') },
    { path: '/settings/widgets/social-media/create', component: () => import('../../../pages/settings/widgets/socialMedia/Create.vue') },
    { path: '/settings/widgets/social-media/:id/edit', component: () => import('../../../pages/settings/widgets/socialMedia/Edit.vue') },
    { path: '/settings/widgets/analytics', component: () => import('../../../pages/settings/widgets/analytics/Index.vue') },
    { path: '/settings/widgets/analytics/create', component: () => import('../../../pages/settings/widgets/analytics/Create.vue') },
    { path: '/settings/widgets/analytics/:id/edit', component: () => import('../../../pages/settings/widgets/analytics/Edit.vue') },
]
