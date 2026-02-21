export default {
    async install(app) {
        const el = document.getElementById('app-root')
        const theme = el?.dataset?.theme || 'adminlte'

        const themeModule = await import(`../themes/${theme}/index.js`)

        Object.entries(themeModule.components).forEach(([name, component]) => {
            app.component(name, component)
        })
    }
}
