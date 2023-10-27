<?php

namespace Omnipay\FirstData;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidCreditCardException;

/**
 * Credit Card class
 */
class Check extends CreditCard
{
    protected $checkTypes = array(
        'personal' => 'P',
        'corporate' => 'c',
    );

    protected $idTypes = array(
        'drivers_license' => 0,
        'ssn' => 1,
        'tax_id' => 2,
        'military_id' => 3,
    );

    public function getCheckNumber()
    {
        return $this->getParameter('check_number');
    }

    public function setCheckNumber($value)
    {
        return $this->setParameter('check_number', $value);
    }

    public function getCheckType()
    {
        $type = $this->getParameter('check_type');

        if (!$type) return $type;

        if (!isset($this->checkTypes[$type])) {
            return 'ERROR';
        }

        return $this->checkTypes[$type];
    }

    public function setCheckType($value)
    {
        return $this->setParameter('check_type', $value);
    }

    public function getCustomerName()
    {
        return $this->getParameter('customer_name');
    }

    public function setCustomerName($value)
    {
        return $this->setParameter('customer_name', $value);
    }

    public function getCustomerIdType()
    {
        $type = $this->getParameter('customer_id_type');

        if (!$type) return $type;

        if (!isset($this->idTypes[$type])) {
            return 'ERROR';
        }

        return $this->idTypes[$type];
    }

    public function setCustomerIdType($value)
    {
        return $this->setParameter('customer_id_type', $value);
    }

    public function getCustomerIdNumber()
    {
        return $this->getParameter('customer_id_number');
    }

    public function setCustomerIdNumber($value)
    {
        return $this->setParameter('customer_id_number', $value);
    }

    public function getBankAccountNumber()
    {
        return $this->getParameter('bank_account_number');
    }

    public function setBankAccountNumber($value)
    {
        return $this->setParameter('bank_account_number', $value);
    }

    public function getBankRoutingNumber()
    {
        return $this->getParameter('bank_routing_number');
    }

    public function setBankRoutingNumber($value)
    {
        return $this->setParameter('bank_routing_number', $value);
    }

    public function validate()
    {
        foreach (array('check_number', 'check_type', 'customer_id_number', 'bank_account_number', 'bank_routing_number') as $key) {
            if (!$this->getParameter($key)) {
                throw new InvalidCreditCardException("The $key parameter is required");
            }
        }

        if ($this->getCheckType() === 'ERROR') {
            throw new InvalidCreditCardException("Invalid Check Type");
        }

        if ($this->getCustomerIdType() === 'ERROR') {
            throw new InvalidCreditCardException("Invalid Customer ID Type");
        }
    }

}
