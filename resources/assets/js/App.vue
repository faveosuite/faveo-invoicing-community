<template>
    <RouterView v-slot="{ Component }">
        <Suspense>
            <component :is="Component" />
            <template #fallback>
                <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading…</span>
                    </div>
                </div>
            </template>
        </Suspense>
    </RouterView>
</template>
