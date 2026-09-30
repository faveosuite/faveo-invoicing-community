<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">{{ __('message.filter') }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <TextField
                        name="company"
                        :label="__('message.company')"
                        :value="form.company"
                        :onChange="(val) => form.company = val"
                        :placehold="__('message.search_by_company')"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="country"
                        :label="__('message.country')"
                        :apiEndpoint="`${baseUrl}/dependency/countries`"
                        dataKey="countries"
                        :value="form.country"
                        :onChange="(val) => form.country = val"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="industry"
                        :label="__('message.industry')"
                        :apiEndpoint="`${baseUrl}/dependency/industries`"
                        dataKey="industries"
                        :value="form.industry"
                        :onChange="(val) => form.industry = val"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="role"
                        :label="__('message.role')"
                        :elements="roleOptions"
                        :value="form.role"
                        :onChange="(val) => form.role = val"
                        :searchable="true"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="position"
                        :label="__('message.position')"
                        :elements="positionOptions"
                        :value="form.position"
                        :onChange="(val) => form.position = val"
                        :searchable="true"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="reg_from"
                        :label="__('message.registered_from')"
                        :value="form.reg_from"
                        :onChange="(val) => form.reg_from = val"
                        :placeholder="__('message.select_date')"                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DatePicker
                        name="reg_till"
                        :label="__('message.registered_till')"
                        :value="form.reg_till"
                        :onChange="(val) => form.reg_till = val"
                        :placeholder="__('message.select_date')"                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="actmanager"
                        :label="__('message.users_account_manager')"
                        :apiEndpoint="`${baseUrl}/dependency/managers`"
                        :apiParams="{ role: 'account_manager' }"
                        dataKey="managers"
                        optionLabel="name"
                        :value="form.actmanager"
                        :onChange="(val) => form.actmanager = val"
                    >
                        <template #option="option">
                            {{ option.name }} &lt;{{ option.email }}&gt;
                        </template>
                    </DynamicSelect>
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="salesmanager"
                        :label="__('message.user_for_sales_manager')"
                        :apiEndpoint="`${baseUrl}/dependency/managers`"
                        :apiParams="{ role: 'manager' }"
                        dataKey="managers"
                        optionLabel="name"
                        :value="form.salesmanager"
                        :onChange="(val) => form.salesmanager = val"
                    >
                        <template #option="option">
                            {{ option.name }} &lt;{{ option.email }}&gt;
                        </template>
                    </DynamicSelect>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="mobile_verified"
                        :label="__('message.mobile_status')"
                        :elements="verifyOptions"
                        :value="form.mobile_verified"
                        :onChange="(val) => form.mobile_verified = val"
                        :searchable="true"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="email_verified"
                        :label="__('message.email_status')"
                        :elements="verifyOptions"
                        :value="form.email_verified"
                        :onChange="(val) => form.email_verified = val"
                        :searchable="true"
                    />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="is_2fa_enabled"
                        :label="__('message.2fa_status')"
                        :elements="twoFAOptions"
                        :value="form.is_2fa_enabled"
                        :onChange="(val) => form.is_2fa_enabled = val"
                        :searchable="true"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <action-button action="apply" type="button" @click="apply" />
            <action-button action="reset" type="button" @click="reset" />
            <action-button action="cancel" type="button" @click="$emit('close')" />
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from 'vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const props = defineProps({
    show:          { type: Boolean, default: false },
    baseUrl:       { type: String, default: '' },
    initialValues: { type: Object, default: () => ({}) },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const roleOptions = [
    { id: 'user',  name: __('message.user') },
    { id: 'admin', name: __('message.admin') },
]

const positionOptions = [
    { id: 'account_manager', name: __('message.account_manager') },
    { id: 'manager',         name: __('message.sales_manager') },
]

const verifyOptions = [
    { id: '1', name: __('message.verified') },
    { id: '0', name: __('message.not_verified') },
]

const twoFAOptions = [
    { id: '1', name: __('message.enabled') },
    { id: '0', name: __('message.disabled') },
]

const empty = () => ({
    company: '', country: null, role: null, position: null,
    reg_from: null, reg_till: null,
    actmanager: null, salesmanager: null,
    mobile_verified: null, email_verified: null, is_2fa_enabled: null,
    industry: null,
})

function resolveForm(values) {
    return {
        ...empty(),
        company:         values.company ?? '',
        reg_from:        values.reg_from ?? null,
        reg_till:        values.reg_till ?? null,
        role:            roleOptions.find(o => o.id === values.role)          ?? null,
        position:        positionOptions.find(o => o.id === values.position)  ?? null,
        mobile_verified: verifyOptions.find(o => o.id === values.mobile_verified) ?? null,
        email_verified:  verifyOptions.find(o => o.id === values.email_verified)  ?? null,
        is_2fa_enabled:  twoFAOptions.find(o => o.id === values.is_2fa_enabled)   ?? null,
    }
}

const form = reactive(resolveForm(props.initialValues))

// The route can navigate /users -> /users?... without remounting this
// component (RouterView isn't keyed on the route), so the form built once
// above goes stale on a second "View all" visit unless we re-map it here.
watch(() => props.initialValues, (values) => {
    Object.assign(form, resolveForm(values))
})

function apply() {
    const params = {}
    Object.entries(form).forEach(([k, v]) => {
        if (v !== '' && v !== null) {
            params[k] = typeof v === 'object' ? (k === 'country' ? v.code : v.id) : v
        }
    })
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
