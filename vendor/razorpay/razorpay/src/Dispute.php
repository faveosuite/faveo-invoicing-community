<?php

namespace Razorpay\Api;

class Dispute extends Entity
{
    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function accept()
    {
        $entityUrl = $this->getEntityUrl(). $this->id. '/accept';

        return $this->request('POST', $entityUrl);
    }

    public function contest($attributes = array())
    {
        $entityUrl = $this->getEntityUrl(). $this->id. '/contest';

        return $this->request('PATCH', $entityUrl, $attributes);
    }
}