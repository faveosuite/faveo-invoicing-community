export default {
    async install(app) {
        const el = document.getElementById('app-root')
        const theme = el?.dataset?.theme || 'adminlte'

        let themeModule
        try {
            themeModule = await import(`../themes/${theme}/index.js`)
        } catch {
            console.warn(`Theme "${theme}" not found, falling back to adminlte.`)
            themeModule = await import('../themes/adminlte/index.js')
        }

        Object.entries(themeModule.components).forEach(([name, component]) => {
            app.component(name, component)
        })
    }
}
