<?php

namespace App;

use File;
use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * ======================================
 * Attchment Model
 * ======================================
 * This is a model representing the attachment table.
 *
 * @author Ladybird <info@ladybirdweb.com>
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 *
 * @mixin \Eloquent
 */
class BaseModel extends Model
{
    /**
     * @var array<mixed>
     */
    protected array $purifyExcept = [
        'short_description',
        'description',
        'product_description',
    ];

    #[Override]
    public function setAttribute($property, $value): void
    {
        //     .DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'HTMLPurifier.auto.php');
        $path = base_path('vendor'.DIRECTORY_SEPARATOR.'htmlpurifier'
            .DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.
            'HTMLPurifier'.DIRECTORY_SEPARATOR.'DefinitionCache'
            .DIRECTORY_SEPARATOR.'Serializer');
        if (! File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        if (! is_array($value) && ! in_array($property, $this->purifyExcept) && $value != strip_tags((string) $value)) {
            $value = $purifier->purify($value);
        }

        parent::setAttribute($property, $value);
    }
}
