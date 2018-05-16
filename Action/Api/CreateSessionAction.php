<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Klarna\Payment\Request\CreateSession;

/**
 * Class CreateSessionAction
 */
class CreateSessionAction extends BaseApiAwareAction
{
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $response = $this->getApi()->createSession($model);

        throw new HttpResponse($response);

    }

    public function supports($request)
    {
        return $request instanceof CreateSession;
    }

}