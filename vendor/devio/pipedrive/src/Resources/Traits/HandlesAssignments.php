<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait HandlesAssignments
{
    /**
     * Get the resource assignments.
     *
     * @param int   $id
     * @param array $options
     * @return Response
     */
    public function assignments($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/assignments', $options);
    }

    /**
     * Add a new assignment to the resource.
     *
     * @param int $id
     * @param int $user_id
     * @return Response
     */
    public function addAssignment($id, $user_id)
    {
        return $this->request->post(':id/assignments', compact('id', 'user_id'));
    }

    /**
     * Delete an assignemt from the resource.
     *
     * @param int $id
     * @param int $user_id
     * @return Response
     */
    public function deleteAssignment($id, $user_id)
    {
        return $this->request->delete(':id/assignments', compact('id', 'user_id'));
    }
}
