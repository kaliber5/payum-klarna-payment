<?php
namespace Payum\Klarna\Payment;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\VarDumper\VarDumper;

class Api
{
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
        $response = $this->doRequest(self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authorizationToken.'/customer-token', 'POST', $fields);
        $this->assertResponseStatus($response, 200);

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
        $response = $this->doRequest(
            self::URL_CUSTOMERTOKEN_TOKENS.'/'.$customerToken.'/order',
            'POST',
            $fields
        );
        $this->assertResponseStatus($response, 200);

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
        $response = $this->doRequest(
            self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authorizationToken.'/order',
            'POST',
            $fields
        );
        $this->assertResponseStatus($response, 200);

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
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS, 'POST', $fields);
        $this->assertResponseStatus($response, 200);

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
        $response = $this->doRequest(self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authToken, 'DELETE');
        $this->assertResponseStatus($response, 204);
    }

    /**
     * updates a Session
     *
     * @param string $sessionId
     * @param array  $fields
     */
    public function updateSession(string $sessionId, array $fields): void
    {
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS.'/'.$sessionId, 'POST', $fields);
        $this->assertResponseStatus($response, 204);
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
        $this->assertResponseStatus($response, 200);

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
        $this->assertResponseStatus($response, 200);

        return $response->getBody();
    }

    /**
     * @param string $orderId
     * @param array  $fields
     *
     * @return string
     */
    public function captureOrder(string $orderId, array $fields): viod
    {
        $response = $this->doRequest(self::URL_ORDERMANAGEMENT_ORDERS.'/'.$orderId.'/captures', 'POST', $fields);
        $this->assertResponseStatus($response, 201);
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

        if ($this->options['debug']) {
            VarDumper::dump($request);
        }
        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            if ($this->options['debug']) {
                VarDumper::dump($response);
            }

            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    protected function assertResponseStatus(ResponseInterface $response, $statusCodes)
    {
        if (!is_array($statusCodes)) {
            $statusCodes = [$statusCodes];
        }

        if (!in_array($response->getStatusCode(), $statusCodes))
        {
            throw new \RuntimeException('Wrong StatusCode: '.$response->getStatusCode());
        }
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://api.playground.klarna.com/' : 'https://api.klarna.com/';
    }
}
