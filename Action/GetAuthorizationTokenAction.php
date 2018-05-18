<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;
use Payum\Klarna\Payment\Request\CreateSession;
use Payum\Klarna\Payment\Request\GetAuthorizationToken;

class GetAuthorizationTokenAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if (($getHttpRequest->method !== 'POST' || isset($getHttpRequest->request['authorization_token'])) === false) {
            throw new HttpRedirect($request->getToken()->getTargetUrl());
        }
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->replace(['authorization_token' => $getHttpRequest->request['authorization_token']]);
        $model->validateNotEmpty('authorization_token');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetAuthorizationToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
