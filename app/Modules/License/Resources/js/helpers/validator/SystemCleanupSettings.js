import store from '../../store';
import { Validator } from '../easy-validator';
import { lang } from '../extraLogics';

export function systemCleanupSettings(data) {
    const { removeOlderCallbacksOptions, removeSystem, removeLicenseReportsOptions, removeSystemReportsOptions } = data;

    // Define validation rules
    const validatingData = {
        DATABASE_CLEANUP_CALLBACKS: removeOlderCallbacksOptions,
        DATABASE_CLEANUP_REPORTS_LICENSES: removeSystem,
        DATABASE_CLEANUP_REPORTS_MAIN: removeLicenseReportsOptions,
        DATABASE_CLEANUP_REPORTS_SYSTEM: removeSystemReportsOptions,
    };

    // Initialize Validator
    const validator = new Validator(lang);

    // Validate data
    let errors = {};
    let isValid = true;

    // Check each field for null value
    Object.keys(validatingData).forEach(key => {
        const value = validatingData[key];
        if (value === null || value === undefined) {
            // Extract string from array if necessary
            const errorMessage = Array.isArray(lang('This field is required')) ? lang('This field is required')[0] : lang('This field is required');
            errors[key] = errorMessage;
            isValid = false;
        }
    });

    // Dispatch validation errors to Vuex store
    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
