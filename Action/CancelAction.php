<?php
namespace Payum\Klarna\Payment\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Payum\Klarna\Payment\Request\CancelAuthToken;

class CancelAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Cancel $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if (!$model->offsetExists('order_id') && $model->offsetExists('authorization_token')) {
            $this->gateway->execute(new CancelAuthToken($model->get('authorization_token')));
            $model->offsetUnset('authorization_token');
        }
        //@TODO implement order cancelation
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Cancel &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
