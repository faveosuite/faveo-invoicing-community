<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Resources\Basics\Resource;

class CallLogs extends Resource
{
    protected $enabled = ['all', 'find', 'add', 'delete', 'addRecording'];

    
    /**
     * Upload a recording file (audio) to a callLog
     *
     * @param int $id
     * @param \SplFileInfo $file
     * @return Response
     */
    public function addRecording($id, \SplFileInfo $file) {
        return $this->request->post(':id/recordings', compact('id', 'file'));
    }
}
