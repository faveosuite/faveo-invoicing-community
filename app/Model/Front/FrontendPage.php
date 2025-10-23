<?php

namespace App\Model\Front;

use App\BaseModel;

class FrontendPage extends BaseModel
{
    protected $table = 'frontend_pages';

    protected $fillable = ['parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type'];

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '', $value);
    }

    public function parent()
    {
        return $this->belongsTo(FrontendPage::class, 'parent_page_id');
    }
}
