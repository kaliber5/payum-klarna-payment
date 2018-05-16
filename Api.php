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
     * @param array $fields
     *
     * @return string json the body of the response
     */
    public function createSession(array $fields): string
    {
        if ($this->options['debug']) {
            VarDumper::dump($fields);
        }

        $response = $this->doRequest(self::URL_PAYMENTS_SESSIONS, 'POST', $fields);
        if ($response->getStatusCode() !== 200)
        {
            throw new \RuntimeException('Wrong StatusCode: '.$response->getStatusCode());
        }

        if ($this->options['debug']) {
            VarDumper::dump(json_decode($response->getBody(), true));
        }

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

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? 'https://api.playground.klarna.com/' : 'https://api.klarna.com/';
    }
}
