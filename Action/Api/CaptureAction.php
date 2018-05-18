<?php

namespace Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Payment\Request\Capture;

/**
 * Class CaptureAction
 */
class CaptureAction extends BaseApiAwareAction
{
    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['order_id', 'captured_amount']);

        $response = $this->getApi()->captureOrder($model->get('order_id'), (array) $model);

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof Capture;
    }
}