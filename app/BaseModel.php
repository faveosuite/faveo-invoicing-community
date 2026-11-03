<?php

namespace App;

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
     * @var list<string>
     */
    protected $fillable = [
        'api_key',
    ];

    /**
     * @var list<string>
     */
    protected array $purifyExcept = [
        'short_description',
        'description',
        'product_description',
    ];

    #[Override]
    public function setAttribute($property, $value): void
    {
        $config = HTMLPurifier_Config::createDefault();
        // No on-disk definition cache: its default dir lives under vendor/, which the
        // web user can't write when composer ran as root — the write warning then
        // aborted the save (e.g. registration). Purify only runs on values with HTML.
        $config->set('Cache.DefinitionImpl', null);
        $purifier = new HTMLPurifier($config);
        if (! is_array($value) && ! in_array($property, $this->purifyExcept) && $value != strip_tags((string) $value)) {
            $value = $purifier->purify($value);
        }

        parent::setAttribute($property, $value);
    }
}
