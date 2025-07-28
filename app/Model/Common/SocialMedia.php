<?php

namespace App\Model\Common;

use App\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocialMedia extends BaseModel
{
    use HasFactory;
    protected $table = 'social_media';

    protected $fillable = ['class', 'fa_class', 'name', 'link'];
}
