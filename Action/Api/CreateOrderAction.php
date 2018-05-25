<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Request\CreateOrder;

/**
 * Class CreateOrderAction
 */
class CreateOrderAction extends BaseApiAwareAction
{
    /**
     * @param CreateOrder $request
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
              'order_tax_amount',
              'order_lines',
          ]
        );

        $orderLines = ArrayObject::ensureArrayObject($model->get('order_lines'));
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

        if ($model->validateNotEmpty(['token_id'], false)) {
            $response = $this->getApi()->createOrderByCustomerToken($model->get('token_id'), (array) $model);
        } else {
            $model->validateNotEmpty(['authorization_token']);
            $response = $this->getApi()->createOrderByAuthToken($model->get('authorization_token'), (array) $model);
        }

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof CreateOrder;
    }
}