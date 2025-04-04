<style>
    .iti--allow-dropdown {
        width: 100% !important;
    }


    .telephone-input {
        width: 100%;
    }


    .iti__selected-country {
        padding: 5px !important;
        background-color: #F2F2F2 !important;
        outline: none !important;
        border-radius: 5% !important;
    }


    .iti__selected-country-primary:hover {
        background-color: #F2F2F2 !important;
    }


    .iti--inline-dropdown .iti__dropdown-content {
        max-width: 355px !important;
        min-width: 270px !important;
    }


    .iti .iti__selected-dial-code {
        margin-right: 4px;
    }


    .iti__search-input {
        padding: 12px 9px 6px 9px !important;
    }


    .iti__arrow {
        margin-right: 5px !important;
    }


</style>
<script>

    let itiInstances = [];

    document.addEventListener('DOMContentLoaded', function () {

        let inputs = document.querySelectorAll('input[type="tel"]');

        inputs.forEach(function (input, index) {
            itiInstances[index] = intlTelInput(input, {
                initialCountry: input.getAttribute('data-country-iso') || 'auto',
                geoIpLookup: (success, failure) => {
                    fetch("https://ipapi.co/json")
                        .then((res) => res.json())
                        .then((data) => success(data.country_code))
                        .catch(() => success('IN'));
                },
                allowDropdown: true,
                separateDialCode: true,
                i18n: 'en',
                showFlags: true,
                formatAsYouType: true,
                strictMode: true,
                formatOnDisplay: true,
                nationalMode: false,
                excludeCountries: ['ax'],
                customPlaceholder: (selectedCountryPlaceholder) => selectedCountryPlaceholder,
            });

            input.addEventListener('countrychange', function() {
                const selectedCountry = itiInstances[index].getSelectedCountryData();
                input.setAttribute('data-dial-code', selectedCountry.dialCode);
                input.setAttribute('data-country-iso', selectedCountry.iso2);
            });
        });
    });

    function validatePhoneNumber(input) {
        let index = Array.from(document.querySelectorAll('input[type="tel"]')).indexOf(input);
        if (index !== -1 && itiInstances[index]) {
            return itiInstances[index].isValidNumber();
        }
        return false;
    }

    function updateCountryCodeAndFlag(input, countryCode) {
        let index = Array.from(document.querySelectorAll('input[type="tel"]')).indexOf(input);
        if (index !== -1 && itiInstances[index]) {
            itiInstances[index].setCountry(countryCode.toLowerCase());

            // Update data attributes based on the selected country
            const selectedCountry = itiInstances[index].getSelectedCountryData();
            input.setAttribute('data-dial-code', selectedCountry.dialCode);
            input.setAttribute('data-country-iso', selectedCountry.iso2);
        }
    }

    function getAllCountryData(filter = null) {
        const iti = itiInstances[0]; // get the instance
        const countries = iti.countries;

        if (!filter) {
            return countries;
        }

        // Convert to lowercase for case-insensitive matching
        const filtered = countries.filter(country => {
            return Object.keys(filter).every(key => {
                const value = filter[key].toString().toLowerCase();
                const countryValue = (country[key] || '').toString().toLowerCase();
                return countryValue === value;
            });
        });

        return filtered;
    }
</script>