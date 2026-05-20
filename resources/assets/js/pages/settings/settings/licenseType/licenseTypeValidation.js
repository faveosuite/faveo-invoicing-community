export function licenseTypeCreateRules(name, t) {
    return {
        license_type_name: [name, { isRequired: t('message.field_required') }],
    }
}

export function licenseTypeEditRules(name, t) {
    return {
        license_type_edit_name: [name, { isRequired: t('message.field_required') }],
    }
}
