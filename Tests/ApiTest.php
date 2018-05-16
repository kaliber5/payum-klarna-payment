<?php

namespace Payum\Klarna\Payment\Tests;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\HttpClientInterface;
use Payum\Klarna\Payment\Api;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 */
class ApiTest extends TestCase
{

    /**
     * @test
     */
    public function shouldCreateSession()
    {
        $api = $this->getApi();

        $fields = [
            'purchase_country' => 'DE',
            'purchase_currency' => 'EUR',
            'locale' => 'de-DE',
            'order_amount' => 1000,
            'order_tax_amount' => 0,
            'order_lines' => [
                [
                    'name' => 'testitem',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'total_amount' => 1000,
                ]
            ]
        ];

        $response = $api->createSession($fields);

        $this->assertArrayHasKey('session_id', $response);
    }

    protected function getApi(): Api
    {
        return new Api(
                [
                    'merchant_id' => 'PK02410_c529686a8f47',
                    'secret'      => 'lW2jDIOeVbJj3rPd',
                    'sandbox'     => true,
                    'debug'       => true,
                ],
                $this->createHttpClientMock(),
                $this->createHttpMessageFactory()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return HttpClientFactory::create();
//        return $this->createMock(HttpClientInterface::class);
    }

    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }
}