import NpmValidator from 'validator';
import isEmpty from 'lodash/isEmpty';
import { message } from './messages';

/**
 * @param Function  handles messages
 */
export function Validator(getMessage) {

    if (getMessage === undefined) {
        getMessage = function(key) {
            return message[key]
        }
    }

    /** Validate takes array of field, value and property and validate values against the properties
     *
     *  @param array array           object array with fieldName, value and validation rules
     *                               array = {
     *                                    field1 :[value1, property1, property2],
     *                                    field2:[value2,property1,property2,property3]
     *                                    }
     *  @return {object, boolean}    {(key,error),true/false}.
     *                               IF there error field is empty, isValid will be true ELSE false
     */
    this.validate = function(array) {
        const errors = {};

        Object.keys(array).forEach(function(field) {

            //adding '' after array[field][0] will make it a string. npm Validator only validates string
            const value = array[field][0] + '';

            //skipping first element of the array. i.e value
            array[field].shift();

            array[field].every(property => {

                // check if property is an object
                // if yes, take its key as validation name and value as message
                if(typeof property === 'object'){

                    //getting key and value of the object
                    const validationProperty = Object.keys(property)[0];
                    const validationMessage = Object.values(property)[0];

                    const error = getValidationForProperty(value, validationProperty);

                    if (error !== undefined) {
                        errors[field] = validationMessage;
                        return false;
                    }
                }
                else {

                    const error = getValidationForProperty(value, property);

                    if (error !== undefined) {
                        errors[field] = error;
                        return false
                    }
                }
                return true

            });
        });

        return {
            errors,
            isValid: isEmpty(errors)
        };
    }

    /**
     * encodes string containing special characters
     * @param {string} value  string   that needs to be encoded
     * @return {string} encoded string
     */
    function encodeStringWithSpecialChars(value){

        //encoding the string to handle special characters
        let formattedValue = encodeURIComponent(value);

        //NOTE: as encodeURIComponent doesn't encode apostrophe, we manually do it
        //replacing apostrophe with %27(as standard)
        formattedValue = formattedValue.replace(new RegExp('\'','g'),'%27');

        return formattedValue;
    }

    /**
     * Decodes string containing special characters
     * @param  {string} value   encoded string
     * @return {string}         decoded string
     */
    function decodeStringWithSpecialChars(value){

        //replacing %27 with apostrophe
        let formattedValue = value.replace(new RegExp('%27','g'),'\'');

        //decoding uri encoded string
        formattedValue = decodeURIComponent(formattedValue);

        return formattedValue;
    }

    /** eval is converting string into function call
     * @param String|Number value     value, which has to be validated
     * @param String property         property against which value will be validated
     * @return String                 error msg from helper functions
     * */
    function getValidationForProperty(value, property) {

        //replacing apostrophe with escape charcter and epostrophe(\'')
        const formattedValue = encodeStringWithSpecialChars(value);

        if (property.indexOf('(') > -1) {

            /*
             * for eg. property = someFunction(arg1)
             * it will be converted into someFunction(arg1,value)
             **/

            let arg0 = property.replace(')', ',' + '\'' + formattedValue + '\'' + ')').match(/\(([^)]+)\)/)[1].split(',')[0];

            switch (property.split('(')[0]) {

                case 'max':
                    return max(arg0,formattedValue);
                    break;

                case 'min':
                    return min(arg0,formattedValue);
                    break;

                case 'isAlpha':
                    return isAlpha(arg0,formattedValue);
                    break;

                case 'isAlphaNumeric':
                    return isAlphaNumeric(arg0,formattedValue);
                    break;

                case 'maxValue':
                    return maxValue(arg0,formattedValue);
                    break;

                case 'minValue':
                    return minValue(arg0,formattedValue);
                    break;

                case 'passwordMatch':
                    return passwordMatch(arg0,formattedValue);
                    break;

                case 'shouldNotMatch':
                    return shouldNotMatch(arg0,formattedValue);
                    break;

                case 'isMobile':
                    return isMobile(arg0,formattedValue);
                    break;

                case 'isValidWithRegex':
                    return isValidWithRegex(arg0,formattedValue);
                    break;

            }

            // const functionWithArgument = property.replace(')', ',' + '\'' + formattedValue + '\'' + ')');

            // return eval(functionWithArgument);
        }

        switch (property) {

            case 'isRequired':
                return isRequired(formattedValue);
                break;

            case 'isEmail':
                return isEmail(formattedValue);
                break;

            case 'isNumber':
                return isNumber(formattedValue);
                break;

            case 'isDate':
                return isDate(formattedValue);
                break;

            case 'isUrl':
                return isUrl(formattedValue);
                break;

            case 'isValidUserName':
                return isValidUserName(formattedValue);
                break;
        }

        // return eval(`${property}('${formattedValue}')`);
    }


    function isRequired(value) {
        value = decodeStringWithSpecialChars(value);

        if (NpmValidator.isEmpty(value)) {
            return getMessage("this_field_is_required");
        }
    }


    function max(maxLength, value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isLength(value, 0, maxLength)) {
            return getMessage("max_length_exceeded", maxLength);
        }
    }

    function min(minLength, value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isLength(value, minLength, 100)) {
            return getMessage("not_enough_length", minLength);
        }
    }

    function isEmail(value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isEmail(value)) {
            return getMessage("invalid_email");
        }
    }

    /**
     * USAGE : write it like a string   in 'isAlpha' or isAlpha(true)
     * @param {string|boolean} strict   if strict is passed as true, it will not allow any space in the string
     * @param {string} value            value input by user
     * */
    function isAlpha(strict = false, value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isEmpty(value)) {

            //making a copy of value to avoid mutating the original copy
            let valueCopy = value.slice(0)

            //if strict is not true, it is going to trim all spaces and validate it further
            if (strict !== true) {
                valueCopy = valueCopy.replace(/ /g, '')
            }

            //allow space
            if (!NpmValidator.isAlpha(valueCopy)) {
                return getMessage("not_alpha");
            }
        }
    }

    /**
     * USAGE : write it like a string in 'isAlphanumeric' or isAlphanumeric(true)
     * @param {string|boolean} strict           if strict is passed as true, it will not allow any space in the string
     * @param {string} value            value input by user
     * */
    function isAlphaNumeric(strict = false, value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isEmpty(value)) {

            //making a copy of value to avoid mutating the original copy
            let valueCopy = value.slice(0)

            //if strict is not true, it is going to trim all spaces and validate it further
            if (strict !== true) {
                valueCopy = valueCopy.replace(/ /g, '')
            }

            if (!NpmValidator.isAlphanumeric(valueCopy)) {
                return getMessage("not_alphanumeric");
            }
        }
    }

    /**
     * Validates if a value is number or not
     * @param  Number  value [description]
     * @return String
     */
    function isNumber(value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isEmpty(value)) {
            if (!NpmValidator.isInt(value)) {
                return getMessage("invalid_number");
            }
        }
    }

    /**
     * validates integer. (minimum value by default is -1000000)
     * @param maxVal => maximum value allowed for value
     * @param value => input value
     */
    function maxValue(maxVal, value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isEmpty(value)) {
            if (!(value <= maxVal)) {
                return getMessage("more_than_max_value");
            }
        }
    }

    /** validates integer.(maximum value by default is 1000000)
     * @param minVal => minimum value allowed for value
     * @param value => input value
     */
    function minValue(minVal, value) {
        value = decodeStringWithSpecialChars(value);
        const stringValue = `${value}`;
        if (!NpmValidator.isEmpty(stringValue)) {
            if (!(value >= minVal)) {
                return getMessage("not_enough_length");
            }
        }
    }

    /**
     * If two passed passwords are matching
     * @param  {String} password
     * @param  {String} passwordConfirm
     * @return {String|undefined}
     */
    function passwordMatch(password, passwordConfirm) {
        if (!NpmValidator.equals(password, passwordConfirm)) {
            return getMessage("password_does_not_match");
        }
    }


    /**
     * If two values are same or not
     * @param  {String} oldPassword [description]
     * @param  {String} newPassword [description]
     * @return {String|undefined}
     */
    function shouldNotMatch(oldPassword, newPassword) {
        if (NpmValidator.equals(oldPassword, newPassword)) {
            return getMessage("password_is_same");
        }
    }

    /**
     * Checks if a value is a valid date or not
     * TODO: use moment for date validation
     * @param  {String}  value
     * @return {Boolean}
     */
    function isDate(value) {
        value = decodeStringWithSpecialChars(value);
        if (!NpmValidator.isISO8601(value) && !NpmValidator.isRFC3339(value)) {
            return getMessage("invalid_date");
        }
    }

    /**
     * @param Boolean isMobileValid     It is sent by the state. So if it is false it returns the error message
     * TODO: Correct this validation
     * @param Any     value             Not required but it is better to just write it in the function rather than recreating a logic
     * @return string                   Validation message
     * */
    function isMobile(isMobileValid, value) {
        value = decodeStringWithSpecialChars(value);
        if (isMobileValid !== 'true') {
            return getMessage("invalid_mobile");
        }
    }

    /**
     * Compare the string with regex pattern and tells if string is valid
     * @return {string} [description]
     */
    function isValidWithRegex(regex,value)
    {
        value = decodeStringWithSpecialChars(value);

        if(value.search(regex) === -1){
            return getMessage('invalid_string');
        }
    }

    /**
     * If the passed value is a valid url
     * @param  {String}  value [description]
     * @return {String|undefined}      if invalid, a message else nothing
     */
    function isUrl(value){
        value = decodeStringWithSpecialChars(value);

        var regexQuery = "^(?:(?:https?|ftp):\\/\\/)?(?:\\S+(?::\\S*)?@)?(?:\\[((?:[a-fA-F0-9:]+))\\]|(?:[a-zA-Z0-9-]+\\.)+[a-zA-Z]{2,})(?::\\d{1,5})?(?:[\\/?#]\\S*)?$";
        var url = new RegExp(regexQuery,"i");

        if(!url.test(value) && !NpmValidator.isEmpty(value)){
            return getMessage('invalid_url');
        }
    }

    /**
     * @param string value
     * @return string
     * username may only contain alphanumeric characters or hyphens.
     * username cannot have multiple consecutive hyphens.
     * username cannot begin or end with a hyphen.
     * Minimum is 2 characters
     * Maximum is 50 characters.
     */
    function isValidUserName(value) {
        var regex = /^[a-z\d](?:[a-z\d]|-(?=[a-z\d])){1,49}$/i;
        if (!NpmValidator.isEmpty(value) && !value.match(regex)) {
            return getMessage("invalid_username");
        }
    }
}
