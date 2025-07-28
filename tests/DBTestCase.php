<?php

namespace Tests;

use App\User;
use DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionClass;

/**
 * Base class for test cases with DB testing.
 */
class DBTestCase extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $token;

    protected $organization;
    //NOTE: For logging into api, we require token but for web we don't need any

    /**
     * creates user for db testing for web.
     *
     * @return
     */
    protected function getLoggedInUser($role = 'user')
    {
        $this->user = User::factory()->create(['role' => $role]);
        $this->be($this->user);
    }

    /**
     * for testing non-public methods.
     *
     * @param  $classObject  Object => object of the class, whose methods are required to be fetched
     * @param  $methodName  String => name of the method as string
     * @param  $arguments  Array => method arguments
     * @return ReflectionMethod
     */
    protected function getPrivateMethod(&$classObject, $methodName, $arguments = [])
    {
        $reflector = new ReflectionClass(get_class($classObject));
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($classObject, $arguments);
    }

    /**
     * for testing non-public properties. Sets non-public properties.
     *
     * @param  object  $classObject  object of the class, whose property are required to be fetched
     * @param  string  $propertyName  name of the perperty as string
     * @param  any  $value  new value of the property
     * @return void
     */
    protected function setPrivateProperty(&$classObject, $propertyName, $value)
    {
        $reflector = new ReflectionClass($classObject);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($classObject, $value);
    }

    /**
     * for testing non-public properties. Gets non-public properties at any instant.
     *
     * @param  object  $classObject  object of the class, whose properties are required to be fetched
     * @param  string  $propertyName  name of the property as string
     * @param  any  $value  new value of the property
     * @return any returns the value of the property
     */
    protected function getPrivateProperty(&$classObject, $propertyName)
    {
        $reflector = new ReflectionClass($classObject);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($classObject);
    }

    /* ++++++++++++++++++++++++++ CUSTOM ASSERTIONS +++++++++++++++++++++++++ */

    /**
     * asserts if the given string is alpha or not.
     *
     * @param  string  $value  the value that need to be checked for assertion
     * @return bool true if $value is an alphabet else false
     */
    protected function assertAlpha($value)
    {
        $isAlpha = preg_match('/[a-zA-Z]/', $value) ? true : false;
        $message = "$value is not an alphabet";
        self::assertThat($isAlpha, self::isTrue(), $message);
    }

    /**
     * asserts if the given string is Number or not.
     *
     * @param  string  $value  the value that need to be checked for assertion
     * @return bool true if $value is an number else false
     */
    protected function assertNumber($value)
    {
        $isNumber = preg_match('/\d/', $value) ? true : false;
        $message = "$value is not a number";
        self::assertThat($isNumber, self::isTrue(), $message);
    }

    /**
     * asserts if given array has keys
     * NOTE: this must be used only if multiple keys are required to be checked.
     *        for checking a single key, there already has a method called assertArrayHasKey in phpunit.
     *
     * @param  array  $arrayOfKeys  array of keys that is required to be tested
     * @param  array  $targetArray  array which is required to be tested
     * @return bool true if $value is an number else false
     */
    protected function assertArrayHasKeys($arrayOfKeys, $targetArray)
    {
        $notFoundKeys = [];
        foreach ($arrayOfKeys as $key) {
            if (! array_key_exists($key, $targetArray)) {
                array_push($notFoundKeys, $key);
            }
        }
        //if not found key is empty, it means all the keys are found. else not
        $hasKeys = ! $notFoundKeys ? true : false;
        $notFoundKeysJson = json_encode($notFoundKeys);
        $message = "$notFoundKeysJson not found in target array";
        self::assertThat($hasKeys, self::isTrue(), $message);
    }

    /**
     * Asserts if the given substring is there in the string or not.
     *
     * @param  string  $string  string that needs to be searched (haystack)
     * @param  string  $substring  string that is to be found (needle)
     * @return void
     */
    protected function assertStringContainsSubstring($string, $substring)
    {
        $message = "'$substring' not found in target string";
        $hasSubstring = (strpos($string, $substring) !== false) ? true : false;
        self::assertThat($hasSubstring, self::isTrue(), $message);
    }

    /*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
}
