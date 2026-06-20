module.exports = jest.fn(() => ({
    destroy: jest.fn(),
    getNumber: jest.fn(() => ''),
    getSelectedCountryData: jest.fn(() => ({ dialCode: '1', iso2: 'us' })),
    isValidNumber: jest.fn(() => true),
    setNumber: jest.fn(),
    setCountry: jest.fn(),
}))

module.exports.utils = {
    numberFormat: { E164: 0, INTERNATIONAL: 1, NATIONAL: 2, RFC3966: 3 },
    numberType: { FIXED_LINE: 0, MOBILE: 1, UNKNOWN: -1 },
    validationError: { IS_POSSIBLE: 0, INVALID_COUNTRY_CODE: 1, TOO_SHORT: 2, TOO_LONG: 3 },
}
