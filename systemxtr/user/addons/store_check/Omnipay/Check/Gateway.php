<?php

namespace Omnipay\Check;

use Omnipay\Manual\Gateway as ManualGateway;

/**
 * Store Check payment gateway
 *
 * This is an example of a custom gateway. It simply extends the existing
 * Omnipay Manual payment gateway.
 *
 * For more information about developing custom gateways, please see
 * https://github.com/omnipay/omnipay
 */
class Gateway extends ManualGateway
{
    public function getName()
    {
        return 'Check';
    }
	
    public function getDefaultParameters()
    {
        return array(
            'adjustment' => '',
        );
    }
    public function getAdjustment()
    {
        return $this->getParameter('adjustment');
    }

    public function setAdjustment($value)
    {
        return $this->setParameter('adjustment', $value);
    }
	
}
