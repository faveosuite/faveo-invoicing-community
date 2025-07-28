<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Exceptions\PipedriveException;
use Devio\Pipedrive\Http\Request;
use Devio\Pipedrive\Resources\Basics\Entity;
use Devio\Pipedrive\Resources\Basics\Resource;

class Webhooks extends Resource
{
    /**
     * The API caller object.
     *
     * @var Request
     */
    protected $request;

    /**
     * List of abstract methods available.
     *
     * @var array
     */
    protected $enabled = ['add', 'delete', 'find'];

    /**
     * List of abstract methods disabled.
     *
     * @var array
     */
    protected $disabled = [];

    /**
     * Get the entity details by ID.
     *
     * @param int $id   Entity ID to find.
     * @return \Devio\Pipedrive\Http\Response|void
     * @throws PipedriveException
     */
    public function find($id)
    {
        throw new PipedriveException("The method find() is not available for the resource {$this->getName()}");
    }

    /**
     * Update an entity by ID.
     * @param int   $id
     * @param array $values
     *
     * @return \Devio\Pipedrive\Http\Response|void
     * @throws PipedriveException
     */
    public function update($id, array $values)
    {
        throw new PipedriveException("The method update() is not available for the resource {$this->getName()}");
    }
}
