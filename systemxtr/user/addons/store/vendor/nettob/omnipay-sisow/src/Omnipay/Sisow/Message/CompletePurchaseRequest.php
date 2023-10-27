<?php

namespace Omnipay\Sisow\Message;

class CompletePurchaseRequest extends PurchaseRequest
{

    protected function generateSignature()
    {
        return sha1(
            $this->getTransactionId() .
            $this->getMerchantId() .
            $this->getMerchantKey()
        );
    }

    public function getData()
    {
        $this->validate('merchantid', 'merchantkey', 'transactionId', 'entrancecode');

        $this->setType('StatusRequest');

        $data['merchantid'] = $this->getMerchantId();
        $data['merchantkey'] = $this->getMerchantKey();
        $data['trxid'] = $this->getTransactionId();
        $data['entrancecode'] = $this->getEntranceCode();
        $data['sha1'] = $this->generateSignature();
        return $data;
    }
}
