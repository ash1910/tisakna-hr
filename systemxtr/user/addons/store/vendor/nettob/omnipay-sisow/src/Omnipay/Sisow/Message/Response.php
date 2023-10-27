<?php

namespace Omnipay\Sisow\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Sisow Response
 */
class Response extends AbstractResponse implements RedirectResponseInterface
{

    public function isSuccessful()
    {
        if ($this->isRedirect()) {
            return false;
        }

        if (isset($this->data->transaction)) {
            if ($this->data->transaction->issuerurl) {
                return true;
            } elseif ((string) $this->data->transaction->status == 'Success') {
                return true;
            }
        } elseif (isset($this->data->directory)) {
            return true;
        } elseif (isset($this->data->merchant)) {
            if ((string) $this->data->merchant->active == 'true') {
                return true;
            }
        }

        return false;
    }

    public function isRedirect()
    {
        return isset($this->data->transaction->issuerurl);
    }

    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            return (string) urldecode($this->data->transaction->issuerurl);
        }
    }

    public function getRedirectMethod()
    {
        return 'GET';
    }

    public function getRedirectData()
    {
        return null;
    }

    public function getTransactionReference()
    {
        if (isset($this->data->transaction)) {
            return (string) $this->data->transaction->trxid;
        }
        return false;
    }

    public function getMessage()
    {
        if (isset($this->data->error)) {
            return (string) $this->data->error->errormessage;
        } elseif (isset($this->data->transaction->status)) {
            return (string) $this->data->transaction->status;
        }
    }

    public function getCode()
    {
        if (isset($this->data->error)) {
            return (string) $this->data->error->errorcode;
        }
    }

    /**
     * Get an associateive array of banks returned from a fetchIssuers request
     */
    public function getIssuers()
    {
        if (isset($this->data->directory)) {
            $issuers = array();

            foreach ($this->data->directory->issuer as $issuer) {
                $issuerid = (string) $issuer->issuerid;
                $issuername = (string) $issuer->issuername;
                $issuers[$issuerid] = (string) $issuername;
            }

            return $issuers;
        }
    }

    /**
     * Get an associateive array of payment methods returned from a fetchPaymentMethods request
     */
    public function getPaymentMethods()
    {
        if (isset($this->data->merchant->payments)) {
            $payments = array();

            foreach ($this->data->merchant->payments->payment as $payment) {
                $paymentname = (string) $payment;
                $payments[$paymentname] = $paymentname;
            }

            return $payments;
        }
    }
}
