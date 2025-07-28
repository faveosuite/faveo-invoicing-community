<?php

namespace Devio\Pipedrive\Resources\Traits;

use Devio\Pipedrive\Http\Response;

trait Searches
{
    /**
     * @param string $term
     * @param array  $fields
     *
     * @return Response
     */
    public function search($term, $fields = [], $options = [])
    {
        $options['term'] = $term;
        $options['fields'] = $fields;

        return $this->request->get('search', $options);
    }
}
