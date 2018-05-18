<?php

namespace Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Payment\Request\GetOrder;

/**
 * Class GetOrderAction
 */
class GetOrderAction extends BaseApiAwareAction
{
    /**
     * @param GetOrder $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(
            [
                'order_id',
            ]
        );

        $response = $this->getApi()->getOrder($model->get('order_id'));

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof GetOrder;
    }
}