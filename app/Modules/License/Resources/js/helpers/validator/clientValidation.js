import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../extraLogics';

export function validateClientSettings(data) {

  const { client_fname, client_lname, client_email, client_username } = data

  var validatingData = {

    client_fname: [client_fname, 'isRequired'],

    client_lname: [client_lname, 'isRequired'],

    client_email: [client_email, 'isRequired', 'isEmail'],

      client_username: [client_username, 'isRequired']

  };

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  store.dispatch('setValidationError', errors);

  return { errors, isValid };
}
