<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_user') }}</h4>
            </div>

            <div class="card-body">
                <!-- Row 1: First Name / Last Name / Email / User Name -->
                <div class="row">
                    <div class="col-md-3">
                        <TextField name="first_name" :label="__('message.first_name')" :value="form.first_name" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <TextField name="last_name" :label="__('message.last_name')" :value="form.last_name" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <TextField name="email" :label="__('message.email')" type="email" :value="form.email" :onChange="onChange" />
                    </div>
                    <div class="col-md-3">
                        <TextField name="user_name" :label="__('message.user_name')" :value="form.user_name" :onChange="onChange" />
                    </div>
                </div>

                <!-- Row 2: Company / Industry / Email Status / Mobile Status -->
                <div class="row">
                    <div class="col-md-3">
                        <TextField name="company" :label="__('message.company')" :value="form.company" :onChange="onChange" />
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('message.email_status') }}</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="1" v-model="form.active" id="emailActive" />
                                    <label class="form-check-label" for="emailActive">{{ __('message.active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="0" v-model="form.active" id="emailInactive" />
                                    <label class="form-check-label" for="emailInactive">{{ __('message.inactive') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('message.mobile_status') }}</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="1" v-model="form.mobile_verified" id="mobileActive" />
                                    <label class="form-check-label" for="mobileActive">{{ __('message.active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="0" v-model="form.mobile_verified" id="mobileInactive" />
                                    <label class="form-check-label" for="mobileInactive">{{ __('message.inactive') }}</label>
                                </div>
                            </div>
                        </div>
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

                <!-- Address: full width -->
                <div class="row">
                    <div class="col-md-12">
                        <TextField name="address" :label="__('message.address')" :value="form.address" :onChange="onChange" />
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
                            :apiEndpoint="`${baseUrl}/dependency/countries`"
                            dataKey="countries"
                            :value="form.country"
                            :onChange="onCountryChange"
                            :placeholder="__('message.choose')"
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
                            :apiEndpoint="`${baseUrl}/dependency/time-zones`"
                            dataKey="time_zones"
                            :value="form.timezone_id"
                            :onChange="onChange"
                            :placeholder="__('message.choose')"
                        />
                    </div>
                    <div class="col-md-3">
                        <PhoneField
                            name="mobile"
                            :label="__('message.mobile')"
                            :value="form.mobile"
                            :onChange="onChange"
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
                <action-button action="save" :loading="saving" @click="submit" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js';

const COMPONENT = 'users-create'

const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router  = useRouter()
const saving  = ref(false)

const form = reactive({
    first_name:      '',
    last_name:       '',
    email:           '',
    user_name:       '',
    company:         '',
    bussiness:       null,
    active:          1,
    mobile_verified: 0,
    role:            null,
    position:        null,
    company_type:    null,
    company_size:    null,
    address:         '',
    town:            '',
    country:         null,
    state:           null,
    zip:             '',
    timezone_id:     null,
    mobile:              '',
    mobile_country_iso:  '',
    mobile_code:         '',
    skype:               '',
    manager:             null,
    account_manager:     null,
})

const stateParams = computed(() => ({ country: form.country?.code ?? '' }))

const roleOptions = computed(() => [
    { id: 'user',  name: __('message.user') },
    { id: 'admin', name: 'Admin' },
])

const positionOptions = computed(() => [
    { id: 'account_manager', name: __('message.account_manager') },
    { id: 'manager',         name: __('message.sales_manager') },
])

const companyTypeOptions = computed(() => [
    { id: 'public-company',  name: __('message.public_company') },
    { id: 'self-employed',   name: __('message.self_employed') },
    { id: 'non-profit',      name: __('message.non_profit') },
    { id: 'privately-held',  name: __('message.privately_held') },
    { id: 'partnership',     name: __('message.partnership') },
])

const companySizeOptions = computed(() => [
    { id: 'Myself-only', name: __('message.myself_only') },
    { id: '2-10',        name: '2-10' },
    { id: '11-50',       name: '11-50' },
    { id: '51-200',      name: '51-200' },
    { id: '201-500',     name: '201-500' },
    { id: '501-1000',    name: '501-1000' },
    { id: '1001-5000',   name: '1001-5000' },
    { id: '5001-10000',  name: '5001-10000' },
    { id: '10001',       name: '10001+' },
])

function onChange(val, name) {
    form[name] = val
}

function onCountryChange(val) {
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

function extractId(val) {
    if (val === null || val === undefined) return null
    return typeof val === 'object' ? val.id : val
}

async function submit() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/users`, {
            first_name:       form.first_name,
            last_name:        form.last_name,
            email:            form.email,
            user_name:        form.user_name,
            company:          form.company,
            bussiness:        extractId(form.bussiness),
            active:           form.active,
            mobile_verified:  form.mobile_verified,
            role:             extractId(form.role),
            position:         extractId(form.position),
            company_type:     extractId(form.company_type),
            company_size:     extractId(form.company_size),
            address:          form.address,
            town:             form.town,
            country:            form.country?.code ?? null,
            mobile_country_iso: form.mobile_country_iso || form.country?.code || null,
            mobile_code:        form.mobile_code || null,
            state:            extractId(form.state),
            zip:              form.zip,
            timezone_id:      extractId(form.timezone_id),
            mobile:           form.mobile,
            skype:            form.skype,
            manager:          extractId(form.manager),
            account_manager:  extractId(form.account_manager),
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/users'), 1500)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
