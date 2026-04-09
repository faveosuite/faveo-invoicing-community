import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function bannedHostValidation(data) {

  const {banned_host_ip, banned_host_comments } = data

  var validatingData = {

      banned_host_ip: [banned_host_ip, 'isRequired'],

    banned_host_comments: [banned_host_comments ,{ 'max(250)' : 'The description should be less than 250 characters.'}]

  };

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
