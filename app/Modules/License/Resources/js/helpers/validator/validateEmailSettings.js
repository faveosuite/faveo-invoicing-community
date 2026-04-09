import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateEmailSettings(data) {

    const { emailDriver, emailFromName, emailFromAddress,emailPort,emailHost, emailEncryption, emailPassword} = data

    let validatingData = {

        EMAIL_DRIVER: [emailDriver,'isRequired'],

        EMAIL_PORT: [emailPort,'isRequired'],

        EMAIL_HOST: [emailHost, 'isRequired'],

        EMAIL_ENCRYPTION: [emailEncryption,'isRequired'],

        EMAIL_FROM_ADDRESS: [emailFromAddress, 'isRequired'],

        EMAIL_PASSWORD: [emailPassword, 'isRequired' ],

        EMAIL_FROM_NAME: [emailFromName, 'isRequired' ],

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
