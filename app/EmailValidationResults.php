<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property string $method
 * @property string $status
 * @property string $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $state
 * @property string|null $town
 * @property string|null $mobile
 * @property string|null $mobile_code
 * @property string|null $mobile_country_iso
 * @property string|null $country
 * @property string|null $company
 * @property string|null $address
 * @property string|null $first_name
 * @property string|null $last_name
 * @property int|null $timezone_id
 * @property string|null $registration
 * @property string|null $ip
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereMobileCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereMobileCountryIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereRegistration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereTimezoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailValidationResults whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailValidationResults extends Model
{
    protected $table = 'email_validation_results';

    protected $fillable = ['email', 'method', 'status', 'result', 'state', 'town', 'first_name', 'last_name', 'company', 'address', 'registration', 'mobile',
        'mobile_code', 'country', 'mobile_country_iso'];
}
