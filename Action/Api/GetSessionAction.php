<?php

namespace Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Action\Api\BaseApiAwareAction;
use Payum\Klarna\Payment\Request\GetSession;

/**
 * Class GetSessionAction
 */
class GetSessionAction extends BaseApiAwareAction
{
    /**
     * @param GetSession $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(
            [
                'session_id',
            ]
        );

        $response = $this->getApi()->getSession($model->get('session_id'));

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof GetSession;
    }
}