<?php

namespace Omnipay\Sisow;

use Omnipay\Common\AbstractGateway;

/**
 * Sisow Gateway
 *
 * @link http://www.sisow.nl/downloads/REST321.pdf
 */
class Gateway extends AbstractGateway
{

    public function getName()
    {
        return 'Sisow';
    }

    public function getDefaultParameters()
    {
        return array(
            'shopid' => '',
            'merchantid' => '',
            'testmode' => false
        );
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantkey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantkey', $value);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantid');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantid', $value);
    }

    public function fetchPaymentMethods(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Sisow\Message\FetchPaymentMethodsRequest', $parameters);
    }

    public function fetchIssuers(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Sisow\Message\FetchIssuersRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Sisow\Message\PurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Sisow\Message\CompletePurchaseRequest', $parameters);
    }
}
