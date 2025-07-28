<?php

namespace Razorpay\Api;

class Iin extends Entity
{   
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        $relativeUrl = $this->getEntityUrl(). 'list';

        return $this->request('GET', $relativeUrl, $options);
    }
}
