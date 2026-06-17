<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommonSettings extends Model
{
    use HasFactory;

    protected $table = 'common_settings';

    protected $fillable = [
        'option_name', 'option_value', 'status', 'optional_field',
    ];

    public function getStatus($option_name)
    {
        $status = '';
        $schema = $this->where('option_name', $option_name)->first();
        if ($schema) {
            return $schema->status;
        }

        return $status;
    }

    public function getOptionValue($option, $field = '')
    {
        $schema = $this->where('option_name', $option);
        if ($field != '') {
            return $schema->where('optional_field', $field)->first();
        }

        return $schema->get();
    }
}
