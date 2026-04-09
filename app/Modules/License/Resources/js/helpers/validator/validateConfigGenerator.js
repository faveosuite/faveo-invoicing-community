import store from '../../store'

import { Validator } from '../easy-validator';

import { lang } from '../../helpers/extraLogics';

export function validateConfigGenerator(data) {

    const {product_id, License_Verification_Period, License_Storage_type, Database_License_File_Location, MySQL_Table_Name, Delete_Cancelled_License, Delete_Cracked_License, God_Mode} = data

    var validatingData = {

        product_id: [product_id, 'isRequired'],

        License_Verification_Period: [License_Verification_Period, 'isRequired'],

        License_Storage_type: [License_Storage_type, 'isRequired'],

        Database_License_File_Location: [Database_License_File_Location, 'isRequired'],

        MySQL_Table_Name: [MySQL_Table_Name, 'isRequired'],

        Delete_Cancelled_License: [Delete_Cancelled_License, 'isRequired'],

        Delete_Cracked_License: [Delete_Cracked_License, 'isRequired'],

        God_Mode: [God_Mode, 'isRequired'],

    };

    const validator = new Validator(lang);

    const { errors, isValid } = validator.validate(validatingData);

    store.dispatch('setValidationError', errors);

    return { errors, isValid };
}
