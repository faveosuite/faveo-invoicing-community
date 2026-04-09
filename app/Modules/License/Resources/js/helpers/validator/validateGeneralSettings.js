import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../extraLogics';

export function validateGeneralSettings(data) {

    const { google_secret_key, google_site_key, recaptcha_status, agora_invoicing_url, timezone, date_format, time_format, license_app_key, license_app_secret } = data

    let validatingData = {

        agora_invoicing_url: [agora_invoicing_url,'isRequired'],

        timezone: [timezone, 'isRequired'],

        date_format: [date_format, 'isRequired' ],

        time_format: [time_format,'isRequired'],

        license_app_key: [license_app_key, 'isRequired'],

        license_app_secret: [license_app_secret, 'isRequired'],

    };

    if(recaptcha_status) {
        validatingData.google_site_key = [google_site_key, 'isRequired'];
        validatingData.google_secret_key = [google_secret_key, 'isRequired'];
    }

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
