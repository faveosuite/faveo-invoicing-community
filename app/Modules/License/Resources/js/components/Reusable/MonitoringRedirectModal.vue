<template>
    <modal v-if="showModal" :showModal="showModal" :onClose="onClose" :containerStyle="containerStyle" :showCloseBtn="true" :showFooter="false">

        <template #title>
            <div class="d-flex align-items-center gap-1 fs-5">
                <div class="d-inline-flex align-items-center justify-content-center rounded-2 bg-warning-subtle text-warning p-2" aria-hidden="true">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                Monitoring unavailable
            </div>
        </template>

        <template #fields>
            <div>
                <div class="mb-3 fs-4 fw-bold text-dark">{{ toolLabel }} could not load</div>

                <p class="mb-2 fw-semibold text-dark">Invalid installation path detected.</p>
                <p class="text-muted mb-3">Folder-based installations are not supported.</p>

                <div class="small fw-semibold text-muted mb-2">Example</div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fas fa-times-circle text-danger fa-xs"></i>
                    <div class="small fw-semibold  mb-0">
                        <strong>Not Supported</strong> · <span class="text-monospace text-muted small">https://yourdomain.com/myapp</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-4">
                    <i class="fas fa-check-circle text-success fa-xs"></i>
                    <div class="small fw-semibold  mb-0">
                        <strong>Supported</strong> · <span class="text-monospace text-muted small">https://yourdomain.com</span>
                    </div>
                </div>

                <p class="text-muted mb-3">Please install the application on a root domain or subdomain.</p>

                <ul class="text-muted small mb-3 ps-3">
                    <li>Move the application to the web root (so it loads at the domain root).</li>
                    <li>Or configure a separate subdomain (recommended) pointing to the application.</li>
                    <li>Then clear cache and try opening Pulse/Horizon again.</li>
                </ul>

                <p class="text-muted small mb-3">
                    This page could not open because {{ toolLabel }} does not support folder-based installations.
                    Please move the app to the domain root or use a subdomain and try again.
                </p>
            </div>
        </template>
    </modal>
</template>

<script>
export default {
    name: 'monitoring-redirect-modal',
    props: {
        showModal: { type: Boolean, default: false },
        onClose: { type: Function, required: true },
        tool: { type: String, default: 'Pulse' },
    },
    computed: {
        toolLabel() {
            const trimmed = String(this.tool || '').trim();
            return trimmed || 'Pulse';
        },
    },
    data() {
        return {
            containerStyle: { width: '740px' },
        };
    },
};
</script>
