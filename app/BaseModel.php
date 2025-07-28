<?php

namespace App;

use File;
use Illuminate\Database\Eloquent\Model;

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
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        if (! is_array($value)) {
            if ($value != strip_tags($value)) {
                $value = $purifier->purify($value);
            }
        }
        parent::setAttribute($property, $value);
    }
}
