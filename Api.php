<?php
namespace Payum\Klarna\Payment;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class Api
 *
 * @package Payum\Klarna\Payment
 */
class Api implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    const URL_PAYMENTS_SESSIONS = 'payments/v1/sessions';

    const URL_PAYMENTS_AUTHORIZATIONS = 'payments/v1/authorizations';

    const URL_ORDERMANAGEMENT_ORDERS = 'ordermanagement/v1/orders';

    const URL_CUSTOMERTOKEN_TOKENS = 'customer-token/v1/tokens';

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Creates a Customer Token
     *
     * @param string $authorizationToken
     * @param array  $fields
     *
     * @return string json the body of the response
     */
    public function createCustomerToken(string $authorizationToken, array $fields): string
    {
        $fields = $this->filterFields(
            $fields,
            [
                'purchase_country',
                'purchase_currency',
                'locale',
                'billing_address',
                'customer',
                'description',
                'intended_use',
            ]
        );
        $response = $this->doRequest(self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authorizationToken.'/customer-token', 'POST', $fields);

        return $response->getBody()->getContents();
    }

    /**
     * Creates an Order by customer token
     *
     * @param string $customerToken
     * @param array  $fields
     *
     * @return string json the body of the response
     */
    public function createOrderByCustomerToken(string $customerToken, array $fields): string
    {
        $fields = $this->filterFields($fields, [
            'attachment',
            'auto_capture',
            'customer_token_order_merchant_urls',
            'merchant_data',
            'merchant_reference1',
            'merchant_reference2',
            'order_amount',
            'order_lines',
            'order_tax_amount',
            'purchase_currency',
            'shipping_address'
        ]);

        $response = $this->doRequest(
            self::URL_CUSTOMERTOKEN_TOKENS.'/'.$customerToken.'/order',
            'POST',
            $fields
        );

        return $response->getBody()->getContents();
    }

    /**
     * Creates an Order by auth token
     *
     * @param string $authorizationToken
     * @param array  $fields
     *
     * @return string json the body of the response
     */
    public function createOrderByAuthToken(string $authorizationToken, array $fields): string
    {
        $fields = $this->filterFields(
            $fields,
            [
                'design',
                'purchase_country',
                'purchase_currency',
                'locale',
                'billing_address',
                'shipping_address',
                'order_amount',
                'order_tax_amount',
                'order_lines',
                'customer',
                'merchant_urls',
                'merchant_reference1',
                'merchant_reference2',
                'merchant_data',
                'options',
                'attachment',
                'custom_payment_method_ids',
                'status',
                'client_token',
                'expires_at',
                'acquiring_channel',
                'auto_capture',
            ]
        );

        $response = $this->doRequest(
            self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authorizationToken.'/order',
            'POST',
            $fields
        );

        return $response->getBody()->getContents();
    }


    /**
     * Creates a Session
     *
     * @param array $fields
     *
     * @return string json the body of the response
     */
    public function createSession(array $fields): string
    {
        $fields = $this->filterFields(
            $fields,
            [
                'design',
                'purchase_country',
                'purchase_currency',
                'locale',
                'billing_address',
                'shipping_address',
                'order_amount',
                'order_tax_amount',
                'order_lines',
                'customer',
                'merchant_urls',
                'merchant_reference1',
                'merchant_reference2',
                'merchant_data',
                'options',
                'attachment',
                'custom_payment_method_ids',
                'status',
                'client_token',
                'expires_at',
                'acquiring_channel',
                'payment_method_categories',
            ]
        );
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS, 'POST', $fields);

        return $response->getBody()->getContents();
    }

    /**
     * Deletes an authorization token
     *
     * @param string $authToken
     *
     */
    public function deleteAuthToken(string $authToken): void
    {
        $this->doRequest(self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authToken, 'DELETE');
    }

    /**
     * updates a Session
     *
     * @param string $sessionId
     * @param array  $fields
     */
    public function updateSession(string $sessionId, array $fields): void
    {
        $fields = $this->filterFields(
            $fields,
            [
                'design',
                'purchase_country',
                'purchase_currency',
                'locale',
                'billing_address',
                'shipping_address',
                'order_amount',
                'order_tax_amount',
                'order_lines',
                'customer',
                'merchant_urls',
                'merchant_reference1',
                'merchant_reference2',
                'merchant_data',
                'options',
                'attachment',
                'custom_payment_method_ids',
                'status',
                'client_token',
                'expires_at',
                'acquiring_channel',
                'payment_method_categories',
            ]
        );
        $this->doRequest(self::URL_PAYMENTS_SESSIONS.'/'.$sessionId, 'POST', $fields);
    }

    /**
     * Retrieve a Session
     *
     * @param string $sessionId the session id
     *
     * @return string json the body of the response
     */
    public function getSession(string $sessionId): string
    {
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS.'/'.$sessionId, 'GET');

        return $response->getBody();
    }

    /**
     * Retrieve an Order
     *
     * @param string $orderId the order id
     *
     * @return string json the body of the response
     */
    public function getOrder(string $orderId): string
    {
        $response = $this->doRequest(self::URL_ORDERMANAGEMENT_ORDERS.'/'.$orderId, 'GET');

        return $response->getBody();
    }

    /**
     * @param string $orderId
     * @param array  $fields
     *
     * @return string
     */
    public function captureOrder(string $orderId, array $fields): void
    {
        $fields = $this->filterFields(
            $fields,
            [
                'captured_amount',
                'description',
                'order_lines',
                'shipping_info',
                'shipping_delay',
            ]
        );

        $this->doRequest(self::URL_ORDERMANAGEMENT_ORDERS.'/'.$orderId.'/captures', 'POST', $fields);
    }

    /**
     * @param array $fields
     *
     * @return ResponseInterface
     */
    protected function doRequest($url, $method, array $fields = null): ResponseInterface
    {
        $headers = [];
        $headers['Authorization'] = 'Basic '.base64_encode($this->options['merchant_id'].':'.$this->options['secret']);
        $headers['Content-Type'] = 'application/json';


        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint().$url, $headers, @json_encode($fields));

        if ($this->options['debug'] && $this->logger) {
            $this->logger->debug(print_r($fields, true));
        }
        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            if ($this->options['debug'] && $this->logger) {
                $this->logger->debug($response->getBody());
            }

            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * removes not allowed fields from array
     *
     * @param array $fields
     * @param array $allowed
     *
     * @return array
     */
    protected function filterFields(array $fields, array $allowed): array
    {
        return array_filter($fields, function($key) use ($allowed) {
            return in_array($key, $allowed);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://api.playground.klarna.com/' : 'https://api.klarna.com/';
    }
}
