<?php

namespace App\Model\Common;

use App\BaseModel;

/**
 * @property int $id
 * @property string $name
 * @property int|null $selected_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Common\Template|null $selectedTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Common\Template> $templates
 * @property-read int|null $templates_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereSelectedTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TemplateType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TemplateType extends BaseModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Template, $this>
     */
    public function selectedTemplate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Template::class, 'selected_template_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Model\Common\Template, $this>
     */
    public function templates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Template::class, 'type');
    }

    public static function getSelectedTemplate(string $name): ?Template
    {
        $templateId = static::where('name', $name)->value('selected_template_id');

        /** @var \App\Model\Common\Template|null $template */
        $template = $templateId ? Template::find($templateId) : null;

        return $template;
    }
}
