<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class DefaultPage extends Model
{

    use SystemActivityLogsTrait;

    protected $table = 'default_pages';

    protected $fillable = ['page_id', 'page_url'];


    protected $logName = 'page';

    protected $logNameColumn = 'Default page';


    protected $logAttributes = [
        'page_id', 'page_url'
    ];

    protected $logUrl = ['/pages', '/edit'];

    protected function getMappings(): array
    {
        return [
            'page_id' => ['Page ID', fn($value) => $value],
            'page_url' => ['Page URL', fn($value) => $value]
        ];
    }
}
