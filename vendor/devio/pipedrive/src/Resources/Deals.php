<?php

namespace Devio\Pipedrive\Resources;

use Devio\Pipedrive\Http\Response;
use Devio\Pipedrive\Resources\Basics\Entity;
use Devio\Pipedrive\Resources\Traits\Searches;
use Devio\Pipedrive\Resources\Traits\ListsProducts;
use Devio\Pipedrive\Resources\Traits\ListsAttachedFiles;

class Deals extends Entity
{
    use ListsProducts, ListsAttachedFiles, Searches;

    /**
     * 
     * Get the deals summary
     * 
     * @param array $options
     * 
     * @return Response
     * 
    */

    public function summary($options = [])
    {

        return $this->request->get('summary', $options);

    }

    /**
     * Get the deals timeline.
     *
     * @param string $start_date
     * @param string $interval
     * @param int    $amount
     * @param string $field_key
     * @param array  $options
     * @return Response
     */
    public function timeline($start_date, $interval, $amount, $field_key, $options = [])
    {
        $options = array_merge(
            compact('start_date', 'interval', 'amount', 'field_key'),
            $options
        );

        return $this->request->get('timeline', $options);
    }

    /**
     * Add a participant to a deal.
     *
     * @param int $id
     * @param int $person_id
     * @return Response
     */
    public function addParticipant($id, $person_id)
    {
        return $this->request->post(':id/participants', compact('id', 'person_id'));
    }

    /**
     * Get the participants of a deal.
     *
     * @param int   $id
     * @param array $options
     * @return Response
     */
    public function participants($id, $options = [])
    {
        array_set($options, 'id', $id);

        return $this->request->get(':id/participants', $options);
    }

    /**
     * Delete a participant from a deal.
     *
     * @param int $id
     * @param int $deal_participant_id
     * @return Response
     */
    public function deleteParticipant($id, $deal_participant_id)
    {
        return $this->request->delete(':id/participants/:deal_participant_id', compact('id', 'deal_participant_id'));
    }

    /**
     * Add a product to the deal.
     *
     * @param int   $id
     * @param int   $product_id
     * @param       $item_price
     * @param int   $quantity
     * @param array $options
     * @return Response
     */
    public function addProduct($id, $product_id, $item_price, $quantity, $options = [])
    {
        $options = array_merge(
            compact('id', 'product_id', 'item_price', 'quantity'),
            $options
        );

        return $this->request->post(':id/products', $options);
    }

    /**
     * Update the details of an attached product.
     *
     * @param int   $id
     * @param int   $deal_product_id
     * @param       $item_price
     * @param int   $quantity
     * @param array $options
     * @return Response
     */
    public function updateProduct($id, $deal_product_id, $item_price, $quantity, $options = [])
    {
        $options = array_merge(
            compact('id', 'deal_product_id', 'item_price', 'quantity'),
            $options
        );

        return $this->request->put(':id/products/:deal_product_id', $options);
    }

    /**
     * Delete an attached product from a deal.
     *
     * @param int $id
     * @param int $product_attachment_id
     * @return Response
     */
    public function deleteProduct($id, $product_attachment_id)
    {
        return $this->request->delete(
            ':id/products/:product_attachment_id',
            compact('id', 'product_attachment_id')
        );
    }

    /**
     * Duplicate a deal.
     *
     * @param int $id The deal id
     * @return Response
     */
    public function duplicate($id)
    {
        return $this->request->post(':id/duplicate', compact('id'));
    }
    
    /**
     * Get the email messages for a deal.
     * 
     * @param int $id The deal id
     * @return Response
     */
    public function mailMessages($id)
    {
        return $this->request->get(':id/mailMessages', compact('id'));
    }

    /**
     * Get the files for a deal.
     *
     * @param int $id The deal id
     * @return Response
     */
    public function files($id)
    {
        return $this->request->get(':id/files', compact('id'));
    }
}
