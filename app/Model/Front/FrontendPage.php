<?php

namespace App\Model\Front;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class FrontendPage extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'frontend_pages';

    protected $fillable = ['parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type'];

    protected $logName = 'pages';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type'
    ];

    protected $logUrl = ['system-managers'];

    protected function getMappings(): array
    {
        return [
            'parent_page_id' => ['Parent Page', fn ($value) => $value],
            'slug' => ['Slug', fn ($value) => $value],
            'name' => ['Name', fn ($value) => $value],
            'content' => ['Content', fn ($value) => $value],
            'url' => ['URL', fn ($value) => $value],
            'publish' => ['Publish status', fn ($value) => $value ? __('message.active') : __('message.inactive')],
            'type' => ['Type', fn ($value) => $value],
        ];
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '', $value);
    }
}
