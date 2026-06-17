<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';

    protected $fillable = ['name', 'data', 'type', 'url', 'reply_to'];

    public function type()
    {
        return $this->hasOne(TemplateType::class, 'id', 'type');
    }
}
