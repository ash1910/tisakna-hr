<?php

namespace Omnipay\Mollie\Message;

use Guzzle\Common\Event;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $endpoint = 'https://api.mollie.nl/v1';

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * @return string
     */
    public function getTestApiKey()
    {
        return $this->getParameter('testApiKey');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTestApiKey($value)
    {
        return $this->setParameter('testApiKey', $value);
    }

    public function getTestMode()
    {
        return $this->getParameter('testMode');
    }

    public function setTestMode($value)
    {
        return $this->setParameter('testMode', $value);
    }

    protected function sendRequest($method, $endpoint, $data = null)
    {
        $this->httpClient->getEventDispatcher()->addListener('request.error', function (Event $event) {
            /**
             * @var \Guzzle\Http\Message\Response $response
             */
            $response = $event['response'];

            if ($response->isClientError()) {
                $event->stopPropagation();
            }
        });

        $apiKey = $this->getTestMode() ? $this->getTestApiKey() : $this->getApiKey();

        $httpRequest = $this->httpClient->createRequest(
            $method,
            $this->endpoint . $endpoint,
            array(
                'Authorization' => 'Bearer ' . $apiKey
            ),
            $data
        );

        return $httpRequest->send();
    }
}
