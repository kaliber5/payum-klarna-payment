<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
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

        $this->getApi()->captureOrder($model->get('order_id'), (array) $model);
    }

    public function supports($request)
    {
        return $request instanceof Capture;
    }
}