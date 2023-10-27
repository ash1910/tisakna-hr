<?php

namespace Omnipay\Sisow\Message;

/**
 * Mollie Fetch Issuers Request
 */
class FetchIssuersRequest extends AbstractRequest
{

    public function getData()
    {
        $this->setType('DirectoryRequest');
        $data = $this->getBaseData();
        return $data;
    }
}
