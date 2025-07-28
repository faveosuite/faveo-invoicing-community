<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationAttempt extends Model
{
    use HasFactory;

    protected $table = 'verification_attempts';

    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'mobile_attempt', 'email_attempt'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
