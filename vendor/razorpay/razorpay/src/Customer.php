<?php

namespace Razorpay\Api;

class Customer extends Entity
{
    /**
     *  @param $id Customer id description
     */
    public function fetch($id)
    {
        return parent::fetch($id);
    }

    public function all($options = array())
    {
        return parent::all($options);
    }

    public function create($attributes = array())
    {
        return parent::create($attributes);
    }

    public function edit($attributes = null)
    {
        $entityUrl = $this->getEntityUrl().$this->id;

        return $this->request('PUT', $entityUrl, $attributes);
    }

    public function tokens()
    {
        $token = new Token();

        $token['customer_id'] = $this->id;

        return $token;
    }

    public function addBankAccount($attributes = array())
    {
        $entityUrl = $this->getEntityUrl().$this->id. '/bank_account';

        return $this->request('POST', $entityUrl, $attributes);
    }

    public function deleteBankAccount($bank_id)
    {
        $entityUrl = $this->getEntityUrl() . $this->id. '/bank_account/'. $bank_id;

        return $this->request('DELETE', $entityUrl);
    }

    public function requestEligibilityCheck($attributes = array())
    {
        $entityUrl = $this->getEntityUrl(). '/eligibility';

        return $this->request('POST', $entityUrl, $attributes);
    }

    public function fetchEligibility($id)
    {
        $entityUrl = $this->getEntityUrl(). '/eligibility/'. $id;

        return $this->request('GET', $entityUrl);
    }
}
