<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ isEdit ? __('message.edit_user') : __('message.create_user') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <!-- Row 1: First Name / Last Name / Email / User Name -->
                    <div class="row">
                        <div class="col-md-3">
                            <TextField name="first_name" :label="__('message.first_name')" :required="true" :value="form.first_name" :onChange="onChange" :error="errors.first_name" />
                        </div>
                        <div class="col-md-3">
                            <TextField name="last_name" :label="__('message.last_name')" :required="true" :value="form.last_name" :onChange="onChange" :error="errors.last_name" />
                        </div>
                        <div class="col-md-3">
                            <TextField name="email" :label="__('message.email')" :required="true" type="email" :value="form.email" :onChange="onChange" :error="errors.email" />
                        </div>
                        <div class="col-md-3">
                            <TextField name="user_name" :label="__('message.user_name')" :value="form.user_name" :onChange="onChange" />
                        </div>
                    </div>

                    <!-- Row 2: Company / Industry / Email Status / Mobile Status -->
                    <div class="row">
                        <div class="col-md-3">
                            <TextField name="company" :label="__('message.company')" :required="true" :value="form.company" :onChange="onChange" :error="errors.company" />
                        </div>
                        <div class="col-md-3">
                            <DynamicSelect
                                name="bussiness"
                                :label="__('message.industry')"
                                :apiEndpoint="`${baseUrl}/dependency/industries`"
                                dataKey="industries"
                                :value="form.bussiness"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-3">
                            <RadioButton
                                name="active"
                                :label="__('message.email_status')"
                                :options="[{ name: __('message.active'), value: 1 }, { name: __('message.inactive'), value: 0 }]"
                                :value="form.active"
                                :onChange="(val) => form.active = val"
                            />
                        </div>
                        <div class="col-md-3">
                            <RadioButton
                                name="mobile_verified"
                                :label="__('message.mobile_status')"
                                :options="[{ name: __('message.active'), value: 1 }, { name: __('message.inactive'), value: 0 }]"
                                :value="form.mobile_verified"
                                :onChange="(val) => form.mobile_verified = val"
                            />
                        </div>
                    </div>

                    <!-- Row 3: Role / Position / Company Type / Company Size -->
                    <div class="row">
                        <div class="col-md-3">
                            <SelectField
                                name="role"
                                :label="__('message.role')"
                                :elements="roleOptions"
                                :value="form.role"
                                :onChange="onRoleChange"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-3">
                            <SelectField
                                name="position"
                                :label="__('message.position')"
                                :elements="positionOptions"
                                :value="form.position"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-3">
                            <SelectField
                                name="company_type"
                                :label="__('message.company_type')"
                                :elements="companyTypeOptions"
                                :value="form.company_type"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                        <div class="col-md-3">
                            <SelectField
                                name="company_size"
                                :label="__('message.company_size')"
                                :elements="companySizeOptions"
                                :value="form.company_size"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="row">
                        <div class="col-md-12">
                            <TextField name="address" :label="__('message.address')" :required="true" :value="form.address" :onChange="onChange" :error="errors.address" />
                        </div>
                    </div>

                    <!-- Row 4: Town / Country / State / Zip -->
                    <div class="row">
                        <div class="col-md-3">
                            <TextField name="town" :label="__('message.town')" :value="form.town" :onChange="onChange" />
                        </div>
                        <div class="col-md-3">
                            <DynamicSelect
                                name="country"
                                :label="__('message.country')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/countries`"
                                dataKey="countries"
                                :value="form.country"
                                :onChange="onCountryChange"
                                :placeholder="__('message.choose')"
                                :error="errors.country"
                            />
                        </div>
                        <div class="col-md-3">
                            <DynamicSelect
                                name="state"
                                :label="__('message.state')"
                                :apiEndpoint="form.country ? `${baseUrl}/dependency/states` : null"
                                :apiParams="stateParams"
                                dataKey="states"
                                :value="form.state"
                                :onChange="onChange"
                                :placeholder="__('message.choose_country_first')"
                            />
                        </div>
                        <div class="col-md-3">
                            <TextField name="zip" :label="__('message.zip')" :value="form.zip" :onChange="onChange" />
                        </div>
                    </div>

                    <!-- Row 5: Timezone / Mobile / Skype / Sales Manager -->
                    <div class="row">
                        <div class="col-md-3">
                            <DynamicSelect
                                name="timezone_id"
                                :label="__('message.timezone')"
                                :required="true"
                                :apiEndpoint="`${baseUrl}/dependency/time-zones`"
                                dataKey="time_zones"
                                :value="form.timezone_id"
                                :onChange="onChange"
                                :placeholder="__('message.choose')"
                                :error="errors.timezone_id"
                            />
                        </div>
                        <div class="col-md-3">
                            <PhoneField
                                name="mobile"
                                :label="__('message.mobile')"
                                :value="form.mobile"
                                :initialCountry="form.mobile_country_iso"
                                :onChange="onChange"
                                :error="errors.mobile"
                                @countryChange="onMobileCountryChange"
                            />
                        </div>
                        <div class="col-md-3">
                            <TextField name="skype" :label="__('message.skype')" :value="form.skype" :onChange="onChange" />
                        </div>
                        <div class="col-md-3">
                            <DynamicSelect
                                name="manager"
                                :label="__('message.sales_manager')"
                                :apiEndpoint="`${baseUrl}/dependency/managers`"
                                :apiParams="{ role: 'manager' }"
                                dataKey="managers"
                                optionLabel="name"
                                :value="form.manager"
                                :onChange="onChange"
                            >
                                <template #option="option">
                                    {{ option.name }} &lt;{{ option.email }}&gt;
                                </template>
                            </DynamicSelect>
                        </div>
                    </div>

                    <!-- Row 6: Account Manager -->
                    <div class="row">
                        <div class="col-md-3">
                            <DynamicSelect
                                name="account_manager"
                                :label="__('message.account_manager')"
                                :apiEndpoint="`${baseUrl}/dependency/managers`"
                                :apiParams="{ role: 'account_manager' }"
                                dataKey="managers"
                                optionLabel="name"
                                :value="form.account_manager"
                                :onChange="onChange"
                            >
                                <template #option="option">
                                    {{ option.name }} &lt;{{ option.email }}&gt;
                                </template>
                            </DynamicSelect>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button :action="isEdit ? 'update' : 'save'" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm, extractId } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { userCreateSchema, userEditSchema } from '@/validations/admin/userValidations'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'

const baseUrl = useBaseUrl()
const router  = useRouter()
const route   = useRoute()
const userId  = route.params.id
const isEdit  = !!userId

const COMPONENT = isEdit ? 'users-edit' : 'users-create'

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(isEdit)
const saving  = ref(false)

const roleOptions = [
    { id: 'user',  name: __('message.user') },
    { id: 'admin', name: __('message.admin') },
]

const positionOptions = [
    { id: 'account_manager', name: __('message.account_manager') },
    { id: 'manager',         name: __('message.sales_manager') },
]

const companyTypeOptions = [
    { id: 'public-company',  name: __('message.public_company') },
    { id: 'self-employed',   name: __('message.self_employed') },
    { id: 'non-profit',      name: __('message.non_profit') },
    { id: 'privately-held',  name: __('message.privately_held') },
    { id: 'partnership',     name: __('message.partnership') },
]

const companySizeOptions = [
    { id: 'Myself-only', name: __('message.myself_only') },
    { id: '2-10',        name: '2-10' },
    { id: '11-50',       name: '11-50' },
    { id: '51-200',      name: '51-200' },
    { id: '201-500',     name: '201-500' },
    { id: '501-1000',    name: '501-1000' },
    { id: '1001-5000',   name: '1001-5000' },
    { id: '5001-10000',  name: '5001-10000' },
    { id: '10001',       name: '10001+' },
]

const form = reactive({
    first_name:         '',
    last_name:          '',
    email:              '',
    user_name:          '',
    company:            '',
    bussiness:          null,
    active:             1,
    mobile_verified:    0,
    role:               null,
    position:           null,
    company_type:       null,
    company_size:       null,
    address:            '',
    town:               '',
    country:            null,
    state:              null,
    zip:                '',
    timezone_id:        null,
    mobile:             '',
    mobile_country_iso: '',
    mobile_code:        '',
    skype:              '',
    manager:            null,
    account_manager:    null,
})

const stateParams = computed(() => ({ country: form.country?.code ?? '' }))

onMounted(async () => {
    if (!isEdit) return
    try {
        const res = await http.get(`/user/${userId}`)
        const u   = res.data?.data ?? res.data

        form.first_name         = u.first_name         ?? ''
        form.last_name          = u.last_name          ?? ''
        form.email              = u.email              ?? ''
        form.user_name          = u.user_name          ?? ''
        form.company            = u.company            ?? ''
        form.bussiness          = u.bussiness          ?? null
        form.active             = u.active             ?? 1
        form.mobile_verified    = u.mobile_verified    ?? 0
        form.role               = roleOptions.find(o => o.id === u.role)          ?? null
        form.position           = positionOptions.find(o => o.id === u.position)  ?? null
        form.company_type       = companyTypeOptions.find(o => o.id === u.company_type) ?? null
        form.company_size       = companySizeOptions.find(o => o.id === u.company_size) ?? null
        form.address            = u.address            ?? ''
        form.town               = u.town               ?? ''
        form.country            = u.country            ?? null
        form.state              = u.state              ?? null
        form.zip                = u.zip                ?? ''
        form.timezone_id        = u.timezone_id        ?? null
        form.mobile             = u.mobile             ?? ''
        form.mobile_code        = u.mobile_code        ?? ''
        form.mobile_country_iso = u.mobile_country_iso ?? ''
        form.skype              = u.skype              ?? ''
        form.manager            = u.manager            ?? null
        form.account_manager    = u.account_manager    ?? null
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function onChange(val, name) {
    setFieldError(name, undefined)
    form[name] = val
}

function onCountryChange(val) {
    setFieldError('country', undefined)
    form.country = val
    form.state   = null
}

function onMobileCountryChange({ iso, dialCode }) {
    form.mobile_country_iso = iso
    form.mobile_code        = dialCode
}

function onRoleChange(val) {
    form.role     = val
    form.position = null
}

async function submit() {
    const schema = isEdit ? userEditSchema : userCreateSchema
    if (!await validateForm(schema, form, setErrors)) return

    saving.value = true
    try {
        const payload = {
            first_name:         form.first_name,
            last_name:          form.last_name,
            email:              form.email,
            user_name:          form.user_name,
            company:            form.company,
            bussiness:          extractId(form.bussiness),
            active:             form.active,
            mobile_verified:    form.mobile_verified,
            role:               extractId(form.role),
            position:           extractId(form.position),
            company_type:       extractId(form.company_type),
            company_size:       extractId(form.company_size),
            address:            form.address,
            town:               form.town,
            country:            form.country?.code ?? null,
            mobile_country_iso: form.mobile_country_iso || form.country?.code || null,
            mobile_code:        form.mobile_code || null,
            state:              extractId(form.state),
            zip:                form.zip,
            timezone_id:        extractId(form.timezone_id),
            mobile:             form.mobile,
            skype:              form.skype,
            manager:            extractId(form.manager),
            account_manager:    extractId(form.account_manager),
        }

        const res = isEdit
            ? await http.patch(`/user/${userId}`, payload)
            : await http.put(`/users`, payload)

        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/users'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
