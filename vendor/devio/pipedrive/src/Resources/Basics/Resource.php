<?php

namespace Devio\Pipedrive\Resources\Basics;

use Devio\Pipedrive\Http\Response;
use ReflectionClass;
use Devio\Pipedrive\Http\Request;
use Devio\Pipedrive\Exceptions\PipedriveException;

abstract class Resource
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
    protected $enabled = ['*'];

    /**
     * List of abstract methods disabled.
     *
     * @var array
     */
    protected $disabled = [];

    /**
     * Should requests to add POST as JSON?
     *
     * @var bool
     */
    protected $addPostedAsJson = false;

    /**
     * Endpoint constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $request->setResource($this->getName());

        $this->request = $request;
    }

    /**
     * Get all the entities.
     *
     * @param array $options Endpoint accepted options
	 * @return Response
     */
    public function all($options = [])
    {
        return $this->request->get('', $options);
    }

    /**
     * Get the entity details by ID.
     *
     * @param int $id Entity ID to find.
	 * @return Response
     */
    public function find($id)
    {
        return $this->request->get(':id', compact('id'));
    }

    /**
     * Add a new entity.
     *
     * @param array $values
     * @return Response
     */
    public function add(array $values)
    {
        if ($this->addPostedAsJson) {
            $values['json'] = true;
        }

        return $this->request->post('', $values);
    }

    /**
     * Update an entity by ID.
     *
     * @param int   $id
     * @param array $values
	 * @return Response
     */
    public function update($id, array $values)
    {
        array_set($values, 'id', $id);

        return $this->request->put(':id', $values);
    }

    /**
     * Delete an entity by ID.
     *
     * @param int $id
	 * @return Response
     */
    public function delete($id)
    {
        return $this->request->delete(':id', compact('id'));
    }

    /**
     * Bulk deleting entities.
     *
     * @param array $ids
     * @return Response
     */
    public function deleteBulk(array $ids)
    {
        return $this->request->delete('', compact('ids'));
    }

    /**
     * Get the endpoint name based on the name class.
     *
     * @return string
     */
    public function getName()
    {
        $reflection = new ReflectionClass($this);

        return camel_case($reflection->getShortName());
    }

    /**
     * Check if the method is enabled for use.
     *
     * @param string $method
     * @return bool
     */
    public function isEnabled($method)
    {
        if ($this->isDisabled($method)) {
            return false;
        }

        // First we will make sure the method only belongs to this abstract class
        // as this does not have to interfere with methods described in child
        // classes. We can now check if it is found in the enabled property.
        if (! in_array($method, get_class_methods(get_class()))) {
            return true;
        }

        return in_array($method, $this->enabled) || $this->enabled == ['*'];
    }

    /**
     * Check if the method is disabled for use.
     *
     * @param string $method
     * @return bool
     */
    public function isDisabled($method)
    {
        return in_array($method, $this->disabled);
    }

    /**
     * Get enabled methods.
     *
     * @return array
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled methods.
     *
     * @param array $enabled
     */
    public function setEnabled($enabled)
    {
        if (! is_array($enabled)) {
            $enabled = func_get_args();
        }

        $this->enabled = $enabled;
    }

    /**
     * Set disabled methods.
     *
     * @param array $disabled
     */
    public function setDisabled($disabled)
    {
        if (! is_array($disabled)) {
            $disabled = func_get_args();
        }

        $this->disabled = $disabled;
    }

    /**
     * Magic method call.
     *
     * @param string $method
     * @param array  $args
     * @return void
     * @throws PipedriveException
     */
    public function __call($method, $args = [])
    {
        // As there are only a few resources that do not have the most common
        // methods described in this function, we can disable some methods
        // in the `disabled` property of the class throwing an exception.
        if (! $this->isEnabled($method)) {
            throw new PipedriveException("The method {$method}() is not available for the resource {$this->getName()}");
        }
    }
}
