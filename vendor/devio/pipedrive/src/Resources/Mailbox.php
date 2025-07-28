<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;

class Mailbox extends Resource
{
    /**
     * Get the Mail threads details by ID.
     *
     * @param int $id   Mail threads ID to find.
	 * @return Response
     */
    public function find($id)
    {
        return $this->request->get('mailThreads/:id', compact('id'));
    }

    /**
     * Delete Mail threads by ID.
     *
     * @param int $id   Mail threads ID to delete.
	 * @return Response
     */
    public function delete($id)
    {
        return $this->request->delete('mailThreads/:id', compact('id'));
    }

    /**
     * Update Mail threads by ID.
     *
     * @param int   $id
     * @param array $values
	 * @return Response
     */
    public function update($id, array $values)
    {
        $values['id'] = $id;
        return $this->request->put('mailThreads/:id', $values);
    }

    /**
     * Get list of mail threads
     *
     * @param string $folder
     * @param array  $options
	 * @return Response 
     */
    public function mailThreads($folder, array $options = [])
    {
        $options['folder'] = $folder;

        return $this->request->get('mailThreads', $options);
    }
    
    /**
     * Get mail messages inside specified mail thread by ID.
     *
     * @param int $id   Mail threads ID to find messages.
	 * @return Response
     */
    public function mailMessages($id, $params=[])
    {
        $params['id'] = $id;
        return $this->request->get('mailThreads/:id/mailMessages', $params);
    }

    /**
     * Get a specific email message by email ID.
     *
     * @param $id   email ID to find a message.
	 * @return Response
     */
    public function mailSpecificMessage($id)
    {
        return $this->request->get('mailMessages/:id', compact('id'));    
    }
}
