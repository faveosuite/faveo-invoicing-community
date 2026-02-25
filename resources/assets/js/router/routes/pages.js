export default [
    { path: '/pages',          component: () => import('../../pages/pages/Index.vue'),  meta: { title: 'Pages',     titleKey: 'message.pages' } },
    { path: '/pages/create',   component: () => import('../../pages/pages/Create.vue'), meta: { title: 'New Page',  titleKey: 'message.new_page' } },
    { path: '/pages/:id/edit', component: () => import('../../pages/pages/Edit.vue'),   meta: { title: 'Edit Page', titleKey: 'message.edit_page' } },
    { path: '/pages/demo',     component: () => import('../../pages/pages/Demo.vue'),   meta: { title: 'Demo Page', titleKey: 'message.add-demo' } },
]
