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
        $templateId = static::where('name', $name)->value('selected_template_id');

        /** @var Template|null $template */
        $template = $templateId ? Template::find($templateId) : null;

        return $template;
    }
}
