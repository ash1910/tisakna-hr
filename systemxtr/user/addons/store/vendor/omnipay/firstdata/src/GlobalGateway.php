<?php

namespace Omnipay\FirstData;

use Omnipay\Common\AbstractGateway;

/**
 * Global Gateway
 *
 * This gateway is useful for testing. It simply authorizes any payment made using a valid
 * credit card number and expiry.
 *
 * Any card number which passes the Luhn algorithm and ends in an even number is authorized,
 * for example: 4242424242424242
 *
 * Any card number which passes the Luhn algorithm and ends in an odd number is declined,
 * for example: 4111111111111111
 */
class GlobalGateway extends AbstractGateway
{
    public function getName()
    {
        return 'First Data - Global Gateway e4';
    }

    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\FirstData', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\GlobalPurchaseRequest', $parameters);
    }

    public function getDefaultParameters()
    {
        return array(
            'gatewayid' => '',
            'password' => '',
            'hmac_key' => '',
            'testMode' => false,
        );
    }

    public function getGatewayid()
    {
        return $this->getParameter('gatewayid');
    }

    public function setGatewayid($value)
    {
        return $this->setParameter('gatewayid', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getHmacKey()
    {
        return $this->getParameter('hmac_key');
    }

    public function setHmacKey($value)
    {
        return $this->setParameter('hmac_key', $value);
    }
}