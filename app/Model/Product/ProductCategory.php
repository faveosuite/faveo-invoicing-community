<?php

declare(strict_types=1);

namespace App\Model\Product;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string|null $category_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductCategory extends Model
{
    use LogsActivity;

    protected $table = 'product_categories';

    protected $fillable = ['id', 'category_name'];

    protected static string $logName = 'Product Category';

    /**
     * @var array<mixed>
     */
    protected static array $logAttributes = ['category_name'];

    protected static bool $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        // dd(Activity::where('subject_id',)->pluck('subject_id'));
        if ($eventName === 'created') {
            return 'Product Category'.$this->name.' was created'; // @phpstan-ignore property.notFound
        }

        if ($eventName === 'updated') {
            return 'Product Category  <strong> '.$this->name.'</strong> was updated'; // @phpstan-ignore property.notFound
        }

        if ($eventName === 'deleted') {
            return 'Product Category <strong> '.$this->name.' </strong> was deleted'; // @phpstan-ignore property.notFound
        }

        return '';

        // return "Product  has been {$eventName}";
        // \Auth::user()->activity;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return \Spatie\Activitylog\Support\LogOptions::defaults();
    }
}
