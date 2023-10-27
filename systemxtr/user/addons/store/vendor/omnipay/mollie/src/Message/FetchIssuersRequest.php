<?php

namespace Omnipay\Mollie\Message;

/**
 * Mollie Fetch Issuers Request
 *
 * @method \Omnipay\Mollie\Message\FetchIssuersResponse send()
 */
class FetchIssuersRequest extends AbstractRequest
{
    /**
     * @return null
     */
    public function getData()
    {
        $apiStr = $this->getTestMode() ? 'testApiKey' : 'apiKey';
        $this->validate($apiStr);
    }

    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('GET', '/issuers');

        return $this->response = new FetchIssuersResponse($this, $httpResponse->json());
    }
}
