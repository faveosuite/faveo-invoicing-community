<?php

namespace App\Model\Common;

use App\BaseModel;

class TemplateType extends BaseModel
{
    public function selectedTemplate()
    {
        return $this->belongsTo(Template::class, 'selected_template_id');
    }

    public function templates()
    {
        return $this->hasMany(Template::class, 'type');
    }

    public static function getSelectedTemplate(string $name): ?Template
    {
        $templateId = static::where('name', $name)->value('selected_template_id');

        return $templateId ? Template::find($templateId) : null;
    }
}
