<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait ListsProducts
{
    /**
     * Get the products attached to a resource.
     *
     * @param int   $id      The resource id
     * @param array $options Extra parameters
     * @return Response
     */
    public function products($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/products', $options);
    }
}
