<?php

namespace Omnipay\Mollie\Message;

/**
 * Mollie iDeal Purchase Request
 */
class IdealPurchaseRequest extends PurchaseRequest
{
    public function getData()
    {
        $data = parent::getData();
        $data['method'] = 'ideal';

        return $data;
    }
}
