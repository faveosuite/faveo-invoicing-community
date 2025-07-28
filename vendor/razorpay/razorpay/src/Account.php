<?php

namespace Razorpay\Api;

class Account extends Entity
{
    
    public function create($attributes = array())
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('POST', $entityUrl, $attributes, 'v2');
    }

    public function fetch($id)
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('GET', $entityUrl . $id, null, 'v2');
    }

    public function delete()
    {
        $entityUrl = $this->getEntityUrl();

        return $this->request('DELETE', $entityUrl . $this->id, null, 'v2');
    }

    public function edit($attributes = array())
    {
        $url = $this->getEntityUrl() . $this->id;

        return $this->request('PATCH', $url, $attributes, 'v2');
    }

    public function stakeholders()
    {
        $stakeholder = new Stakeholder();

        $stakeholder['account_id'] = $this->id;

        return $stakeholder;
    }

    public function products()
    {
        $product = new Product();

        $product['account_id'] = $this->id;

        return $product;
    }

    public function webhooks()
    {
        $webhook = new Webhook();

        $webhook['account_id'] = $this->id;

        return $webhook;
    }

    public function uploadAccountDoc($attributes = array())
    {
      $attributes = $this->setFile($attributes);

      $entityUrl = $this->getEntityUrl() .$this->id .'/documents';
      
      return $this->request('POST', $entityUrl, $attributes, 'v2');
    }

    public function fetchAccountDoc()
    {
        $entityUrl = $this->getEntityUrl() .$this->id .'/documents';

        return $this->request('GET', $entityUrl, null, 'v2');
    }
}
