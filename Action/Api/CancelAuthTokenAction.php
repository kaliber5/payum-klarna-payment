<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Request\CancelAuthToken;
use Webmozart\Assert\Assert;

/**
 * Class CancelAuthTokenAction
 */
class CancelAuthTokenAction extends BaseApiAwareAction
{
    /**
     * @param CancelAuthToken $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        Assert::string($request->getModel());
        $this->getApi()->deleteAuthToken($request->getModel());
    }

    public function supports($request)
    {
        return $request instanceof CancelAuthToken;
    }
}