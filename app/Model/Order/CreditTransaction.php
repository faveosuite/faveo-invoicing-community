<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Append-only history behind a client's credit balance — never edited or
 * deleted. `amount` is signed: positive for a deposit (overpayment,
 * manual_grant, downgrade_proration), negative for a spend
 * (applied_to_invoice). `payment_id`/`invoice_id` are just context for *why*
 * a row happened, not a restriction on what the balance can be spent on —
 * credit is fully pooled per currency regardless of which type produced it.
 *
 * @property int $id
 * @property int $user_id
 * @property string $currency
 * @property string $amount
 * @property string $type
 * @property int|null $payment_id
 * @property int|null $invoice_id
 * @property string|null $note
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CreditTransaction query()
 *
 * @mixin \Eloquent
 */
class CreditTransaction extends BaseModel
{
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    public const TYPE_OVERPAYMENT = 'overpayment';

    public const TYPE_MANUAL_GRANT = 'manual_grant';

    public const TYPE_DOWNGRADE_PRORATION = 'downgrade_proration';

    public const TYPE_APPLIED_TO_INVOICE = 'applied_to_invoice';

    protected $table = 'credit_transactions';

    protected $fillable = ['user_id', 'currency', 'amount', 'type', 'payment_id', 'invoice_id', 'note'];

    /**
     * Human-readable label for a TYPE_* value — callers (admin credit history,
     * anywhere else that lists these) show this instead of the raw snake_case
     * constant. Falls back to a mechanical underscore-replace for any type not
     * listed here, so a future TYPE_* addition degrades instead of vanishing.
     */
    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_OVERPAYMENT => __('message.credit_type_overpayment'),
            self::TYPE_MANUAL_GRANT => __('message.credit_type_manual_grant'),
            self::TYPE_DOWNGRADE_PRORATION => __('message.credit_type_downgrade_proration'),
            self::TYPE_APPLIED_TO_INVOICE => __('message.credit_type_applied_to_invoice'),
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}
