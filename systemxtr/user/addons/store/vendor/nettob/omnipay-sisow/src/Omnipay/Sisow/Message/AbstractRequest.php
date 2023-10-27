<?php

namespace Omnipay\Sisow\Message;

use \Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;

/**
 * Sisow Abstract Request
 */
abstract class AbstractRequest extends BaseAbstractRequest
{

    protected $endpoint = 'https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/';

    public function getMerchantId()
    {
        return $this->getParameter('merchantid');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantid', $value);
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantkey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantkey', $value);
    }

    public function getIssuer()
    {
        return $this->getParameter('issuer');
    }

    public function setIssuer($value)
    {
        return $this->setParameter('issuer', $value);
    }

    public function getType()
    {
        return $this->getParameter('type');
    }

    public function setType($value)
    {
        return $this->setParameter('type', $value);
    }

    protected function getBaseData()
    {
        $data = array();

        if ($this->getTestMode()) {
            $data['test'] = 'true';
        }

        return $data;
    }

    public function sendData($data)
    {
        $url = $this->getEndpoint() . '?' . http_build_query($data);
        $httpResponse = $this->httpClient->get($url)->send();
        return $this->createResponse($httpResponse->xml());
    }

    protected function getEndpoint()
    {
        if ($this->getType()) {
            $this->endpoint .= $this->getType();
        }
        return $this->endpoint;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }
}
