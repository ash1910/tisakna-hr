<?php

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * PayPal Response
 */
class GlobalResponse extends AbstractResponse
{
	public function isSuccessful()
	{
		return ($this->data['transaction_approved'] == '1') ? true : false;
	}

	public function getTransactionReference()
	{
		return $this->data['reference_no'];
	}

	public function getMessage()
	{
		return $this->data['exact_message'];
	}
	public function getCode()
    {
        return $this->data['exact_resp_code'];
    }
}