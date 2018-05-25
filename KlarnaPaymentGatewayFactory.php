<?php
namespace Payum\Klarna\Payment;

use Payum\Klarna\Payment\Action\Api\CancelAuthTokenAction;
use Payum\Klarna\Payment\Action\Api\CreateOrderAction;
use Payum\Klarna\Payment\Action\Api\GetOrderAction;
use Payum\Klarna\Payment\Action\Api\GetSessionAction;
use Payum\Klarna\Payment\Action\Api\CreateCustomerTokenAction;
use Payum\Klarna\Payment\Action\Api\CreateSessionAction;
use Payum\Klarna\Payment\Action\AuthorizeAction;
use Payum\Klarna\Payment\Action\CancelAction;
use Payum\Klarna\Payment\Action\ConvertPaymentAction;
use Payum\Klarna\Payment\Action\CaptureAction;
use Payum\Klarna\Payment\Action\GetAuthorizationTokenAction;
use Payum\Klarna\Payment\Action\NotifyAction;
use Payum\Klarna\Payment\Action\RefundAction;
use Payum\Klarna\Payment\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Klarna\Payment\Action\SyncAction;

class KlarnaPaymentGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'klarna_payment',
            'payum.factory_title' => 'Klarna Payment',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.get_authorization_token' => new GetAuthorizationTokenAction(),
//            'payum.action.refund' => new RefundAction(),
//            'payum.action.notify' => new NotifyAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.cancel_auth_token' => new CancelAuthTokenAction(),
            'payum.action.api.capture' => new \Payum\Klarna\Payment\Action\Api\CaptureAction(),
            'payum.action.api.create_customer_token' => new CreateCustomerTokenAction(),
            'payum.action.api.create_order' => new CreateOrderAction(),
            'payum.action.api.create_session' => new CreateSessionAction(),
            'payum.action.api.get_order' => new GetOrderAction(),
            'payum.action.api.get_session' => new GetSessionAction(),

            'sandbox' => true,
            'debug' => false,
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'merchant_id'  => '',
                'secret'       => '',
                'terms_uri'    => '',
                'checkout_uri' => '',
                'sandbox' => true,
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['merchant_id', 'secret'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
