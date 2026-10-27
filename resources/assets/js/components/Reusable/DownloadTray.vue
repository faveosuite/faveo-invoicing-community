<template>
    <!-- Bottom-left: bottom-right is taken by third-party widgets. z-index 1039: above the admin
         sidebar (1038), below offcanvas/modals (1045+). Inline margin: admin common.scss forces
         .card { margin-bottom: 1.5rem !important }, which lifts the tray off the bottom edge. -->
    <section v-if="downloadTray.open && downloadTray.items.length"
         class="position-fixed bottom-0 start-0 ms-3 col-11 col-sm-6 col-lg-4 col-xxl-3 card shadow rounded-0 rounded-top"
         :aria-label="headerTitle" style="z-index: 1039; margin-bottom: 0 !important">
        <div class="card-header d-flex align-items-center gap-2 py-2">
            <span class="flex-grow-1 text-truncate fw-semibold" aria-live="polite">{{ headerTitle }}</span>
            <!-- Plain icon button sized to sit beside Bootstrap's btn-close. -->
            <button type="button" class="btn btn-link link-secondary p-0 lh-1"
                    :aria-label="downloadTray.collapsed ? __('message.expand') : __('message.minimize')"
                    v-tooltip="downloadTray.collapsed ? __('message.expand') : __('message.minimize')"
                    @click="downloadTray.collapsed = !downloadTray.collapsed">
                <i :class="downloadTray.collapsed ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" aria-hidden="true"></i>
            </button>
            <button type="button" class="btn-close ms-2 mb-0" :aria-label="__('message.close')" v-tooltip="__('message.close')"
                    @click="closeDownloadTray"></button>
        </div>

        <template v-if="!downloadTray.collapsed">
            <div v-if="active.length" class="d-flex justify-content-between align-items-center gap-3 px-3 py-2 small bg-primary-subtle text-body-secondary">
                <span>{{ __('message.download_tray_hint') }}</span>
                <a href="#" class="link-primary fw-medium text-decoration-none" @click.prevent="cancelAllDownloads">{{ __('message.download_cancel_all') }}</a>
            </div>

            <ul class="list-group list-group-flush">
                <li v-for="item in downloadTray.items" :key="item.id" class="list-group-item d-flex align-items-center gap-3">
                    <i :class="[iconFor(item.name), 'fs-5 text-center']" aria-hidden="true"></i>

                    <div class="flex-grow-1 text-truncate">
                        <div class="fw-medium text-truncate">{{ item.name }}</div>
                        <div v-if="isActiveDownload(item)" class="progress my-1" role="progressbar"
                             :aria-label="item.name" aria-valuemin="0" aria-valuemax="100"
                             :aria-valuenow="percent(item) ?? undefined" style="height: 8px">
                            <!-- bg-primary: Porto paints every .progress-bar grey unless given a colour.
                                 8px so the moving stripes are visible. Width is the live %. -->
                            <div class="progress-bar bg-primary"
                                 :class="{ 'progress-bar-striped progress-bar-animated w-100': percent(item) === null }"
                                 :style="percent(item) === null ? undefined : { width: percent(item) + '%' }"></div>
                        </div>
                        <div class="small text-truncate" :class="metaClass(item)">{{ meta(item) }}</div>
                    </div>

                    <button v-if="isActiveDownload(item)" type="button" class="btn-close mb-0"
                            :aria-label="__('message.cancel')" v-tooltip="__('message.cancel')" @click="cancelDownload(item)"></button>
                    <i v-else-if="item.status === 'started'" class="fa-solid fa-circle-check fs-5 text-success" aria-hidden="true"></i>
                    <!-- Same small row-action button the app uses elsewhere (Copy/Reissue, product Download). -->
                    <button v-else-if="item.status === 'failed'" type="button" class="btn btn-light btn-sm table_btn text-nowrap"
                            @click="retryDownload(item)">
                        <i class="fa-solid fa-rotate-right me-1" aria-hidden="true"></i>{{ __('message.retry') }}
                    </button>
                </li>
            </ul>
        </template>
    </section>
</template>

<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import {
    downloadTray, isActiveDownload, cancelDownload, retryDownload, cancelAllDownloads, closeDownloadTray,
} from '@/core/composables/useDownload'
import { __ } from '@/plugins/i18n'

const active  = computed(() => downloadTray.items.filter(isActiveDownload))
const started = computed(() => downloadTray.items.filter(i => i.status === 'started'))
const failed  = computed(() => downloadTray.items.filter(i => i.status === 'failed'))

// One/other keys: __() has no plural rules, and "1 downloads" reads badly.
const plural = (key, count) => __(`message.${key}_${count === 1 ? 'one' : 'other'}`, { count })

const headerTitle = computed(() => {
    if (active.value.length) return plural('download_tray_preparing', active.value.length)
    if (failed.value.length) return __('message.download_tray_ready_failed', { ready: started.value.length, failed: failed.value.length })
    if (started.value.length) return plural('download_tray_ready', started.value.length)
    return __('message.download_tray_cancelled')
})

// Ticks once a second only while something is preparing, for its running timer.
const now = ref(Date.now())
let timer = null
watch(() => downloadTray.items.some(i => i.status === 'preparing'), (preparing) => {
    clearInterval(timer)
    if (preparing) timer = setInterval(() => { now.value = Date.now() }, 1000)
}, { immediate: true })
onBeforeUnmount(() => clearInterval(timer))

function elapsed(item) {
    const secs = Math.max(0, Math.floor((now.value - item.startedAt) / 1000))
    return `${Math.floor(secs / 60)}:${String(secs % 60).padStart(2, '0')}`
}

// Null when the server didn't send a size — the bar then just moves, no made-up %.
function percent(item) {
    if (item.status !== 'downloading' || !item.total) return null
    return Math.min(100, Math.round(item.loaded / item.total * 100))
}

// KB under 1 MB (a PDF would read "0 of 0 MB"), one decimal under 10 MB.
function size(bytes) {
    if (bytes < 1048576) return `${Math.max(1, Math.round(bytes / 1024))} KB`
    const mb = bytes / 1048576
    return `${mb < 10 ? mb.toFixed(1) : Math.round(mb)} MB`
}

function meta(item) {
    switch (item.status) {
        case 'preparing':   return __('message.download_meta_preparing', { time: elapsed(item) })
        case 'downloading': return percent(item) === null
            ? __('message.download_meta_downloading_unknown')
            : __('message.download_meta_progress', { percent: percent(item), loaded: size(item.loaded), total: size(item.total) })
        case 'started':     return __('message.download_meta_ready')
        case 'failed':      return item.error
        default:            return __('message.download_meta_cancelled')
    }
}

function metaClass(item) {
    return item.status === 'failed' ? 'text-danger' : 'text-body-secondary'
}

function iconFor(name) {
    const ext = (name ?? '').split('.').pop().toLowerCase()
    if (['zip', 'tar', 'gz', 'rar'].includes(ext)) return 'fa-solid fa-file-zipper text-warning'
    if (ext === 'pdf') return 'fa-solid fa-file-pdf text-danger'
    return 'fa-solid fa-file text-primary'
}
</script>
