<?php

namespace Omnipay\FirstData\Message;

class GlobalPurchaseRequest extends GlobalAbstractRequest
{
	public function getData()
	{
		$this->setTransactionType(GlobalAbstractRequest::TRAN_PURCHASE);
		$data = $this->getBaseData('DoDirectPayment');

		$this->validate('amount', 'card');
		$this->getCard()->validate();

		$data['amount'] = $this->getAmount();
		$data['currency_code'] = $this->getCurrency();
		$data['reference_no'] = $this->getTransactionId();

		// add credit card details
		$data['credit_card_type'] = self::get_card_type($this->getCard()->getBrand());
		$data['cc_number'] = $this->getCard()->getNumber();
		$data['cardholder_name'] = $this->getCard()->getName();
		$data['cc_expiry'] = $this->getCard()->getExpiryDate('my');
		$data['cvd_code'] = $this->getCard()->getCvv();
		$data['cvd_presence_ind'] = '1';
		//$data['cavv'] = $this->getCard()->getCvv();

		$data['client_ip'] = $this->getClientIp();
		$data['client_email'] = $this->getCard()->getEmail();
		$data['language'] = strtoupper($this->getCard()->getCountry());

		$data['address'] = array();
		$data['address']['address1'] = $this->getCard()->getAddress1();
		$data['address']['city'] = $this->getCard()->getCity();
		$data['address']['zip'] = $this->getCard()->getPostcode();
		$data['address']['country'] = $this->getCard()->getCountry();

		//$data['STARTDATE'] = $this->getCard()->getStartMonth().$this->getCard()->getStartYear();
		//$data['FIRSTNAME'] = $this->getCard()->getFirstName();
		//$data['LASTNAME'] = $this->getCard()->getLastName();
		//$data['DESC'] = $this->getDescription();
		//$data['STREET'] = $this->getCard()->getAddress1();
		//$data['STREET2'] = $this->getCard()->getAddress2();
		//$data['city'] = $this->getCard()->getCity();
		//$data['state'] = $this->getCard()->getState();
		//$data['zip_code'] = $this->getCard()->getPostcode();
		//$data['country'] = strtoupper($this->getCard()->getCountry());
		//var_dump($data);
		//die();
		return $data;
	}
}