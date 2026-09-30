import { useDateTime } from '../core/composables/useDateTime';

/**
 * gets the last integer from a given url(string)
 * @param  {string} url  url with/without Id
 * @return {integer}     id
 */
export const getIdFromUrl = (url) => {

    let urlArray = url.split("/");

    let idArray = urlArray.filter(function (item) {
        return (parseInt(item) == item);
    });

    return idArray[idArray.length - 1];
};

/**
 * finds object in an object array by key
 * for eg.  array =[{id:1, name:'something'},{id:2, name:'something more'}],
 * we call this method like this : findObjectByKey(array, 'id', 1)
 * and we get result like this : {id:1, name:'something'}
 *
 * @param  {array}         array                    array object that has to be searched
 * @param  {string|number} key                      name of the key
 * @param  {string|number} value                    value of the key
 * @return {object|null}                            found object if present else null
 */
export const findObjectByKey = (array, key, value) => {
    for (var i = 0; i < array.length; i++) {
        if (array[i][key] == value) {
            return array[i];
        }
    }
    return null;
}

/**
 * converts english string into language string
 * NOTE: global lang() method is only available in vue components, so it is required to declare again
 *
 * @param {string}  string      english string
 * @return {string}             language string
 */
export const lang = (string) => {
    if (typeof translator !== 'undefined' && translator.lang) {
        return translator.lang[string] || string;
    }
    return string;
};


/**
 * flattens an array or object by one layer(in an immutable way)
 * For eg. [[1,2,3,4],[5,6]] will become [1,2,3,4,5,6,7]
 *
 *         { key1 : [{id :1},{id:2}], key2: [{id :3},{id:4}] } becomes [{id :1},{id:2},{id :3},{id:4}]
 *
 * @param {array|object} input
 * @return {array}  flattened array|object
 * */
export const flatten = (input) => {

    let flattenObject = Object.keys(input).reduce(function (r, k) {
        return r.concat(input[k]);
    }, []);

    return flattenObject;
}

/**
 * Converts given string into boolean based on php rules
 * for eg. `0` means false, '1' means true, null means false
 * @return {any}
 */
export const boolean = (value) => {

    //for checking if variable is an empty array
    if (Array.isArray(value) && value.length === 0) {
        return false;
    }

    switch (value) {
        case 0:
            return false;

        case '0':
            return false;

        case null:
            return false;

        case "":
            return false;

        case undefined:
            return false;

        case false:
            return false;

        default:
            return true;
    }
};

/**
 * gets the substring value of a given string
 * @param  {string} name
 * @param  {count} number of letters
 * @return {string}     string
 */
export const getSubStringValue = (name, count) => {
    if (name) {
        if (name.length > count) {
            return name.substring(0, count) + '...';
        } else {
            return name;
        }
    }
    return '';
};

/**
 * gets the substring value of a given string
 * @param  {number} length
 * @return {string}     string
 */
export const generateRandomString = (length = 16) => {
    const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
    const bytes = new Uint8Array(length)
    crypto.getRandomValues(bytes)
    let a = ''
    for (let e = 0; e < length; e++) {
        a += charset[bytes[e] % charset.length]
    }
    return a;
};

export const formatDateTime = (dateTime) => {
    return useDateTime().formatDateTime(dateTime)
};

/**
 * Using third party api to get client location by their IP
 * https://about.ip2c.org/#about
 * Using `fetch` to bypassing cors errors
 * https://github.com/axios/axios/issues/1358
 */
export const getCountry = () => {
    return fetch('https://ip2c.org/s')
        .then((response) => response.text())
        .then((response) => {
            const result = (response || '').toString();
            if (!result || result[0] !== '1') {
                throw new Error('unable to fetch the country');
            }
            return result.substr(2, 2);
        });
}
