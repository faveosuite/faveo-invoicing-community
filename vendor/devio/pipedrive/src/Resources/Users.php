<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;
use Devio\Pipedrive\Resources\Traits\FindsByName;
use Devio\Pipedrive\Resources\Traits\ListsActivities;
use Devio\Pipedrive\Resources\Traits\ListsUpdates;
use Devio\Pipedrive\Resources\Traits\ListsFollowers;
use Devio\Pipedrive\Resources\Traits\ListsPermittedUsers;

class Users extends Resource
{
    use FindsByName,
        ListsActivities,
        ListsFollowers,
        ListsPermittedUsers,
        ListsUpdates;

    /**
     * Disabled abstract methods.
     *
     * @var array
     */
    protected $disabled = ['delete', 'deleteBulk'];

    /**
     * Get the user permissions.
     *
     * @param int $id
     * @return Response
     */
    public function permissions($id)
    {
        return $this->request->get(':id/permissions', compact('id'));
    }
}
