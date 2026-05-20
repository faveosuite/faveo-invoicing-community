<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBackupCodes extends Model
{
    protected $table = 'user_backup_codes';

    protected $fillable = ['user_id', 'backup_codes'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
