import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../extraLogics';

export function validateSecuritySettings(data) {

    const { WHITELISTED_ACCESS,BANNED_HOSTS, FAILED_LOGINS_LIMIT, FAILED_LICENSINGS_LIMIT, FAILED_HOSTS_FORGET, WHITELISTED_IP } = data

    var validatingData = {

        WHITELISTED_ACCESS: [WHITELISTED_ACCESS,'isRequired'],

        BANNED_HOSTS: [BANNED_HOSTS, 'isRequired'],

        FAILED_LOGINS_LIMIT: [FAILED_LOGINS_LIMIT, 'isRequired'],

        FAILED_LICENSINGS_LIMIT: [FAILED_LICENSINGS_LIMIT, 'isRequired' ],

        FAILED_HOSTS_FORGET: [FAILED_HOSTS_FORGET,'isRequired'],

        WHITELISTED_IP: [WHITELISTED_IP,'isRequired']

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
