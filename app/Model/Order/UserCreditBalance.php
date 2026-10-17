<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * The client's spendable credit balance, one row per user+currency. Written
 * only by {@see \App\Services\Payment\CreditBalanceService}, which locks this
 * row (SELECT ... FOR UPDATE) before every grant/apply so concurrent requests
 * can't double-spend the same balance. The history behind this number lives
 * in {@see CreditTransaction}, never here.
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $balance
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCreditBalance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCreditBalance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserCreditBalance query()
 *
 * @mixin \Eloquent
 */
class UserCreditBalance extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'user_credit_balances';

    protected $fillable = ['user_id', 'currency', 'balance'];
}
