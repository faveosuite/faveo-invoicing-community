<?php

namespace App;

use Override;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = ['user_id', 'updated_by_user_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    #[Override]
    public function delete()
    {
        return parent::delete();
    }
}
