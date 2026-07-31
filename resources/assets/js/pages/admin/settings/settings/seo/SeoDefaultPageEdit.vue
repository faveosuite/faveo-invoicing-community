<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_seo') }} ({{ pageLabel }})</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <SeoFieldsCard
                        :bare="true"
                        :form="form"
                        :errors="errors"
                        :onChange="onChange"
                        :ogSameAsMeta="ogSameAsMeta"
                        @update:ogSameAsMeta="ogSameAsMeta = $event"
                        :ogImagePreview="ogImagePreview"
                        :componentName="COMPONENT"
                        @image-change="onImageChange"
                    />
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import { seoDefaultPageSchema } from '@/validations/admin/seoValidations'
import SeoFieldsCard from '@/components/Reusable/FormField/SeoFieldsCard.vue'

const COMPONENT = 'seo-default-page-edit'
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)

const ogImagePreview = ref('')
const selectedOgImage = ref(null)
const selectedOgImageName = ref('')
const ogSameAsMeta = ref(false)

function onImageChange(value) {
    ogImagePreview.value = value.image
    selectedOgImage.value = value.file
    selectedOgImageName.value = value.name
}

const form = reactive({
    meta_title: '',
    meta_description: '',
    og_title: '',
    og_description: '',
})

const labels = {
    login: __('message.seo_login_and_register'),
    forgot_password: __('message.forgot-password'),
    reset_password: __('message.reset_password'),
    cart: __('message.shopping_cart'),
}

const pageLabel = computed(() => labels[route.params.pageKey] ?? route.params.pageKey)

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`/seo/default-pages/${route.params.pageKey}`)
        const row = res.data?.data ?? res.data

        form.meta_title = row.meta_title ?? ''
        form.meta_description = row.meta_description ?? ''
        form.og_title = row.og_title ?? ''
        form.og_description = row.og_description ?? ''
        ogImagePreview.value = row.og_image ?? ''
        ogSameAsMeta.value = Boolean(row.og_same_as_meta)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    if (!await validateForm(seoDefaultPageSchema, form, setErrors)) return

    saving.value = true
    try {
        const fd = new FormData()
        fd.append('meta_title', form.meta_title ?? '')
        fd.append('meta_description', form.meta_description ?? '')
        fd.append('og_title', form.og_title ?? '')
        fd.append('og_description', form.og_description ?? '')
        fd.append('og_same_as_meta', ogSameAsMeta.value ? 1 : 0)
        if (selectedOgImage.value) {
            fd.append('og_image', selectedOgImage.value, selectedOgImageName.value)
        }
        fd.append('_method', 'PATCH')

        const res = await http.post(`/seo/default-pages/${route.params.pageKey}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/settings/seo/pages'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
