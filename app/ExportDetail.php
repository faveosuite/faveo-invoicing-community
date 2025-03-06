<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportDetail extends Model
{
    use HasFactory;
    protected $table = 'export_details';
    protected $fillable = ['user_id', 'file', 'file_path', 'name'];

    public function user(){
        return $this->belongsTo(\App\User::class);
    }
}

