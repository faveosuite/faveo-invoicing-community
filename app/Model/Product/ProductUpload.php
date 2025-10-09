<?php

namespace App\Model\Product;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class ProductUpload extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'product_uploads';

    protected $fillable = ['product_id', 'title', 'description', 'version', 'file', 'is_private', 'is_restricted', 'release_type'];

    protected $logName = 'product';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'product_id', 'title', 'description', 'version', 'file', 'is_private', 'is_restricted', 'release_type'
    ];

    protected $logUrl = ['edit-upload/','/'];

    protected function getMappings(): array
    {
        return [
            'product_id' => ['Product', fn ($value) => Product::find($value)?->name],
            'title' => ['Title', fn ($value) => $value],
            'description' => ['Description', fn ($value) => $value],
            'version' => ['Version', fn ($value) => $value],
            'file' => ['File', fn ($value) => $value],
            'is_private' => ['Is Private', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'is_restricted' => ['Is Restricted', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'release_type' => ['Release Type', fn ($value) => ucfirst($value) ],
        ];
    }

    public function product()
    {
        return $this->belongsTo(\App\Model\Product\Product::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Model\Order\Order::class);
    }

    public function getDependenciesAttribute($value)
    {
        return json_decode($value);
    }
}
