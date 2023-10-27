<?php

namespace Omnipay\FirstData\Message;
use Omnipay\FirstData\Check;

class TelecheckPurchaseRequest extends GlobalAbstractRequest
{


	public function setCheck($value)
    {
        if ($value && !$value instanceof Check) {
            $value = new Check($value);
        }

        return $this->setParameter('check', $value);
    }

    public function getCheck()
    {
        return $this->getParameter('check');
    }




	public function getData()
	{
		$this->setTransactionType(GlobalAbstractRequest::TRAN_PURCHASE);
		$data = $this->getBaseData('DoDirectPayment');

		$this->validate('amount', 'check');

		$check = $this->getCheck();
		$check->validate();

		$data['currency_code'] = $this->getCurrency();
		$data['reference_no'] = $this->getTransactionId();

		// Required Fields
		$data['amount'] = $this->getAmount();
		$data['check_number'] = $check->getCheckNumber();
		$data['check_type'] = $check->getCheckType();
		$data['cardholder_name'] = $check->getCustomerName() ?: $this->getCard()->getBillingName();
		$data['account_number'] = $check->getBankAccountNumber();
		$data['bank_id'] = $check->getBankRoutingNumber();
		$data['customer_id_type'] = $check->getCustomerIdType();
		$data['customer_id_number'] = $check->getCustomerIdNumber();
		$data['client_email'] = $this->getCard()->getEmail();
		$data['address'] = array();
		$data['address']['address1'] = $this->getCard()->getAddress1();
		$data['address']['address2'] = $this->getCard()->getAddress2();
		$data['address']['city'] = $this->getCard()->getCity();
		$data['address']['state'] = $this->getCard()->getState();
		$data['address']['zip'] = $this->getCard()->getPostcode();
		$data['address']['country_code'] = strtoupper($this->getCard()->getCountry());

		$data['client_ip'] = $this->getClientIp();
		$data['language'] = 'EN';


		/*
		$data['release_type']
		$data['gift_card_amount']
		$data['date_of_birth'] /// MMDDYYYY
		$data['vip']
		$data['registration_date']
		$data['clerk_id']
		$data['device_id']
		$data['micr']
		*/

		return $data;
	}
}