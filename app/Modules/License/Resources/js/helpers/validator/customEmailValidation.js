import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateCustomEmailSettings(data) {

  const {
    email_expiring_license_subject,
    email_expiring_license_text,
    email_expiring_updates_subject,
    email_expiring_updates_text,
    email_expiring_support_subject,
    email_expiring_support_text,
  } = data

  var validatingData = {

    email_expiring_license_subject: [email_expiring_license_subject, { 'max(50)' : 'The subject should be less than 50 characters.'} ,'isRequired'],
    email_expiring_license_text: [email_expiring_license_text,{ 'max(250)' : 'The email text should be less than 250 characters.'} , 'isRequired'],
    email_expiring_updates_subject: [email_expiring_updates_subject, { 'max(50)' : 'The subject should be less than 50 characters.'} , 'isRequired'],
    email_expiring_updates_text: [email_expiring_updates_text,{ 'max(250)' : 'The email text should be less than 250 characters.'}, 'isRequired'],
    email_expiring_support_subject: [email_expiring_support_subject,{ 'max(50)' : 'The subject should be less than 50 characters.'} , 'isRequired'],
    email_expiring_support_text: [email_expiring_support_text, { 'max(250)' : 'The email text should be less than 250 characters.'} , 'isRequired'],
  };

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
