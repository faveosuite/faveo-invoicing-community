import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateLicenseSettings(data) {

  const { product_id, client_name, product_title, license_code, license_expire_date, license_updates_date, license_support_date, license_comments } = data

  var validatingData = {

      product_id: [product_id, 'isRequired'],

      product: [product_title, 'isRequired'],

      license_expire_date: [license_expire_date, 'isRequired'],

      license_updates_date: [license_updates_date, 'isRequired'],

      license_support_date: [license_support_date, 'isRequired'],

      license_comments:[license_comments, { 'max(250)' : 'The comments limit should be less than 250 characters.'}]

  };

  if(!license_code) {

      validatingData.client = [client_name, 'isRequired'];
  }

  if(!client_name) {

      validatingData.license_code = [license_code, 'isRequired'];
  }

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
