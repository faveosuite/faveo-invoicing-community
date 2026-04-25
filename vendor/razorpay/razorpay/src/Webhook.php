<?php

namespace Razorpay\Api;
use Requests;

class Webhook extends Entity
{
    /**
     * @param $id webhook id description
     */
    public function create($attributes = array())
    {
        if(isset($this->account_id))
        {
            $url = 'accounts/'. $this->account_id . '/' .$this->getEntityUrl();     
        
            return $this->request('POST', $url, $attributes, 'v2');
        }
        return parent::create($attributes);
    }

    public function fetch($id)
    {
        if(isset($this->account_id))
        {
            $url = 'accounts/'. $this->account_id . '/' .$this->getEntityUrl() . $id;     

            return $this->request('GET', $url, null, 'v2');
        }
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        if(isset($this->account_id))
        {
            $url = 'accounts/'. $this->account_id . '/' .$this->getEntityUrl();     
        
            return $this->request('GET', $url, $options, 'v2');
        }
        return parent::all($options);
    }

    /**
     * Patches given webhook with new attributes
     *
     * @param array $attributes
     * @param string $id
     * @return Webhook
     */
    public function edit($attributes, $id)
    {
        $url = $this->getEntityUrl() . $id;
        
        if(isset($this->account_id))
        {
            $url = 'accounts/'.$this->account_id .'/'. $url;     
        
            return $this->request('PATCH', $url, $attributes, 'v2');
        }
        return $this->request(Requests::PUT, $url, $attributes);
    }

    public function delete($id)
    {
        $url = 'accounts/'. $this->account_id . '/' .$this->getEntityUrl(). $id;     
        
        return $this->request('DELETE', $url, null, 'v2');
    }
}
