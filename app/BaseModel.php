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
 */
class BaseModel extends Model
{
    protected $purifyExcept = [
        'short_description',
        'description',
        'product_description',
    ];

    #[Override]
    public function setAttribute($property, $value)
    {
        // require_once base_path('vendor'.DIRECTORY_SEPARATOR.'htmlpurifier'
        //     .DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'HTMLPurifier.auto.php');
        $path = base_path('vendor'.DIRECTORY_SEPARATOR.'htmlpurifier'
            .DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.
            'HTMLPurifier'.DIRECTORY_SEPARATOR.'DefinitionCache'
            .DIRECTORY_SEPARATOR.'Serializer');
        if (! File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        }

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        if (! is_array($value) && ! in_array($property, $this->purifyExcept)) {
            if ($value != strip_tags((string) $value)) {
                $value = $purifier->purify($value);
            }
        }

        parent::setAttribute($property, $value);
    }
}
