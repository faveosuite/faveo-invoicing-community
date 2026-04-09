import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateProductSettings(data) {

  const { product_title, product_sku, product_description, product_url_homepage, product_url_download } = data

  var validatingData = {

    product_title: [product_title, 'isRequired'],

    product_sku: [product_sku, 'isRequired'],

    product_description: [product_description, { 'max(250)' : 'The description should be less than 250 characters.'} ]

  };

  // Add optional URL validators only when values are provided
  if (product_url_download !== null) {
      console.log(product_url_homepage)
    validatingData.product_url_homepage = [product_url_homepage, { 'isUrl': lang('enter_valid_homepage_url') }];
  }

  if (product_url_download !== null) {
    validatingData.product_url_download = [product_url_download, { 'isUrl': lang('enter_valid_download_url') }];
  }


  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
