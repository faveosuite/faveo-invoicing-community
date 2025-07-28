<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Resource;

class ItemSearch extends Resource
{
    /**
     * Enabled abstract methods.
     *
     * @var array
     */
    protected $enabled = [];

    /**
     * Search.
     *
     * @param string $term
     * @param array  $options
     * @return Response
     */
    public function search($term, $options = [])
    {
        array_set($options, 'term', $term);

        return $this->request->get('', $options);
    }

    /**
     * Search from a specific field.
     *
     * @param string $term
     * @param string $field_type
     * @param string $field_key
     * @param array  $options
     * @return Response
     */
    public function searchFromField($term, $field_type, $field_key, $options = [])
    {
        $options = array_merge(compact('term', 'field_type', 'field_key'), $options);

        return $this->request->get('field', $options);
    }
}
