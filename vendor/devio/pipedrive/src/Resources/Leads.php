<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;
use Devio\Pipedrive\Resources\Traits\Searches;

class Leads extends Resource
{
    use Searches;

    /**
     * Disabled abstract methods.
     *
     * @var array
     */
    protected $disabled = ['deleteBulk'];

    protected $addPostedAsJson = true;

    /**
     * Get all labels.
     *
     * @return Response
     */
    public function labels()
    {
        $this->request->setResource('leadLabels');

        return $this->request->get('');
    }

    /**
     * Add a label.
     *
     * @return Response
     */
    public function addLabel(array $values = [])
    {
        $this->request->setResource('leadLabels');

        $values['json'] = true;

        return $this->request->post('', $values);
    }


    /**
     * Delete a label.
     *
     * @var int $id
     * @return Response
     */
    public function deleteLabel($id)
    {
        $this->request->setResource('leadLabels');

        return $this->request->delete('' . $id);
    }

    /**
     * @param int   $id
     * @param array $values
     * @return Response
     */
    public function updateLabel($id, array $values = [])
    {
        $this->request->setResource('leadLabels');

        return $this->request->put('/' . $id, $values);
    }

    /**
     * @param int   $id
     * @param array $values
     * @return Response
     */
    public function update($id, array $values = [])
    {
        $values['json'] = true;

        array_set($values, 'id', $id);

        return $this->request->patch(':id', $values);
    }

    /**
     * Get all sources.
     *
     * @return Response
     */
    public function sources()
    {
        $this->request->setResource('leadSources');

        return $this->request->get('');
    }
}
