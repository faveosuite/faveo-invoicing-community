<template>
    <div v-if="show" class="card card-light mb-3">
        <div class="card-header">
            <h4 class="card-title">Filter</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Company Name</label>
                    <input type="text" class="form-control mb-3" v-model="form.company" placeholder="Search by company" />
                </div>
                <div class="col-md-4">
                    <DynamicSelect
                        name="country"
                        label="Country"
                        :apiEndpoint="`${baseUrl}/dependency/countries`"
                        dataKey="countries"
                        :value="form.country"
                        :onChange="(val) => form.country = val"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="role"
                        label="Role"
                        :elements="roleOptions"
                        :value="form.role"
                        :onChange="(val) => form.role = val"
                        placeholder="Select"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="position"
                        label="Position"
                        :elements="positionOptions"
                        :value="form.position"
                        :onChange="(val) => form.position = val"
                        placeholder="Select"
                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="reg_from"
                        label="Registered From"
                        :value="form.reg_from"
                        :onChange="(val) => form.reg_from = val"
                        placeholder="Select date"                    />
                </div>
                <div class="col-md-4">
                    <DatePicker
                        name="reg_till"
                        label="Registered Till"
                        :value="form.reg_till"
                        :onChange="(val) => form.reg_till = val"
                        placeholder="Select date"                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <DynamicSelect
                        name="actmanager"
                        label="Users for Account Manager"
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
                        label="Users for Sales Manager"
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
                <div class="col-md-4">
                    <SelectField
                        name="mobile_verified"
                        label="Mobile Status"
                        :elements="verifyOptions"
                        :value="form.mobile_verified"
                        :onChange="(val) => form.mobile_verified = val"
                        placeholder="Select"
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <SelectField
                        name="email_verified"
                        label="Email Status"
                        :elements="verifyOptions"
                        :value="form.email_verified"
                        :onChange="(val) => form.email_verified = val"
                        placeholder="Select"
                    />
                </div>
                <div class="col-md-4">
                    <SelectField
                        name="is_2fa_enabled"
                        label="2FA Status"
                        :elements="twoFAOptions"
                        :value="form.is_2fa_enabled"
                        :onChange="(val) => form.is_2fa_enabled = val"
                        placeholder="Select"
                    />
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="button" @click="apply">
                <i class="fas fa-check"></i>&nbsp; Apply
            </button>
            <button class="btn btn-primary" type="button" @click="reset">
                <i class="fas fa-rotate-left"></i>&nbsp; Reset
            </button>
            <button class="btn btn-secondary" type="button" @click="$emit('close')">
                <i class="fas fa-xmark"></i>&nbsp; Cancel
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
    show:    { type: Boolean, default: false },
    baseUrl: { type: String, default: '' },
})

const emit = defineEmits(['apply', 'reset', 'close'])

const roleOptions = [
    { id: 'user',  name: 'User' },
    { id: 'admin', name: 'Admin' },
]

const positionOptions = [
    { id: 'account_manager', name: 'Account Manager' },
    { id: 'manager',         name: 'Sales Manager' },
]

const verifyOptions = [
    { id: '1', name: 'Verified' },
    { id: '0', name: 'Not Verified' },
]

const twoFAOptions = [
    { id: '1', name: 'Enabled' },
    { id: '0', name: 'Disabled' },
]

const empty = () => ({
    company: '', country: null, role: null, position: null,
    reg_from: null, reg_till: null,
    actmanager: null, salesmanager: null,
    mobile_verified: null, email_verified: null, is_2fa_enabled: null,
})

const form = reactive(empty())

function apply() {
    const params = {}
    Object.entries(form).forEach(([k, v]) => {
        if (v !== '' && v !== null) {
            params[k] = typeof v === 'object' ? v.id : v
        }
    })
    emit('apply', params)
}

function reset() {
    Object.assign(form, empty())
    emit('reset')
}
</script>
