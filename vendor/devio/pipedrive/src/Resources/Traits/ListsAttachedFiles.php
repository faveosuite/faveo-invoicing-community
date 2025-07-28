<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait ListsAttachedFiles
{
    /**
     * Get the resource attached files.
     *
     * @param int   $id      The resource id
     * @param array $options Extra parameters
     * @return Response
     */
    public function attachedFiles($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/files', $options);
    }
}
