<?php

namespace App\Model\Common;

use App\BaseModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $selected_template_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Template|null $selectedTemplate
 * @property-read Collection<int, Template> $templates
 * @property-read int|null $templates_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereSelectedTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TemplateType extends BaseModel
{
    /**
     * @return BelongsTo<Template, $this>
     */
    public function selectedTemplate(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'selected_template_id');
    }

    /**
     * @return HasMany<Template, $this>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'type');
    }

    public static function getSelectedTemplate(string $name): ?Template
    {
        $type = static::where('name', $name)->first(['id', 'selected_template_id']);
        if (! $type) {
            return null;
        }

        // Fresh installs seed templates after the migration that fills selected_template_id,
        // so a type can have templates but no selection; fall back to its first template.
        return ($type->selected_template_id ? Template::find($type->selected_template_id) : null)
            ?? Template::where('type', $type->id)->orderBy('id')->first();
    }
}
