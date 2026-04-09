import store from "../../store";

import {Validator} from '../easy-validator';

import {lang} from '../../helpers/extraLogics';

export function validateForgotSettings(data){

    const { email } = data;

    let validatingData = {

        email: [email, 'isRequired','isEmail'],
    };
    const validator = new Validator(lang);

    const {errors, isValid} = validator.validate(validatingData);

    store.dispatch('setValidationError', errors); //if component is valid, an empty state will be sent

    return {errors, isValid};
};
