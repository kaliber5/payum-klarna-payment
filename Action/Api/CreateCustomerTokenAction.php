<?php

namespace Payum\Klarna\Payment\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Payment\Request\CreateCustomerToken;

/**
 * Class CreateCustomerTokenAction
 *
 * @package Payum\Klarna\Payment\Action\Api
 */
class CreateCustomerTokenAction extends BaseApiAwareAction
{
    /**
     * @param CreateCustomerToken $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(
            [
                'authorization_token',
                'purchase_country',
                'purchase_currency',
                'locale',
                'description',
                'intended_use',
            ]
        );

        $response = $this->getApi()->createCustomerToken($model->get('authorization_token'), (array) $model);

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof CreateCustomerToken;
    }

}