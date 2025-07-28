<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait ListsActivities
{
    /**
     * List the resource activities.
     *
     * @param int   $id
     * @param array $options
     * @return Response
     */
    public function activities($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/activities', $options);
    }
}
