import {store} from "store";

import {Validator} from '../easy-validator';

import {lang} from 'helpers/extraLogics';

export function validateRegisterSettings(data){

    const { first_name, last_name, email, password, confirm } = data;

    let validatingData = {

        first_name: [first_name, 'isRequired'],

        last_name: [last_name, 'isRequired'],

        email: [email, 'isRequired', 'isEmail'],

        password: [password, 'isRequired', 'max(50)', 'min(2)'],

        confirm: [confirm, 'isRequired', 'max(50)', 'min(2)'],
    };
    const validator = new Validator(lang);

    const {errors, isValid} = validator.validate(validatingData);

    store.dispatch('setValidationError', errors); //if component is valid, an empty state will be sent

    return {errors, isValid};
};
