import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'
import pinia from './plugins/pinia.js'

const el = document.getElementById('app-root')
const theme = el?.dataset?.theme || 'adminlte'

// load theme first, then mount
import(`./themes/${theme}/index.js`).then(themeModule => {
    const app = createApp(App)

    // register all theme components globally
    const components = themeModule.components || themeModule.default?.components
    if (components) {
        Object.entries(components).forEach(([name, component]) => {
            app.component(name, component)
        })
    }

    app.use(pinia)
    app.use(router)
    app.mount('#app-root')
})
