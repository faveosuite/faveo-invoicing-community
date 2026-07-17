<template>
    <div>
        <AppAlert componentName="product-build-apply" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.apply_build_to_products') || 'Shared Build Release' }}</h4>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    {{ __('message.apply_build_hint') }}
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <SelectField name="release_type" :label="__('message.release_type')" :required="true"
                            :elements="releaseTypes" :value="selectedReleaseType"
                            :onChange="(v) => form.release_type = v?.value ?? ''" :clearable="false" :searchable="false" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold d-block">
                            {{ __('message.source') || 'Source' }}
                            <span v-if="needsSource" class="text-danger ms-1">*</span>
                            <ToolTip :message="__('message.source_build_file_hint') || 'Use this for a regular release. It covers every selected product, except any that specifically use an encoded build.'" size="small" />
                        </label>
                        <input type="file" class="form-control" accept=".zip" :disabled="sourceUploading" @change="onSourceFile" />
                        <UploadStatus :uploading="sourceUploading" :progress="sourceUploadProgress" :error="sourceFileError" :uploadedName="sourceUploadedName" />
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold d-block">
                            {{ __('message.obfuscated') || 'Obfuscated / Encoded' }}
                            <span v-if="needsObfuscated" class="text-danger ms-1">*</span>
                            <ToolTip :message="__('message.build_file_hint') || 'Only needed if one of the selected products uses an encoded build.'" size="small" />
                        </label>
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

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <TextField name="main_version" :label="__('message.main_version') || 'Main Version'" :value="mainVersion"
                            :hint="__('message.main_version_hint') || 'Applied to every product below. A group\'s own version overrides this for that group; editing a product directly overrides both.'"
                            :onChange="onMainVersionChange" />
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-bold mb-0">
                        {{ __('message.products') }}
                        <span class="text-danger ms-1">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm" style="width: 220px;"
                        v-model="productSearch" :placeholder="__('message.search') || 'Search products...'" />
                </div>
                <small class="text-muted d-block mb-2">{{ selectedProductIds.length }} {{ __('message.selected') || 'selected' }}</small>
                <div v-if="errors.products" class="invalid-feedback d-block mb-2">{{ errors.products }}</div>

                <div v-if="loadingProducts" class="text-center py-4"><loader /></div>
                <div v-else-if="!groupedProducts.length" class="text-muted small">{{ __('message.no_records_found') || 'No products found.' }}</div>
                <div v-else class="masonry-container">
                    <div v-for="group in groupedProducts" :key="group.groupName" class="masonry-item">
                        <div class="card card-light mb-3">
                            <div class="card-body bg-light p-2">
                                <h6 class="border-bottom pb-2">
                                    <span class="text-uppercase"> {{ group.groupName }}</span>

                                    <span class="float-end fs-8 text-muted">
                                        <input class="all_check" type="checkbox"
                                            :checked="isGroupFullySelected(group)" @change="toggleGroup(group)">&nbsp;
                                        {{ __('message.select_all') || 'Select All' }}
                                    </span>
                                </h6>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <label class="mb-0 small text-muted flex-shrink-0">{{ __('message.group_version') || 'Version for all' }}</label>
                                    <input type="text" class="form-control form-control-sm" style="width: 120px;"
                                        :value="groupVersions[group.groupName] || ''"
                                        @input="onGroupVersionChange(group, $event.target.value)"
                                        :placeholder="__('message.version') || 'Version'">
                                </div>

                                <div v-for="p in group.products" :key="p.id" class="d-flex align-items-center gap-2 mb-2">
                                    <input class="form-check-input mt-0 flex-shrink-0" type="checkbox" :id="`product-${p.id}`"
                                        :checked="selectedProductIds.includes(p.id)" @change="toggleProduct(p.id)">
                                    <label class="form-check-label fw-normal flex-grow-1 mb-0" :for="`product-${p.id}`">
                                        {{ p.name }}
                                        <span v-if="p.build_type === 'obfuscated'" class="badge bg-warning-subtle text-warning-emphasis ms-1">{{ __('message.obfuscated') || 'Obfuscated / Encoded' }}</span>
                                        <span v-else-if="p.build_type === 'source'" class="badge bg-info-subtle text-info-emphasis ms-1">{{ __('message.source') || 'Source' }}</span>
                                    </label>
                                    <input type="text" class="form-control form-control-sm flex-shrink-0" style="width: 120px;"
                                        :disabled="!selectedProductIds.includes(p.id)"
                                        :placeholder="__('message.version') || 'Version'"
                                        v-model="productVersions[p.id]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <AppButton>
                    <button type="button" class="btn btn-primary" :disabled="saving || uploading || sourceUploading" @click="submit">
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import TextArea from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import UploadStatus from '@/components/Reusable/UploadStatus.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import { useChunkedFileUpload } from '@/core/composables/useChunkedFileUpload'
import { successHandler, applyServerValidation } from '@/helpers/responseHandler.js'

const router = useRouter()
const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()
// Independent instance — its own file/uploading/progress/error refs — for the
// obfuscated build. Only required if a selected product is build_type=obfuscated.
const {
    file: sourceFile, uploading: sourceUploading, uploadProgress: sourceUploadProgress,
    fileError: sourceFileError, uploadedName: sourceUploadedName, uploadedForFile: sourceUploadedForFile,
    onFile: onSourceFile,
} = useChunkedFileUpload()

const loadingProducts = ref(true)
const rawGroups = ref([]) // [{ groupName, products: [{id, name}] }] — pre-grouped + permission-filtered server-side
const productSearch = ref('')
const selectedProductIds = ref([])
const productVersions = ref({}) // { [productId]: version } — cascades main -> group -> individual, last one touched wins
const groupVersions = ref({}) // { [groupName]: version } — bulk-stamps every product in that group the moment it changes
const mainVersion = ref('') // bulk-stamps every product across every group the moment it changes

function onMainVersionChange(value) {
    mainVersion.value = value
    rawGroups.value.forEach(group => {
        group.products.forEach(p => {
            productVersions.value[p.id] = value
        })
    })
}

function onGroupVersionChange(group, value) {
    groupVersions.value[group.groupName] = value
    group.products.forEach(p => {
        productVersions.value[p.id] = value
    })
}

const productsById = computed(() => {
    const map = {}
    rawGroups.value.forEach(group => group.products.forEach(p => { map[p.id] = p }))
    return map
})

const selectedProducts = computed(() => selectedProductIds.value.map(id => productsById.value[id]).filter(Boolean))
// Source is the default file — required for anything selected that isn't
// explicitly opted into obfuscation. Obfuscated is only required when
// something obfuscated-tagged was actually selected.
const needsObfuscated = computed(() => selectedProducts.value.some(p => p.build_type === 'obfuscated'))
const needsSource = computed(() => selectedProducts.value.some(p => p.build_type !== 'obfuscated'))

const groupedProducts = computed(() => {
    const q = productSearch.value.trim().toLowerCase()
    if (!q) return rawGroups.value
    return rawGroups.value
        .map(group => {
            const groupMatches = group.groupName?.toLowerCase().includes(q)
            const products = groupMatches ? group.products : group.products.filter(p => p.name?.toLowerCase().includes(q))
            return { ...group, products }
        })
        .filter(group => group.products.length)
})

onMounted(async () => {
    try {
        const res = await http.get('/dependency/products', { params: { permission: 'downloadPermission' } })
        const groups = res.data?.data?.products ?? []
        rawGroups.value = groups.map(g => ({ groupName: g.name, products: g.children ?? [] }))
        if (mainVersion.value) onMainVersionChange(mainVersion.value)
    } finally {
        loadingProducts.value = false
    }
})

function toggleProduct(id) {
    const idx = selectedProductIds.value.indexOf(id)
    if (idx === -1) {
        selectedProductIds.value.push(id)
    } else {
        selectedProductIds.value.splice(idx, 1)
    }
    setFieldError('products', undefined)
}

function isGroupFullySelected(group) {
    return group.products.length > 0 && group.products.every(p => selectedProductIds.value.includes(p.id))
}

function toggleGroup(group) {
    const groupIds = group.products.map(p => p.id)
    if (isGroupFullySelected(group)) {
        selectedProductIds.value = selectedProductIds.value.filter(id => !groupIds.includes(id))
    } else {
        selectedProductIds.value = [...new Set([...selectedProductIds.value, ...groupIds])]
    }
    setFieldError('products', undefined)
}

const form = ref({
    description: '', release_type: 'official',
    is_private: false, is_restricted: false, dependencies: '[]',
})

const releaseTypes = [
    { name: __('message.official') || 'Official', value: 'official' },
    { name: __('message.pre_release') || 'Pre Release', value: 'pre_release' },
    { name: __('message.beta') || 'Beta', value: 'beta' },
]
const selectedReleaseType = computed(() => releaseTypes.find(r => r.value === form.value.release_type) ?? releaseTypes[0])

function parseDependencies() {
    const raw = (form.value.dependencies || '').trim() || '[]'
    try {
        const data = JSON.parse(raw)

        return Array.isArray(data) ? { data } : { error: 'invalid_json' }
    } catch {
        return { error: 'invalid_json' }
    }
}

// Each box's own upload has an independent upload step (useChunkedFileUpload)
// — this checks it actually succeeded, requiring one only if `required` is
// true (which depends on what's currently selected — see needsObfuscated /
// needsSource).
function validateSlot(required, { file, uploading, uploadedForFile, fileError }) {
    if (required && !file.value) {
        fileError.value = __('message.file')
    } else if (file.value && uploading.value) {
        fileError.value = __('message.please_wait')
    } else if (file.value && uploadedForFile.value !== file.value && !fileError.value) {
        fileError.value = __('message.something_wrong')
    }
}

async function submit() {
    validateSlot(needsSource.value, { file: sourceFile, uploading: sourceUploading, uploadedForFile: sourceUploadedForFile, fileError: sourceFileError })
    validateSlot(needsObfuscated.value, { file, uploading, uploadedForFile, fileError })

    const errs = {}
    if (!selectedProductIds.value.length) {
        errs.products = __('message.select-a-row') || 'Select at least one product.'
    } else if (selectedProductIds.value.some(id => !productVersions.value[id]?.trim())) {
        errs.products = __('message.version_required_per_product') || 'Every selected product needs a version.'
    }
    if (!form.value.description || form.value.description.replace(/<[^>]*>/g, '').trim() === '') {
        errs.description = __('message.description')
    }
    const deps = parseDependencies()
    if (deps.error) errs.dependencies = __('message.enter_json_format') || 'Enter valid JSON format.'
    setErrors(errs)
    if (Object.keys(errs).length || fileError.value || sourceFileError.value) return

    saving.value = true
    try {
        // Fan the already-uploaded build(s) out — each selected product with
        // its own version. Source is the default file; a product only uses
        // the Obfuscated one instead if it's explicitly tagged build_type=
        // obfuscated. Which bundled plugins each product's build keeps is
        // resolved automatically on the backend from the product's own
        // Plugins-tab configuration.
        const res = await http.put(`/product/upload-build/apply`, {
            filename: uploadedName.value || null,
            filename_source: sourceUploadedName.value || null,
            description: form.value.description,
            release_type: form.value.release_type,
            is_private: form.value.is_private,
            is_restricted: form.value.is_restricted,
            dependencies: deps.data,
            products: selectedProductIds.value.map(id => ({ id, version: productVersions.value[id] })),
        })

        // Alert is tagged for the destination page (products-index), not this one,
        // since we navigate away immediately on success — mirrors ProductVersionCreate.vue.
        successHandler(res, 'products-index')
        router.push('/products')
    } catch (err) {
        applyServerValidation(err, {
            setErrors,
            fields: ['description', 'dependencies', 'products'],
            component: 'product-build-apply',
        })
    } finally {
        saving.value = false
    }
}
</script>

<style scoped>
.masonry-container {
    column-count: 2;
    column-gap: 1rem;
}

.masonry-item {
    break-inside: avoid;
    margin-bottom: 1rem;
}

.all_check {
    width: 13px;
    height: 13px;
    vertical-align: bottom;
    position: relative;
    top: -1px;
    overflow: hidden;
}
</style>
