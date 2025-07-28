<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CustomValidationProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Call the compositeUniqueValidator method
        $this->arraySizeValidator();
        $this->duplicateCountryForCurrencyValidator();
    }

    /*
     * Extends validator to perform array size validation
     * @return void
     */
    private function arraySizeValidator(): void
    {
        $this->app['validator']->extend('array_size_equals', static function ($attribute, $value, $parameters, $validator) {
            $parameters = array_map('trim', $parameters);

            return count($value) === count(request(array_shift($parameters)));
        });
    }

    /*
     * Extends validator to perform array size validation
     * @return void
     */
    private function duplicateCountryForCurrencyValidator(): void
    {
        $this->app['validator']->extend('duplicate_country', function ($attribute, $value, $parameters, $validator) {
            $parameters = array_map('trim', $parameters);
            $currencyArray = request(array_shift($parameters));

            $keys = array_keys(request(current(explode('.', $attribute))), $value);
            $arrayForChecking = array_intersect_key($currencyArray, array_flip($keys));

            return ! (count($arrayForChecking) !== count(array_unique($arrayForChecking)));
        });
    }
}
