<?php

declare(strict_types=1);

namespace App\Model\Common\Mailchimp;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $category_id
 * @property string|null $list_id
 * @property string|null $category_option_id
 * @property string|null $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereCategoryOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereListId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailchimpGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MailchimpGroup extends Model
{
    protected $table = 'mailchimp_groups';

    protected $fillable = ['category_id', 'list_id', 'category_option_id', 'category_name'];
}
