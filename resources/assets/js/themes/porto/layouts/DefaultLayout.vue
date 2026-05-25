<template>
    <div class="body p-relative bottom-1">

        <header id="header" class="header-effect-reveal"
                data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'reveal', 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': false, 'stickyChangeLogo': false, 'stickyStartAt': 200, 'stickySetTop': '-44px'}">
            <Navbar />
        </header>

        <div class="container-fluid py-4">
            <div class="row pt-2">

                <Sidebar />

                <div class="col-lg-9">

                    <Transition name="alert-slide">
                        <div v-if="notification.visible"
                             :class="`alert alert-${notification.type} alert-dismissible`"
                             role="alert">
                            {{ notification.message }}
                            <button type="button" class="btn-close" aria-label="Close"
                                    @click="notification.dismiss" />
                        </div>
                    </Transition>

                    <RouterView v-slot="{ Component }">
                        <Suspense>
                            <component :is="Component" />
                            <template #fallback>
                                <inline-loader />
                            </template>
                        </Suspense>
                    </RouterView>

                </div>
            </div>
        </div>

        <AppFooter />

    </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue'
import { useRoute }            from 'vue-router'
import { useNotification }     from '@/core/composables/useNotification.js'
import { useAlertStore }       from '@/core/stores/alert.js'

const route       = useRoute()
const alertStore  = useAlertStore()
const notification = reactive(useNotification())

// Clear alerts on navigation
import { watch } from 'vue'
watch(() => route.path, () => alertStore.unsetAlert())

// Re-init Porto sticky header after Vue mounts the DOM
onMounted(() => {
    if (window.Theme?.init) {
        window.Theme.init()
    }
})
</script>

<style scoped>
.alert-slide-enter-active,
.alert-slide-leave-active {
    transition: all 0.3s ease;
}
.alert-slide-enter-from,
.alert-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
