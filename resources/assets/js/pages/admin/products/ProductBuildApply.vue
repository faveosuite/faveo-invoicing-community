<template>
    <div>
        <AppAlert componentName="product-build-apply" />

        <div class="card card-light mb-0">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.apply_build_to_products') || 'Shared Build Release' }}</h4>
            </div>

            <div class="card-body">
                <div class="alert alert-info d-flex gap-2 align-items-start mb-3">
                    <i class="fas fa-info-circle mt-1 flex-shrink-0"></i>
                    <div>{{ __('message.apply_build_hint') }}</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <DynamicSelect name="release_type" :label="__('message.release_type')" :required="true"
                            :elements="releaseTypes" :value="selectedReleaseType"
                            :onChange="(v) => form.release_type = v?.value ?? ''" :clearable="false" :searchable="false" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-block">{{ __('message.file') }}<span class="text-danger ms-1">*</span></label>
                        <input type="file" class="form-control" accept=".zip" :disabled="uploading" @change="onFile" />
                        <UploadStatus :uploading="uploading" :progress="uploadProgress" :error="fileError" :uploadedName="uploadedName" />
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold d-block">{{ __('message.private_release') }}</label>
                        <Switch name="is_private" :value="form.is_private" :onChange="(v) => form.is_private = v" />
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold d-block">{{ __('message.restrict_update') }}</label>
                        <Switch name="is_restricted" :value="form.is_restricted" :onChange="(v) => form.is_restricted = v" />
                    </div>

                    <div class="col-md-12">
                        <TinyMCE name="description" id="editor-apply-build-description" :label="__('message.description')"
                            :value="form.description" :onChange="(v) => { form.description = v; setFieldError('description', undefined) }"
                            :error="errors.description" />
                    </div>

                    <div class="col-md-12">
                        <TextArea name="dependencies" type="textarea" :rows="6" :length="100000" :label="__('message.dependencies')" :required="true"
                            :hint="__('message.enter_json_format')" :value="form.dependencies"
                            :onChange="(v) => { form.dependencies = v; setFieldError('dependencies', undefined) }" :error="errors.dependencies" />
                    </div>
                </div>

                <hr class="my-4" />

                <div class="card card-light products-panel mb-0">
                    <div class="card-header d-flex flex-column gap-2">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <TextField name="main_version" :label="__('message.main_version') || 'Main Version'" :value="mainVersion"
                                    :hint="__('message.main_version_hint')" :onChange="onMainVersionChange" />
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h4 class="card-title mb-0">{{ __('message.products') }}<span class="text-danger ms-1">*</span></h4>
                            <span :class="['badge', selectedCount > 0 ? 'bg-primary' : 'bg-secondary']">
                                {{ selectedCount === 0 ? (__('message.none_selected') || 'None selected') : selectedCount + ' ' + (__('message.selected') || 'selected') }}
                            </span>
                            <div class="ms-auto d-flex align-items-center gap-2">
                                <div class="search-box">
                                    <i class="fas fa-search search-box-icon"></i>
                                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                                        v-model="productSearch" :placeholder="searchPlaceholder" />
                                </div>
                                <button type="button" :class="['chip', { 'chip-active': onlySelected }]" @click="toggleOnlySelected">
                                    <i class="fas fa-check" style="font-size: 10px;"></i>{{ __('message.selected_only') || 'Selected only' }}
                                </button>
                                <button type="button" class="chip" @click="clearAll">
                                    <i class="fas fa-times" style="font-size: 10px;"></i>{{ __('message.clear') || 'Clear' }}
                                </button>
                            </div>
                        </div>
                        <div v-if="errors.products" class="invalid-feedback d-block mb-0">{{ errors.products }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            <button v-for="c in groupChips" :key="c.groupName" type="button"
                                :class="['chip', { 'chip-active': c.active }]" @click="selectGroup(c.groupName)">
                                {{ c.groupName }}
                                <span class="chip-count">{{ c.count > 0 ? c.count + '/' + c.total : c.total }}</span>
                            </button>
                        </div>
                    </div>

                    <div v-if="loadingProducts" class="text-center py-4"><loader /></div>

                    <template v-else>
                        <div class="products-toolbar d-flex align-items-center gap-3 flex-wrap">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="select-all-visible"
                                    :checked="allVisibleSelected" :disabled="!visibleProducts.length" @change="toggleAllVisible" />
                                <label class="form-check-label small" for="select-all-visible">{{ selectAllLabel }}</label>
                            </div>
                            <span class="vr d-none d-sm-block"></span>
                            <span class="small text-muted flex-shrink-0">{{ __('message.group_version') || 'Version for this view' }}</span>
                            <input type="text" class="form-control form-control-sm" style="width: 140px;"
                                :value="bulkVersionInput" @input="onBulkVersionInput($event.target.value)"
                                :placeholder="__('message.version') || 'Version'" />
                            <span class="ms-auto small text-muted">{{ visibleMeta }}</span>
                        </div>

                        <div class="products-grid-wrap flex-grow-1">
                            <div v-if="!visibleProducts.length" class="d-flex flex-column align-items-center gap-2 text-center py-5">
                                <i class="far fa-folder-open text-muted" style="font-size: 22px;"></i>
                                <span>{{ emptyTitle }}</span>
                                <span class="text-muted small">{{ emptyHint }}</span>
                            </div>
                            <div v-else class="products-grid">
                                <div v-for="p in visibleProducts" :key="p.id" :class="['product-row', { 'product-row-checked': selectedProductIds.includes(p.id) }]">
                                    <input class="form-check-input flex-shrink-0" type="checkbox" :id="`product-${p.id}`"
                                        :checked="selectedProductIds.includes(p.id)" @change="toggleProduct(p.id)">
                                    <label class="flex-grow-1 min-width-0 mb-0" :for="`product-${p.id}`">
                                        <div class="product-row-name">{{ p.name }}</div>
                                        <div v-if="subtitleFor(p)" class="product-row-sub">{{ subtitleFor(p) }}</div>
                                    </label>
                                    <input v-if="selectedProductIds.includes(p.id)" type="text" class="form-control form-control-sm product-row-version"
                                        :placeholder="__('message.version') || 'version'" v-model="productVersions[p.id]">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="card-footer">
                <AppButton>
                    <button type="button" class="btn btn-primary" :disabled="saving || uploading" @click="submit">
                        <i class="fas fa-circle-notch fa-spin me-1" v-if="saving"></i>
                        <i class="fas fa-save me-1" v-else></i>
                        {{ saving ? __('message.please_wait') : __('message.save') }}
                    </button>
                </AppButton>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import TextArea from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import UploadStatus from '@/components/Reusable/UploadStatus.vue'
import { useChunkedFileUpload } from '@/core/composables/useChunkedFileUpload'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const router = useRouter()
const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()

const loadingProducts = ref(true)
const rawGroups = ref([]) // [{ groupName, products: [{id, name}] }] — pre-grouped + permission-filtered server-side
const activeGroup = ref('')
const onlySelected = ref(false)
const productSearch = ref('')
const selectedProductIds = ref([])
const productVersions = ref({}) // { [productId]: version } — cascades main -> bulk-for-view -> individual, last one touched wins
const mainVersion = ref('') // stamps every product across every group the moment it changes
const bulkVersionInput = ref('') // transient — stamps whatever is currently visible (group / search / tray)

onMounted(async () => {
    try {
        const res = await http.get('/dependency/products', { params: { permission: 'downloadPermission' } })
        const groups = res.data?.data?.products ?? []
        rawGroups.value = groups.map(g => ({ groupName: g.name, products: g.children ?? [] }))
        activeGroup.value = rawGroups.value[0]?.groupName ?? ''
    } finally {
        loadingProducts.value = false
    }
})

const allProductsFlat = computed(() =>
    rawGroups.value.flatMap(g => g.products.map(p => ({ id: p.id, name: p.name, groupName: g.groupName, licenseType: p.license_type })))
)

const searchPlaceholder = computed(() => {
    const total = allProductsFlat.value.length
    return total ? `${__('message.search') || 'Search'} all ${total} products` : (__('message.search') || 'Search')
})

// Three mutually exclusive ways to browse: one active group at a time,
// everything currently selected (regardless of group), or a live search
// across every group. Only one is ever "visible" at once.
const viewMode = computed(() => {
    if (productSearch.value.trim()) return 'search'
    if (onlySelected.value) return 'selected'
    return 'group'
})

const visibleProducts = computed(() => {
    if (viewMode.value === 'search') {
        const q = productSearch.value.trim().toLowerCase()
        return allProductsFlat.value.filter(p => p.name.toLowerCase().includes(q))
    }
    if (viewMode.value === 'selected') {
        return allProductsFlat.value.filter(p => selectedProductIds.value.includes(p.id))
    }
    return allProductsFlat.value.filter(p => p.groupName === activeGroup.value)
})

// Browsing one group at a time, the group is already obvious from the active
// chip, so the second line is more useful as the product's own license type.
// Once results span groups (search / the selected tray), the license type is
// less useful than knowing which group each result actually came from.
function subtitleFor(p) {
    return viewMode.value === 'group' ? p.licenseType : p.groupName
}

// A bulk value typed for one view has no honest meaning once the visible set
// changes underneath it — clear it rather than silently reapplying it.
watch([viewMode, activeGroup], () => { bulkVersionInput.value = '' })

function selectGroup(groupName) {
    activeGroup.value = groupName
    onlySelected.value = false
    productSearch.value = ''
}

function toggleOnlySelected() {
    onlySelected.value = !onlySelected.value
    productSearch.value = ''
}

function toggleProduct(id) {
    const idx = selectedProductIds.value.indexOf(id)
    if (idx === -1) selectedProductIds.value.push(id)
    else selectedProductIds.value.splice(idx, 1)
    setFieldError('products', undefined)
}

const allVisibleSelected = computed(() =>
    visibleProducts.value.length > 0 && visibleProducts.value.every(p => selectedProductIds.value.includes(p.id))
)

function toggleAllVisible() {
    const ids = visibleProducts.value.map(p => p.id)
    if (allVisibleSelected.value) {
        selectedProductIds.value = selectedProductIds.value.filter(id => !ids.includes(id))
    } else {
        selectedProductIds.value = [...new Set([...selectedProductIds.value, ...ids])]
    }
    setFieldError('products', undefined)
}

function onBulkVersionInput(value) {
    bulkVersionInput.value = value
    visibleProducts.value.forEach(p => { productVersions.value[p.id] = value })
}

function onMainVersionChange(value) {
    mainVersion.value = value
    allProductsFlat.value.forEach(p => { productVersions.value[p.id] = value })
}

function clearAll() {
    selectedProductIds.value = []
    productVersions.value = {}
    setFieldError('products', undefined)
}

const selectAllLabel = computed(() => {
    if (viewMode.value === 'search') return `${__('message.select_all') || 'Select All'} (${visibleProducts.value.length})`
    if (viewMode.value === 'selected') return `${__('message.select_all') || 'Select All'} (${visibleProducts.value.length})`
    return `${__('message.select_all') || 'Select All'} (${activeGroup.value})`
})

const visibleMeta = computed(() => {
    const total = visibleProducts.value.length
    const sel = visibleProducts.value.filter(p => selectedProductIds.value.includes(p.id)).length
    if (viewMode.value === 'search') return `${sel} of ${total} results selected`
    if (viewMode.value === 'selected') return `${total} product${total === 1 ? '' : 's'} in the tray`
    return `${sel} of ${total} selected`
})

const emptyTitle = computed(() => {
    if (viewMode.value === 'selected') return __('message.nothing_selected_yet') || 'Nothing selected yet'
    if (viewMode.value === 'search') return `No products match "${productSearch.value}"`
    return __('message.no_records_found') || 'No records found.'
})
const emptyHint = computed(() => {
    if (viewMode.value === 'selected') return 'Pick products from any group — they collect here.'
    if (viewMode.value === 'search') return 'Try a shorter term, or clear the search to browse by group.'
    return ''
})

const groupChips = computed(() => rawGroups.value.map(g => ({
    groupName: g.groupName,
    count: g.products.filter(p => selectedProductIds.value.includes(p.id)).length,
    total: g.products.length,
    active: viewMode.value === 'group' && activeGroup.value === g.groupName,
})))

const selectedCount = computed(() => selectedProductIds.value.length)

const releaseTypes = [
    { name: __('message.official') || 'Official', value: 'official' },
    { name: __('message.pre_release') || 'Pre Release', value: 'pre_release' },
    { name: __('message.beta') || 'Beta', value: 'beta' },
]
const selectedReleaseType = computed(() => releaseTypes.find(r => r.value === form.value.release_type) ?? releaseTypes[0])

const form = ref({
    description: '', release_type: 'official',
    is_private: false, is_restricted: false, dependencies: '[]',
})

function parseDependencies() {
    const raw = (form.value.dependencies || '').trim() || '[]'
    try {
        const data = JSON.parse(raw)

        return Array.isArray(data) ? { data } : { error: 'invalid_json' }
    } catch {
        return { error: 'invalid_json' }
    }
}

// The upload has its own independent upload step (useChunkedFileUpload) —
// this just checks it actually succeeded.
function validateSlot() {
    if (!file.value) {
        fileError.value = __('message.file')
    } else if (uploading.value) {
        fileError.value = __('message.please_wait')
    } else if (uploadedForFile.value !== file.value && !fileError.value) {
        fileError.value = __('message.something_wrong')
    }
}

async function submit() {
    validateSlot()

    const errs = {}
    if (!selectedProductIds.value.length) {
        errs.products = __('message.select-a-row') || 'Select at least one product.'
    } else if (selectedProductIds.value.some(id => !productVersions.value[id]?.trim())) {
        errs.products = __('message.version_required_per_product') || 'Every selected product needs a version.'
    }
    const descTpl = document.createElement('template')
    descTpl.innerHTML = form.value.description || ''
    if (!descTpl.content.textContent.trim()) {
        errs.description = __('message.description')
    }
    const deps = parseDependencies()
    if (deps.error) errs.dependencies = __('message.enter_json_format') || 'Enter valid JSON format.'
    setErrors(errs)
    if (Object.keys(errs).length || fileError.value) return

    saving.value = true
    try {
        const res = await http.put(`/product/upload-build/apply`, {
            filename: uploadedName.value,
            description: form.value.description,
            release_type: form.value.release_type,
            is_private: form.value.is_private,
            is_restricted: form.value.is_restricted,
            dependencies: deps.data,
            products: selectedProductIds.value.map(id => ({ id, version: productVersions.value[id] })),
        })

        successHandler(res, 'product-build-apply')
        setTimeout(() => router.push('/products'), 2000)
    } catch (err) {
        errorHandler(err, 'product-build-apply', { setErrors })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.min-width-0 {
    min-width: 0;
}

.products-panel {
    display: flex;
    flex-direction: column;
    height: min(78vh, 780px);
    min-height: 480px;
}

.search-box {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.search-box-icon {
    position: absolute;
    left: 10px;
    font-size: 0.75rem;
    color: #99a1af;
    pointer-events: none;
}

.search-box .form-control {
    padding-left: 1.85rem;
}

.chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    border: 1px solid #ced4da;
    background: #fff;
    border-radius: 999px;
    padding: 0.3rem 0.75rem;
    font-size: 0.8rem;
    color: #495057;
    white-space: nowrap;
}

.chip-active {
    background: #3c8dbc;
    border-color: #3c8dbc;
    color: #fff;
}

.chip-count {
    font-size: 0.7rem;
    opacity: 0.85;
}

.products-toolbar {
    padding: 0.6rem 1rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

.products-grid-wrap {
    overflow-y: auto;
    padding: 0.75rem 1rem;
    min-height: 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px 14px;
    align-content: start;
}

.product-row {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 14px;
    border: 1px solid transparent;
}

.product-row label {
    cursor: pointer;
}

.product-row-checked {
    background: #dceefb;
    border-color: #3c8dbc;
}

.product-row .form-check-input {
    width: 22px;
    height: 22px;
    border-radius: 6px;
    flex-shrink: 0;
}

.product-row .form-check-input:checked {
    background-color: #3c8dbc;
    border-color: #3c8dbc;
}

.product-row-name {
    font-size: 0.83rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-row-sub {
    font-size: 0.7rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-row-version {
    width: 90px;
    flex-shrink: 0;
    border-radius: 999px;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}
</style>
