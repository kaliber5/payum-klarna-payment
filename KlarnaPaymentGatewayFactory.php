<?php
namespace Payum\Klarna\Payment;

use Payum\Klarna\Payment\Action\AuthorizeAction;
use Payum\Klarna\Payment\Action\CancelAction;
use Payum\Klarna\Payment\Action\ConvertPaymentAction;
use Payum\Klarna\Payment\Action\CaptureAction;
use Payum\Klarna\Payment\Action\NotifyAction;
use Payum\Klarna\Payment\Action\RefundAction;
use Payum\Klarna\Payment\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

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
//            'payum.action.refund' => new RefundAction(),
//            'payum.action.cancel' => new CancelAction(),
//            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
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
