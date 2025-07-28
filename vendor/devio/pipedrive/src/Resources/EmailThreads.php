<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;

class EmailThreads extends Resource
{
    /**
     * Disabled abstract methods.
     *
     * @var array
     */
    protected $disabled = ['add'];

    /**
     * Get the messages inside a thread.
     *
     * @param int   $id
     * @param array $options
     * @return Response
     */
    public function messages($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/messages', $options);
    }
}
