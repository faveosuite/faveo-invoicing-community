export default [
    { path: '/pages', component: () => import('@/pages/pages/Index.vue'), meta: { title: 'Pages' } },
    { path: '/pages/create', component: () => import('@/pages/pages/Create.vue'), meta: { title: 'New Page' } },
    { path: '/pages/:id/edit', component: () => import('@/pages/pages/Edit.vue'), meta: { title: 'Edit Page' } },
    { path: '/pages/demo', component: () => import('@/pages/pages/Demo.vue'), meta: { title: 'Demo Page' } },
]
