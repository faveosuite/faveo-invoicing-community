export default [
    { path: '/pages',          component: () => import('../../pages/admin/pages/FrontendPageIndex.vue'),  meta: { title: 'All Pages', titleKey: 'message.all_pages' } },
    { path: '/pages/create',   component: () => import('../../pages/admin/pages/FrontendPageCreate.vue'), meta: { title: 'Create New Page', titleKey: 'message.create_new_page' } },
    { path: '/pages/:id/edit', component: () => import('../../pages/admin/pages/FrontendPageEdit.vue'),   meta: { title: 'Edit Page', titleKey: 'message.edit_page' } },
    { path: '/pages/settings', component: () => import('../../pages/admin/pages/PageSettings.vue'),      meta: { title: 'Page Settings', titleKey: 'message.page_settings' } },
]
