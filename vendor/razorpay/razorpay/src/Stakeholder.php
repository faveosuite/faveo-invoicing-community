<?php

namespace Razorpay\Api;

class Stakeholder extends Entity
{
    public function create($attributes = array())
    {
        $url = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl();     
        
        return $this->request('POST', $url, $attributes, 'v2');
    }

    public function fetch($id)
    {
        $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id;

        return $this->request('GET', $entityUrl, null, 'v2');
    }

    public function all($options = array())
    {
        $relativeUrl = 'accounts/'.$this->account_id.'/'.$this->getEntityUrl();

        return $this->request('GET', $relativeUrl, $options, 'v2');
    }

    public function edit($id, $attributes = array())
    {
        $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id;

        return $this->request('PATCH', $entityUrl, $attributes, 'v2');
    }

    public function uploadStakeholderDoc($id, $attributes = array())
    {
      $attributes = $this->setFile($attributes);

      $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id.'/documents';
      
      return $this->request('POST', $entityUrl, $attributes, 'v2');
    }

    public function fetchStakeholderDoc($id)
    {
        $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id.'/documents';

        return $this->request('GET', $entityUrl, null, 'v2');
    }
}
