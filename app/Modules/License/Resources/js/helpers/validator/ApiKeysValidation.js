import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function ApiKeysValidation(data) {

    const {api_key_secret, api_key_products_add, api_key_products_edit, api_key_clients_add, api_key_clients_edit, api_key_licenses_add, api_key_licenses_edit, api_key_installations_edit, api_key_search, api_key_status,api_key_description} = data

    var validatingData = {

        api_key_secret: [api_key_secret, 'isRequired'],

        api_key_products_add: [api_key_products_add, 'isRequired'],

        api_key_products_edit: [api_key_products_edit, 'isRequired'],

        api_key_clients_add: [api_key_clients_add, 'isRequired'],

        api_key_clients_edit: [api_key_clients_edit, 'isRequired'],

        api_key_licenses_add: [api_key_licenses_add, 'isRequired'],

        api_key_licenses_edit: [api_key_licenses_edit, 'isRequired'],

        api_key_installations_edit: [api_key_installations_edit, 'isRequired'],

        api_key_search: [api_key_search, 'isRequired'],

        api_key_status: [api_key_status, 'isRequired'],

        api_key_description:[api_key_description, 'isRequired'],

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
