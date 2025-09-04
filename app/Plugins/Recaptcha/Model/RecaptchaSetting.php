<?php

namespace App\Plugins\Recaptcha\Model;

use Illuminate\Database\Eloquent\Model;

class RecaptchaSetting extends Model
{
    protected $fillable = [
        'v2_site_key',
        'v2_secret_key',
        'v3_site_key',
        'v3_secret_key',
        'captcha_version',
        'failover_action',
        'score_threshold',
        'error_message',
        'theme',
        'size',
        'badge_position',
    ];
}
