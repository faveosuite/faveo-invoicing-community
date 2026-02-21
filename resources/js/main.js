import { createApp } from 'vue'
import App from './App.vue'
import router from './router/index.js'
import pinia from './plugins/pinia.js'
import themeLoader from './plugins/themeLoader.js'

const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(themeLoader)

app.mount('#app-root')
