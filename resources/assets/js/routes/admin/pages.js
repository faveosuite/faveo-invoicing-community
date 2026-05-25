export default [
    { path: '/pages',          component: () => import('../../pages/admin/pages/FrontendPageIndex.vue'),  meta: { title: 'Pages',     titleKey: 'message.pages' } },
    { path: '/pages/create',   component: () => import('../../pages/admin/pages/FrontendPageCreate.vue'), meta: { title: 'Create New Page', titleKey: 'message.create_new_page' } },
    { path: '/pages/:id/edit', component: () => import('../../pages/admin/pages/FrontendPageEdit.vue'),   meta: { title: 'Edit Page', titleKey: 'message.edit_page' } },
    { path: '/pages/demo',     component: () => import('../../pages/admin/pages/FrontendPageDemo.vue'),   meta: { title: 'Demo Page', titleKey: 'message.add-demo' } },
]
