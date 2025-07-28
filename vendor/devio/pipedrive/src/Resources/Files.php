<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;

class Files extends Resource
{
    /**
     * Disabled abstract methods.
     *
     * @var array
     */
    protected $disabled = ['deleteBulk'];

    /**
     * Create a remote file and link it to an item.
     *
     * @param string $file_type
     * @param string $title
     * @param string $item_type
     * @param int    $item_id
     * @param string $remote_location
     * @return Response
     */
    public function createRemote($file_type, $title, $item_type, $item_id, $remote_location)
    {
        return $this->request->post(
            'remote',
            compact('file_type', 'title', 'item_type', 'item_id', 'remote_location')
        );
    }

    /**
     * Link a remote file to an item.
     *
     * @param string $item_type
     * @param int    $item_id
     * @param string $remote_id
     * @param string $remote_location
     * @return Response
     */
    public function linkRemote($item_type, $item_id, $remote_id, $remote_location)
    {
        return $this->request->post(
            'remoteLink',
            compact('item_type', 'item_id', 'remote_id', 'remote_location')
        );
    }

    /**
     * Initializes a file download.
     *
     * @param int $id
     * @return Response
     */
    public function download($id) {
        return $this->request->get(':id/download', compact('id'));
    }
}
