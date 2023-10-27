<?php

namespace Omnipay\FirstData\Message;

use Omnipay\FirstData\Check;
use Omnipay\Common\CreditCard;

/**
 * First Data Abstract Request
 */
abstract class GlobalAbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
	protected $liveEndpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v14';
	protected $testEndpoint = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v14';

	/**
	 * @var int - api transaction type
	 */
	protected $transactionType = '00';
	/**
	 * Transaction types
	 */
	const TRAN_PURCHASE = '00';
	const TRAN_PREAUTH = '01';
	const TRAN_PREAUTHCOMPLETE = '02';
	const TRAN_FORCEDPOST = '03';
	const TRAN_REFUND = '04';
	const TRAN_PREAUTHONLY = '05';
	const TRAN_PAYPALORDER = '07';
	const TRAN_VOID = '13';
	const TRAN_TAGGEDPREAUTHCOMPLETE = '32';
	const TRAN_TAGGEDVOID = '33';
	const TRAN_TAGGEDREFUND = '34';
	const TRAN_CASHOUT = '83';
	const TRAN_ACTIVATION = '85';
	const TRAN_BALANCEINQUIRY = '86';
	const TRAN_RELOAD = '88';
	const TRAN_DEACTIVATION = '89';

	public function getGatewayid()
	{
		return $this->getParameter('gatewayid');
	}

	public function setGatewayID($value)
	{
		return $this->setParameter('gatewayid', $value);
	}

	public function getPassword()
	{
		return $this->getParameter('password');
	}

	public function setPassword($value)
	{
		return $this->setParameter('password', $value);
	}

	public function getHmacKey()
    {
        return $this->getParameter('hmac_key');
    }

    public function setHmacKey($value)
    {
        return $this->setParameter('hmac_key', $value);
    }

	/**
	 * Set transaction type
	 * @param int $transactionType
	 * @return object
	 */
	public function setTransactionType($transactionType)
	{
		$this->transactionType = $transactionType;
		return $this;
	}
	/**
	 * Return transaction type
	 * @return int
	 */
	public function getTransactionType()
	{
		return $this->transactionType;
	}

	protected function getBaseData($method)
	{
		$data = array();
		$data['gateway_id'] = $this->getGatewayID();
		$data['password'] = $this->getPassword();
		$data['transaction_type'] = $this->getTransactionType();

		return $data;
	}

	protected function getHeaders($data)
	{
		$gatewayId = $this->getGatewayid();
		$hmacKey = $this->getHmacKey();
		$content_type = 'application/json';
		$hashtime = gmdate('c');
		$content_digest = sha1(json_encode($data));
		$api_uri = '/transaction/v14';
		$hashstr = "POST\n" . $content_type . "\n" . $content_digest . "\n" . $hashtime . "\n" . $api_uri;
		$authstr = base64_encode(hash_hmac('sha1', $hashstr, $hmacKey, true));

		$headers = array();
		$headers['Content-Type'] = $content_type;
		$headers['Accept'] = 'application/json';
		$headers['Authorization'] = 'GGE4_API ' . $gatewayId . ':' . $authstr;
		$headers['X-GGe4-Date'] =  $hashtime;
		$headers['X-GGe4-Content-SHA1'] = $content_digest;

		return $headers;
	}

	public function sendData($data)
	{
		$client = $this->httpClient->post(
			$this->getEndpoint(),
			$this->getHeaders($data),
			json_encode($data)
		);

		$client->getCurlOptions()->set(CURLOPT_PORT,443);
		$httpResponse = $client->send();
		return $this->createResponse($httpResponse->json());
	}

	protected function getEndpoint()
	{
		return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
	}

	protected function createResponse($data)
	{
		return $this->response = new GlobalResponse($this, $data);
	}

	protected static $card_types = array(
		'visa' => 'Visa',
		'mastercard' => 'Mastercard',
		'discover' => 'Discover',
		'amex' => 'American Express',
		'diners_club' => 'Diners Club',
		'jcb' => 'JCB', //'Gift Card', 'PayPal'
	);
	public static function get_card_type($type){
		if(isset(self::$card_types[$type])){
			return self::$card_types[$type];
		}
		return $type;
	}
}