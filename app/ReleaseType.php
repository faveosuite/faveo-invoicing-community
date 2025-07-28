<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleaseType extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'release_types';

    protected $fillable = ['type'];
}
