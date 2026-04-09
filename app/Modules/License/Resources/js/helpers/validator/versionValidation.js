import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../extraLogics';

export function validateVersionSettings(data) {

    const { version_number, product_id, version_comments } = data

    var validatingData = {

        version_number: [version_number, 'isRequired'],

        product_id: [product_id, 'isRequired'],

        version_comments: [version_comments, { 'max(250)' : 'The comment should be less than 250 characters.'} ]

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
