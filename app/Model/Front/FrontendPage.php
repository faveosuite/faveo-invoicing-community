<?php

namespace App\Model\Front;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class FrontendPage extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'frontend_pages';

    protected $fillable = ['parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type', 'created_at'];

    protected $logName = 'pages';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'parent_page_id', 'slug', 'name', 'content', 'url', 'publish', 'type', 'created_at'
    ];

    protected $logUrl = ['pages', 'edit'];

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
            'created_at' => [
                'Publishing Date',
                fn($value) => \Carbon\Carbon::parse($value)->format('d M Y, h:i A')
            ],
        ];
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = str_replace(' ', '', $value);
    }
}
