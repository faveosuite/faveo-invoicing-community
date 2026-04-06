import store from '../../store'

import {Validator} from "../easy-validator";

import {lang} from '../extraLogics';

export function validateProfileSettings(data){

    const { client_fname, client_lname, client_email, client_username, client_timezone_id } = data;

    let validatingData = {

        client_fname: [client_fname, 'isRequired'],

        client_lname: [client_lname, 'isRequired'],

        client_email: [client_email, 'isRequired'],

        client_username: [client_username, 'isRequired'],

        client_timezone_id: [client_timezone_id, 'isRequired'],
    };

    const validator = new Validator(lang);

    const {errors, isValid} = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return {errors, isValid};
};
