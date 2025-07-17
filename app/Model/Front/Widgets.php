<?php

namespace App\Model\Front;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class Widgets extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'widgets';

    protected $fillable = ['name', 'type', 'publish', 'content', 'allow_tweets', 'allow_mailchimp', 'allow_social_media'];

    protected $logName = 'widgets';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'name', 'type', 'publish', 'allow_tweets', 'allow_mailchimp', 'allow_social_media',
    ];

    protected $logUrl = [
        'segments' => ['widgets', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Name', fn ($value) => $value],
            'type' => ['Type', fn ($value) => $value],
            'publish' => ['Publish status', fn ($value) => $value ? __('message.active') : __('message.inactive')],
            'allow_tweets' => ['Allow Tweets', fn ($value) => $value ? __('message.active') : __('message.inactive')],
            'allow_mailchimp' => ['Allow Mailchimp', fn ($value) => $value ? __('message.active') : __('message.inactive')],
            'allow_social_media' => ['Allow Social Media', fn ($value) => $value ? __('message.active') : __('message.inactive')],
        ];
    }
}
