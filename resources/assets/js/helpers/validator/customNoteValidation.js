import { useAlertStore } from '../../core/stores/alert'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateCustomNoteSettings(data) {

  const {
    notification_product_not_found,
    notification_product_inactive,
    notification_license_ok,
    notification_license_not_found,
    notification_invalid_ip,
    notification_invalid_domain,
    notification_domain_required,
    notification_domain_in_use,
    notification_license_suspended,
    notification_license_expired,
    notification_updates_expired,
    notification_support_expired,
    notification_license_cancelled,
    notification_license_limit,
    notification_installation_not_found,
    notification_invalid_signature,
    notification_host_banned,
    notification_unknown_error
  } = data

  var validatingData = {

    notification_product_not_found: [notification_product_not_found,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_product_inactive: [notification_product_inactive,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_ok: [notification_license_ok,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_not_found: [notification_license_not_found,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_invalid_ip: [notification_invalid_ip,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_invalid_domain: [notification_invalid_domain,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_domain_required: [notification_domain_required,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_domain_in_use: [notification_domain_in_use,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_suspended: [notification_license_suspended,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_expired: [notification_license_expired,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_updates_expired: [notification_updates_expired,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_support_expired: [notification_support_expired,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_cancelled: [notification_license_cancelled,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_license_limit: [notification_license_limit,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_installation_not_found: [notification_installation_not_found,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_invalid_signature: [notification_invalid_signature,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_host_banned: [notification_host_banned,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
    notification_unknown_error: [notification_unknown_error,{ 'max(250)' : 'The word limit should be less than 250 characters.'} , 'isRequired'],
  };

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  useAlertStore().setValidationError(errors);

  return { errors, isValid };
}
