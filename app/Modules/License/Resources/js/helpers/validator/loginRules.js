import store from "../../store";

import {Validator} from '../easy-validator';

import { lang } from '../extraLogics';

export function validateLoginSettings(data){

    const { user_name, password } = data;

    let validatingData = {

        user_name: [user_name, 'isRequired'],

        password: [password, 'isRequired', 'max(50)', 'min(2)'],
    };
    const validator = new Validator(lang);

    const {errors, isValid} = validator.validate(validatingData);

    store.dispatch('setValidationError', errors); //if component is valid, an empty state will be sent

    return {errors, isValid};
};
