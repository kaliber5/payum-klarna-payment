<?php
namespace Payum\Klarna\Payment;

use Http\Client\HttpClient;
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
     * @param array $fields
     *
     * @return string json the body of the response
     */
    public function createCustomerToken($authorizationToken, array $fields): string
    {
        $response = $this->doRequest(self::URL_PAYMENTS_AUTHORIZATIONS.'/'.$authorizationToken.'/customer-token', 'POST', $fields);
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
     * Creates a Session
     *
     * @param array $fields
     *
     * @return string json the body of the response
     */
    public function updateSession(string $session_id, array $fields): string
    {
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS.'/'.$session_id, 'POST', $fields);
        $this->assertResponseStatus($response, 204);

        return $response->getBody();
    }

    /**
     * Retrieve a Session
     *
     * @param string the session id
     *
     * @return string json the body of the response
     */
    public function getSession(string $session_id): string
    {
        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS.'/'.$session_id, 'POST', $fields);
        $this->assertResponseStatus($response, 200);

        return $response->getBody();
    }

    /**
     * @param array $fields
     *
     * @return ResponseInterface
     */
    protected function doRequest($url, $method, array $fields): ResponseInterface
    {
        $headers = [];
        $headers['Authorization'] = 'Basic '.base64_encode($this->options['merchant_id'].':'.$this->options['secret']);
        $headers['Content-Type'] = 'application/json';

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint().$url, $headers, json_encode($fields));

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
