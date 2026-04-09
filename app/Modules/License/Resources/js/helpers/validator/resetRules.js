import store from "../../store";

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateResetSettings(data) {

    const { password, password_confirmation, token } = data;

    let validatingData = {

        password: [password, 'isRequired', 'max(50)', 'min(2)'],

        password_confirmation: [password_confirmation, 'isRequired', 'max(50)', 'min(2)'],

    };
    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors); //if component is valid, an empty state will be sent

    return { errors, isValid };
};
