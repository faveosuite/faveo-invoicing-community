<template>
    <div>
        <AppAlert componentName="product-build-apply" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.apply_build_to_products') || 'Apply Build to Products' }}</h4>
            </div>

            <div class="card-body">
                <p class="text-muted">
                    {{ __('message.apply_build_hint') }}
                </p>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <TextField name="default_version" :label="__('message.default_version') || 'Default Version'" :value="defaultVersion"
                            :hint="__('message.default_version_hint') || 'Pre-fills the version when you check a product below — each one stays independently editable.'"
                            :onChange="(v) => defaultVersion = v" />
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
                <div v-else class="border rounded p-3" style="max-height: 480px; overflow-y: auto;">
                    <div v-for="(group, index) in groupedProducts" :key="group.groupName" :class="{ 'mt-3': index > 0 }">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <input class="form-check-input mt-0" type="checkbox"
                                :checked="isGroupFullySelected(group)" @change="toggleGroup(group)" />
                            <strong>{{ group.groupName }}</strong>
                            <span class="text-muted small">({{ countSelectedInGroup(group) }}/{{ group.products.length }})</span>
                        </div>

                        <div v-for="p in group.products" :key="p.id" class="d-flex align-items-center gap-2 mb-1 ms-4">
                            <input class="form-check-input mt-0" type="checkbox" :id="`product-${p.id}`"
                                :checked="selectedProductIds.includes(p.id)" @change="toggleProduct(p.id)" />
                            <label class="mb-0 flex-grow-1" :for="`product-${p.id}`">{{ p.name }}</label>
                            <input type="text" class="form-control form-control-sm" style="width: 140px;"
                                :disabled="!selectedProductIds.includes(p.id)"
                                :placeholder="__('message.version') || 'Version'"
                                v-model="productVersions[p.id]" />
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <SelectField name="release_type" :label="__('message.release_type')" :required="true"
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import TextArea from '@/components/Reusable/FormField/TextField.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import UploadStatus from '@/components/Reusable/UploadStatus.vue'
import { useChunkedFileUpload } from '@/core/composables/useChunkedFileUpload'
import { successHandler, applyServerValidation } from '@/helpers/responseHandler.js'

const router = useRouter()
const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)
const { file, uploading, uploadProgress, fileError, uploadedName, uploadedForFile, onFile } = useChunkedFileUpload()

const loadingProducts = ref(true)
const products = ref([])
const productSearch = ref('')
const selectedProductIds = ref([])
const productVersions = ref({}) // { [productId]: version } — independently editable per row
const defaultVersion = ref('') // pre-fills a product's version the moment it's checked, not sent as-is

const filteredProducts = computed(() => {
    const q = productSearch.value.trim().toLowerCase()
    if (!q) return products.value
    return products.value.filter(p => p.name?.toLowerCase().includes(q) || p.group?.toLowerCase().includes(q))
})

const groupedProducts = computed(() => {
    const groups = new Map()
    filteredProducts.value.forEach(p => {
        const groupName = p.group || 'Ungrouped'
        if (!groups.has(groupName)) groups.set(groupName, [])
        groups.get(groupName).push(p)
    })
    return Array.from(groups, ([groupName, groupProducts]) => ({ groupName, products: groupProducts }))
})

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

onMounted(async () => {
    try {
        const res = await http.get('/products', { params: { limit: 500 } })
        products.value = res.data?.data?.data ?? []
    } finally {
        loadingProducts.value = false
    }
})

function toggleProduct(id) {
    const idx = selectedProductIds.value.indexOf(id)
    if (idx === -1) {
        selectedProductIds.value.push(id)
        if (!productVersions.value[id]) productVersions.value[id] = defaultVersion.value
    } else {
        selectedProductIds.value.splice(idx, 1)
    }
    setFieldError('products', undefined)
}

function isGroupFullySelected(group) {
    return group.products.length > 0 && group.products.every(p => selectedProductIds.value.includes(p.id))
}

function countSelectedInGroup(group) {
    return group.products.filter(p => selectedProductIds.value.includes(p.id)).length
}

function toggleGroup(group) {
    const groupIds = group.products.map(p => p.id)
    if (isGroupFullySelected(group)) {
        selectedProductIds.value = selectedProductIds.value.filter(id => !groupIds.includes(id))
    } else {
        selectedProductIds.value = [...new Set([...selectedProductIds.value, ...groupIds])]
        groupIds.forEach(id => {
            if (!productVersions.value[id]) productVersions.value[id] = defaultVersion.value
        })
    }
    setFieldError('products', undefined)
}

function parseDependencies() {
    const raw = (form.value.dependencies || '').trim() || '[]'
    try {
        const data = JSON.parse(raw)

        return Array.isArray(data) ? { data } : { error: 'invalid_json' }
    } catch {
        return { error: 'invalid_json' }
    }
}

async function submit() {
    // The file has its own independent upload step (useChunkedFileUpload) —
    // this just checks it actually succeeded for the currently-picked file.
    if (!file.value) {
        fileError.value = __('message.file')
    } else if (uploading.value) {
        fileError.value = __('message.please_wait')
    } else if (uploadedForFile.value !== file.value && !fileError.value) {
        fileError.value = __('message.something_wrong')
    }

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
    if (Object.keys(errs).length || fileError.value) return

    saving.value = true
    try {
        // Fan the already-uploaded build out — each selected product with its
        // own version. Which bundled plugins each product's build keeps is
        // resolved automatically on the backend from the product's own
        // Plugins-tab configuration.
        const res = await http.put(`/product/upload-build/apply`, {
            filename: uploadedName.value,
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
