<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait ListsDeals
{
    /**
     * Get the resource deals.
     *
     * @param int   $id      The resource id
     * @param array $options Extra parameters
     * @return Response
     */
    public function deals($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/deals', $options);
    }
}
