<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Resources\Basics\Resource;
use Devio\Pipedrive\Resources\Traits\HandlesAssignments;

class PermissionSets extends Resource
{
    use HandlesAssignments;

    /**
     * Enabled abstract methods.
     *
     * @var array
     */
    protected $enabled = ['all', 'find', 'update'];
}
