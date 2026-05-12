import { useAlertStore } from '../../core/stores/alert'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateInstallationSettings(data) {

  const { installation_ip, installation_domain } = data

  var validatingData = {

    installation_ip: [installation_ip, 'isRequired'],
  };

  const validator = new Validator(lang);

  const { errors, isValid } = validator.validate(validatingData);

  useAlertStore().setValidationError(errors);

  return { errors, isValid };
}
