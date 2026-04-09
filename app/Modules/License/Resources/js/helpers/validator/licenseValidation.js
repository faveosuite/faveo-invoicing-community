import {store} from 'store'

import { Validator } from '../easy-validator';

import { lang } from 'helpers/extraLogics';

export function validateLicenseSettings(data) {

  const { product_id, license_expire_date, license_updates_date, license_support_date } = data

  var validatingData = {

      product_id: [product_id, 'isRequired'],
      license_expire_date: [license_expire_date, 'isRequired'],
      license_updates_date: [license_updates_date, 'isRequired'],
      license_support_date: [license_support_date,'isRequired']
  };

  // if(!data.client_id){
  //
  //   validatingData['license_code'] = [data.license_code,'isRequired'];
  //
  // }

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
