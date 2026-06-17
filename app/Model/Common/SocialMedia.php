<?php

declare(strict_types=1);

namespace App\Model\Common;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialMedia extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'social_media';

    protected $fillable = ['class', 'fa_class', 'name', 'link'];

    protected $logName = 'social_media';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'name', 'link',
    ];

    protected $logUrl = [
        'segments' => ['social-media', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Social media name', fn ($value) => $value],
            'link' => ['Link', fn ($value) => $value],
        ];
    }
}
