<?php
/**
 * Created by PhpStorm.
 * User: schacht
 * Date: 17.05.18
 * Time: 18:33
 */

namespace Payum\Klarna\Payment\Action\Api;


use Payum\Klarna\Payment\Request\CreateCustomerToken;

class CreateCustomerTokenAction extends BaseApiAwareAction
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
                'description',
                'intended_use',
            ]
        );

        $response = $this->getApi()->createSession($model);

        $model->replace(json_decode($response, true));
    }

    public function supports($request)
    {
        return $request instanceof CreateCustomerToken;
    }

}