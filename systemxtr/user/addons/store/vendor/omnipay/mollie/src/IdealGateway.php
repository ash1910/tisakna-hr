<?php

namespace Omnipay\Mollie;

/**
 * Mollie iDeal Gateway
 */
class IdealGateway extends Gateway
{
    public function getName()
    {
        return 'Mollie - iDeal';
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Mollie\Message\IdealPurchaseRequest', $parameters);
    }
}