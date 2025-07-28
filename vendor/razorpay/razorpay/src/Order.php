<?php

namespace Razorpay\Api;

use Requests;

class Order extends Entity
{
    /**
     * @param $id Order id description
     */
    public function create($attributes = array())
    {
        $attributes = json_encode($attributes);

        Request::addHeader('Content-Type', 'application/json');

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

    public function edit($attributes = array())
    {
        $url = $this->getEntityUrl() . $this->id;

        return $this->request('PATCH', $url, $attributes);
    }

    public function payments()
    {
        $relativeUrl = $this->getEntityUrl().$this->id.'/payments';

        return $this->request('GET', $relativeUrl);
    }

    public function transfers($options = array())
    {
        $relativeUrl = $this->getEntityUrl().$this->id;

        return $this->request('GET', $relativeUrl, $options);
    }

    public function viewRtoReview()
    {
        $relativeUrl = $this->getEntityUrl(). $this->id .'/rto_review';

        return $this->request('POST', $relativeUrl);
    }

    public function editFulfillment($attributes = array())
    {
        $relativeUrl = $this->getEntityUrl(). $this->id .'/fulfillment';

        return $this->request('POST', $relativeUrl, $attributes);
    }
}
