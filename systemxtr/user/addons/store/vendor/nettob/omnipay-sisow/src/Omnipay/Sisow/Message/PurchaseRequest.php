<?php

namespace Omnipay\Sisow\Message;

/**
 * Sisow Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{

    public function getPayment()
    {
        return $this->getParameter('payment');
    }

    public function setPayment($value)
    {
        return $this->setParameter('payment', $value);
    }

    public function getIssuerId()
    {
        return $this->getParameter('issuerid');
    }

    public function setIssuerId($value)
    {
        return $this->setParameter('issuerid', $value);
    }

    public function getEntranceCode()
    {
        return $this->getParameter('entrancecode');
    }

    public function setEntranceCode($value)
    {
        return $this->setParameter('entrancecode', $value);
    }

    public function getCallbackUrl()
    {
        return $this->getParameter('callbackurl');
    }

    public function setCallbackUrl($value)
    {
        return $this->setParameter('callbackurl', $value);
    }

    protected function generateSignature()
    {
        return sha1(
            $this->getTransactionId() .
            $this->getEntranceCode() .
            $this->getAmountInteger() .
            $this->getMerchantId() .
            $this->getMerchantKey()
        );
    }

    public function getData()
    {
        $this->validate(
            'merchantid',
            'merchantkey',
            'amount',
            'transactionId',
            'returnUrl',
            'notifyUrl',
            'entrancecode'
        );
        $this->setType('TransactionRequest');
        $data = $this->getBaseData();
        $data['merchantid'] = $this->getMerchantId();
        $data['merchantkey'] = $this->getMerchantKey();
        $data['payment'] = $this->getPayment();
        $data['purchaseid'] = $this->getTransactionId();
        $data['amount'] = $this->getAmountInteger();
        $data['issuerid'] = $this->getIssuerId();
        $data['entrancecode'] = $this->getEntranceCode();
        $data['description'] = $this->getDescription();
        $data['returnurl'] = $this->getReturnUrl();
        $data['cancelurl'] = $this->getCancelUrl();
        $data['callbackurl'] = $this->getCallbackUrl();
        $data['notifyurl'] = $this->getNotifyUrl();
        $data['sha1'] = $this->generateSignature();
        return $data;
    }
}
