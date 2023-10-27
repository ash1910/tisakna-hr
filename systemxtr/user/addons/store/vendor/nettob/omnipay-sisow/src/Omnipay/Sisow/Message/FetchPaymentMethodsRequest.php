<?php

namespace Omnipay\Sisow\Message;

/**
 * Sisow Fetch Payment Methods Request
 */
class FetchPaymentMethodsRequest extends AbstractRequest
{

    protected function generateSignature()
    {
        return sha1(
            $this->getMerchantId() .
            $this->getMerchantKey()
        );
    }

    public function getData()
    {
        $this->validate('merchantid', 'merchantkey');
        $this->setType('CheckMerchantRequest');

        $data = $this->getBaseData();

        $data['merchantid'] = $this->getMerchantId();
        $data['merchantkey'] = $this->getMerchantKey();
        $data['sha1'] = $this->generateSignature();

        return $data;
    }
}
