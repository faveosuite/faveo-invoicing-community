import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateCleanUpSettings(data) {

    const { DATABASE_CLEANUP_ENABLED, DATABASE_CLEANUP_CALLBACKS,DATABASE_CLEANUP_REPORTS_MAIN,DATABASE_CLEANUP_REPORTS_SYSTEM, DATABASE_CLEANUP_REPORTS_LICENSES} = data

    var validatingData = {

        DATABASE_CLEANUP_ENABLED: [DATABASE_CLEANUP_ENABLED,'isRequired'],

        DATABASE_CLEANUP_CALLBACKS: [DATABASE_CLEANUP_CALLBACKS,'isRequired'],

        DATABASE_CLEANUP_REPORTS_MAIN: [DATABASE_CLEANUP_REPORTS_MAIN, 'isRequired'],

        DATABASE_CLEANUP_REPORTS_SYSTEM: [DATABASE_CLEANUP_REPORTS_SYSTEM,'isRequired'],

        DATABASE_CLEANUP_REPORTS_LICENSES: [DATABASE_CLEANUP_REPORTS_LICENSES, 'isRequired'],

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
