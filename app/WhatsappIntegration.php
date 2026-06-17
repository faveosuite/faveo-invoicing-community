<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $app_id
 * @property string $app_secret
 * @property string $verify_token
 * @property string $config_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereAppSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereConfigId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappIntegration whereVerifyToken($value)
 * @mixin \Eloquent
 */
class WhatsappIntegration extends Model
{
    protected $table = 'whatsapp_integration';

    protected $fillable = ['app_id', 'app_secret', 'verify_token', 'callback_url', 'config_id'];
}
