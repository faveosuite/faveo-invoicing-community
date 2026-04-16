import { createWebHistory, createRouter } from "vue-router";

import routes  from './routes';

const router = createRouter({

    history: createWebHistory((document.querySelector('meta[name="app-base-path"]')?.content || '') + '/license-manager'),

    inkActiveClass: '',

    linkExactActiveClass :'active exact-active',

    routes
})

export default router;
