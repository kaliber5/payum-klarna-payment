<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Request\CreateSession;

/**
 * Class CreateSessionAction
 */
class CreateSessionAction extends BaseApiAwareAction
{
    /**
     * @param CreateSession $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(
            [
                'purchase_country',
                'purchase_currency',
                'locale',
                'order_amount',
                'order_lines',
            ]
        );
        $model->validatedKeysSet(['order_tax_amount']);
        $orderLines = $model['order_lines'];
        foreach ($orderLines as $orderLine) {
            $order = ArrayObject::ensureArrayObject($orderLine);
            $order->validateNotEmpty(
                [
                    'name',
                    'quantity',
                    'unit_price',
                    'total_amount',
                ]
            );
        }

        $response = $this->getApi()->createSession((array) $model);

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof CreateSession;
    }

}