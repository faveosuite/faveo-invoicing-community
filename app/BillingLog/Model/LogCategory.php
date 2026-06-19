<?php

declare(strict_types=1);

namespace App\BillingLog\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\BillingLog\Model\ExceptionLog> $exceptions
 * @property-read int|null $exceptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\BillingLog\Model\MailLog> $mail
 * @property-read int|null $mail_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LogCategory whereName($value)
 *
 * @mixin \Eloquent
 */
class LogCategory extends Model
{
    protected $table = 'log_categories';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function exceptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExceptionLog::class);
    }

    public function mail(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MailLog::class);
    }
}
