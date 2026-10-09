<?php

declare(strict_types=1);

namespace App\BillingLog\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property-read Collection<int, ExceptionLog> $exceptions
 * @property-read int|null $exceptions_count
 * @property-read Collection<int, MailLog> $mail
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

    /**
     * @return HasMany<ExceptionLog, $this>
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(ExceptionLog::class);
    }

    /**
     * @return HasMany<MailLog, $this>
     */
    public function mail(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }
}
