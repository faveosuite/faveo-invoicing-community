<?php

namespace Razorpay\Api;

class Product extends Entity
{
    public function requestProductConfiguration($attributes = array())
    {
        $url = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl();     
        
        return $this->request('POST', $url, $attributes, 'v2');
    }

    public function fetch($id)
    {
        $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id;

        return $this->request('GET', $entityUrl, null, 'v2');
    }

    public function edit($id, $attributes = array())
    {
        $entityUrl = 'accounts/'.$this->account_id .'/'.$this->getEntityUrl().'/'.$id;

        return $this->request('PATCH', $entityUrl, $attributes, 'v2');
    }
    
    public function fetchTnc($product_name)
    {
        $entityUrl = $this->getEntityUrl().'/'.$product_name.'/tnc';

        return $this->request('GET', $entityUrl,null , 'v2');
    }
}
