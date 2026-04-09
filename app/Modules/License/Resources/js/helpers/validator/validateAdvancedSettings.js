import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateAdvancedSettings(data) {

    const { API_STATUS} = data

    var validatingData = {

        API_STATUS: [API_STATUS,'isRequired'],

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
