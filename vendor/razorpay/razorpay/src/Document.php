<?php

namespace Razorpay\Api;

class Document extends Entity
{

    public function create($attributes = array())
    {
        $attributes = $this->setFile($attributes);

        return parent::create($attributes);
    }

    public function fetch($id)
    {
        return parent::fetch($id);
    }

}
