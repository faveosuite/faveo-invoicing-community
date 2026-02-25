export default [
    { path: '/settings/widgets/footer',                component: () => import('../../../pages/settings/widgets/Footer.vue'),                meta: { title: 'Footer Widget',    titleKey: 'message.footer_widget' } },
    { path: '/settings/widgets/social-media',          component: () => import('../../../pages/settings/widgets/socialMedia/Index.vue'),     meta: { title: 'Social Media',     titleKey: 'message.social_media' } },
    { path: '/settings/widgets/social-media/create',   component: () => import('../../../pages/settings/widgets/socialMedia/Create.vue'),   meta: { title: 'Create New Social Media', titleKey: 'message.create_new_social_media' } },
    { path: '/settings/widgets/social-media/:id/edit', component: () => import('../../../pages/settings/widgets/socialMedia/Edit.vue'),     meta: { title: 'Edit Social Media', titleKey: 'message.edit_social_media' } },
    { path: '/settings/widgets/analytics',             component: () => import('../../../pages/settings/widgets/analytics/Index.vue'),      meta: { title: 'Analytics',        titleKey: 'message.analytics' } },
    { path: '/settings/widgets/analytics/create',      component: () => import('../../../pages/settings/widgets/analytics/Create.vue'),     meta: { title: 'Add Analytics',    titleKey: 'message.add_analytics' } },
    { path: '/settings/widgets/analytics/:id/edit',    component: () => import('../../../pages/settings/widgets/analytics/Edit.vue'),       meta: { title: 'Edit Analytics',   titleKey: 'message.edit_analytics' } },
]
